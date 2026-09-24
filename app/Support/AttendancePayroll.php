<?php

namespace App\Support;

use App\Models\AttendanceSession;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

final class AttendancePayroll
{
    public const DAILY_TYPES = ['Staff', 'Utility', 'Watchman', 'Admin Personnel'];

    public const MODELS = [
        'Fulltime' => \App\Models\FulltimeTimesheet::class,
        'Part-time' => \App\Models\ParttimeTimesheet::class,
        'Staff' => \App\Models\StaffTimesheet::class,
        'Utility' => \App\Models\UtilityTimesheet::class,
        'Watchman' => \App\Models\WatchmanTimesheet::class,
        'Admin Personnel' => \App\Models\AdminPersonnelTimesheet::class,
    ];

    public static function preview(Employee $employee, string $start, string $end): array
    {
        $date = Carbon::parse($start);
        $period = $date->day <= 15 ? '1-15' : '16-end';
        $rows = [];
        foreach (self::MODELS as $type => $model) {
            $query = $model::where(function ($q) use ($employee) {
                $q->where('employee_id', $employee->id)->orWhere(function ($q) use ($employee) {
                    $q->whereNull('employee_id')->whereRaw('LOWER(TRIM(email)) = ?', [strtolower(trim($employee->email))]);
                });
            })->where('period', $period);
            if ($type === 'Fulltime') {
                $query->whereYear('date', $date->year)->whereMonth('date', $date->month);
            } else {
                $query->where('year', $date->year)->where(function ($q) use ($date) {
                    $q->where('month', $date->month)->orWhere('month', 'LIKE', $date->format('F').'%');
                });
            }
            if ($row = $query->latest('id')->first()) {
                $daily = in_array($type, self::DAILY_TYPES, true);
                $summary = self::summary($employee, $start, $end);
                $rate = (float) ($daily ? $row->rate_per_day : $row->rate_per_hour);
                $gross = round(($daily ? $summary['days'] : $summary['hours']) * $rate, 2);
                $rows[] = ['type' => $type, 'rate' => $rate, 'unit' => $daily ? 'day' : 'hour', 'gross' => $gross,
                    'net' => WageLiquidation::fromTimesheet($row, $gross)['net_pay']];
            }
        }

        return $rows;
    }

    public static function summary(Employee $employee, string $start, string $end): array
    {
        $sessions = AttendanceSession::where('employee_id', $employee->id)
            ->whereBetween('work_date', [$start, $end])->get();
        $daily = $sessions->where('status', 'complete')->groupBy(fn ($s) => $s->work_date->toDateString())
            ->map(fn ($rows) => $rows->sum('worked_seconds') / 3600);
        $dayHours = max(1, (float) config('attendance.hours_per_day'));

        return [
            'hours' => round($daily->sum(), 4),
            'daily_hours' => $daily->map(fn ($hours) => round($hours, 4))->all(),
            // A daily rate pays at most one regular day per work date.
            'days' => round($daily->sum(fn ($hours) => min(1, $hours / $dayHours)), 4),
            'pending' => $sessions->whereIn('status', ['open', 'needs_review'])->count(),
            'sessions' => $sessions,
        ];
    }

    public static function apply(array $payload, Carbon $start, Carbon $end): array
    {
        $row = $payload['timesheet'];
        // Match the master-list ID or normalized email, never a name or unrelated row ID.
        $employee = $row->employee_id ? Employee::find($row->employee_id) : null;
        if (! $row->employee_id && ! empty($row->email)) {
            $matches = Employee::whereRaw('LOWER(TRIM(email)) = ?', [strtolower(trim($row->email))])->get();
            if ($matches->count() > 1) {
                throw ValidationException::withMessages(['attendance' => 'Duplicate master-list email. Resolve the employee identity before payroll.']);
            }
            $employee = $matches->first();
        }
        if (! $employee || ! $employee->attendance_payroll_from || $end->toDateString() < $employee->attendance_payroll_from) {
            return $payload;
        }
        if ($start->toDateString() < $employee->attendance_payroll_from) {
            throw ValidationException::withMessages(['attendance' => "{$employee->name}: select a cutoff entirely after attendance payroll activation."]);
        }
        $expectedEnd = $start->day === 1 ? $start->copy()->day(15) : $start->copy()->endOfMonth();
        if (! in_array($start->day, [1, 16], true) || ! $end->isSameDay($expectedEnd)) {
            throw ValidationException::withMessages(['attendance' => 'Attendance payroll requires one complete cutoff: 1-15 or 16-end.']);
        }
        $summary = self::summary($employee, $start->toDateString(), $end->toDateString());
        if ($summary['pending']) {
            throw ValidationException::withMessages(['attendance' => "{$employee->name}: resolve open or flagged attendance sessions before sending payroll."]);
        }
        if ((float) $payload['rate'] <= 0) {
            throw ValidationException::withMessages(['attendance' => "{$employee->name}: set a payroll rate first."]);
        }
        $daily = in_array($payload['type'], self::DAILY_TYPES, true);
        $units = $daily ? $summary['days'] : $summary['hours'];
        $payload['totalDaysOrHours'] = $units.($daily ? ' days' : ' hours');
        $payload['totalHonorarium'] = round($units * (float) $payload['rate'], 2);
        // The mail template reads the timesheet too. Give it the same calculated
        // snapshot without replacing the administrator's stored schedule.
        $snapshot = clone $row;
        $snapshot->setAttribute($daily ? 'total_days' : 'total_hour', $units);
        $snapshot->setAttribute('total_honorarium', $payload['totalHonorarium']);
        $snapshot->setAttribute('attendance_hours_by_date', $summary['daily_hours']);
        $payload['timesheet'] = $snapshot;
        $payload['attendance_snapshot'] = [
            'hours_by_date' => $summary['daily_hours'],
            'sessions' => $summary['sessions']->map(fn ($s) => $s->only(['id', 'work_date', 'clocked_in_at', 'clocked_out_at', 'worked_seconds', 'status', 'reviewed_by', 'review_note']))->all(),
        ];

        return $payload;
    }
}
