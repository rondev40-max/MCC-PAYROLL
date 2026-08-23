<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Builds a Daily Time Record in the shape of CSC Form No. 48.
 *
 * The form is fixed by the Civil Service Commission: one row per calendar day,
 * four time columns (A.M. Arrival / Departure, P.M. Arrival / Departure) and an
 * Undertime column split into hours and minutes, with a TOTAL row underneath.
 * The layout is not ours to redesign — only the screen around it is.
 *
 * Undertime is the deficiency against the required eight hours, so a late
 * arrival and an early departure both land in the same column.
 */
final class Dtr
{
    /** Official hours, matching AttendanceController's constants. */
    public const AM_ARRIVAL   = '08:00';
    public const AM_DEPARTURE = '12:00';
    public const PM_ARRIVAL   = '13:00';
    public const PM_DEPARTURE = '17:00';

    /** Required minutes on a regular day, exclusive of the lunch break. */
    public const REQUIRED_MINUTES = 480;

    /**
     * One month of rows for one employee.
     *
     * Keyed on employee + course + type rather than employee id alone: the
     * dashboard strips the type prefix when it saves ("FT-12" is stored as 12),
     * so a full-timer and a part-timer sharing a row id would otherwise merge
     * into one DTR.
     *
     * @return array{
     *   rows: list<array>, totals: array{minutes:int, hours:int, mins:int, days:int, worked:int},
     *   month: Carbon, employee: array
     * }
     */
    public static function build(
        int $employeeId,
        string $course,
        Carbon $month,
        ?string $employeeType = null
    ): array {
        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();
        $employeeType = self::employeeType($employeeType);

        $records = self::fetch($employeeId, $course, $start, $end, $employeeType);

        $rows          = [];
        $totalUndertime = 0;
        $totalWorked    = 0;
        $daysWithEntry  = 0;

        for ($day = 1; $day <= $end->day; $day++) {
            $date   = $start->copy()->addDays($day - 1);
            $record = $records[$date->toDateString()] ?? null;

            $legacyStatus = strtolower(trim((string) ($record?->status ?? '')));
            $legacyHasWork = in_array($legacyStatus, ['present', 'late', 'half_day'], true)
                || ($legacyStatus === '' && (float) ($record?->hours_rendered ?? 0) > 0);
            $legacyOuterOnly = $record
                && $legacyHasWork
                && empty($record->am_in_time)
                && empty($record->am_out_time)
                && empty($record->pm_in_time)
                && empty($record->pm_out_time)
                && !empty($record->time_in)
                && !empty($record->time_out);
            $amIn  = self::time($record?->am_in_time  ?? ($legacyOuterOnly ? $record?->time_in : null));
            $amOut = self::time($record?->am_out_time ?? ($legacyOuterOnly ? self::AM_DEPARTURE : null));
            $pmIn  = self::time($record?->pm_in_time  ?? ($legacyOuterOnly ? self::PM_ARRIVAL : null));
            $pmOut = self::time($record?->pm_out_time ?? ($legacyOuterOnly ? $record?->time_out : null));

            $metrics   = self::metrics($amIn, $amOut, $pmIn, $pmOut, $record?->status);
            $worked    = $metrics['worked'];
            $hasEntry  = $metrics['has_entry'];
            $isPresent = $metrics['present'];
            $undertime = $metrics['undertime'];

            if ($isPresent) {
                $daysWithEntry++;
                $totalWorked += $worked;
                $totalUndertime += $undertime;
            }

            $rows[] = [
                'day'          => $day,
                'date'         => $date->toDateString(),
                'weekday'      => $date->format('D'),
                'is_weekend'   => in_array((int) $date->dayOfWeek, [0, 6], true),
                'is_future'    => $date->isFuture(),
                'am_in'        => $amIn,
                'am_out'       => $amOut,
                'pm_in'        => $pmIn,
                'pm_out'       => $pmOut,
                'has_entry'    => $hasEntry,
                'worked'       => $worked,
                'undertime'    => $undertime,
                'lateness'     => $metrics['lateness'],
                'overtime'     => $metrics['overtime'],
                'ut_hours'     => intdiv($undertime, 60),
                'ut_minutes'   => $undertime % 60,
                'status'       => $record?->status ?? null,
                'remarks'      => $record?->remarks ?? null,
            ];
        }

        return [
            'rows'  => $rows,
            'month' => $start,
            'totals' => [
                'minutes' => $totalUndertime,
                'hours'   => intdiv($totalUndertime, 60),
                'mins'    => $totalUndertime % 60,
                'days'    => $daysWithEntry,
                'worked'  => $totalWorked,
            ],
            'employee' => self::employee($records, $employeeId, $employeeType, $course),
        ];
    }

    /**
     * Attendance rows for the month, indexed by date.
     *
     * @return array<string, object>
     */
    private static function fetch(
        int $employeeId,
        string $course,
        Carbon $start,
        Carbon $end,
        ?string $employeeType
    ): array {
        try {
            if (!Schema::hasTable('attendances')) {
                return [];
            }

            $query = DB::table('attendances')
                ->where('employee_id', $employeeId)
                // Match every spelling of the department — see Departments.
                // A checker on 'BSED' opening a DTR whose rows were stored as
                // 'EDUCATION' got a blank form for a month they had filled in.
                ->whereIn(DB::raw('UPPER(TRIM(course))'), Departments::codesFor($course))
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()]);

            $typeKey = self::employeeTypeKey($employeeType);
            if ($typeKey) {
                $query->whereRaw(
                    'UPPER(REPLACE(REPLACE(REPLACE(TRIM(COALESCE(employee_type, \'\')), \'-\', \'\'), \' \', \'\'), \'_\', \'\')) = ?',
                    [$typeKey]
                );
            }

            return $query->orderBy('id')->get()
                ->keyBy(fn ($row) => Carbon::parse($row->date)->toDateString())
                ->all();
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Name and designation, taken from whichever row carries them.
     */
    private static function employee(
        array $records,
        int $employeeId,
        ?string $type,
        string $course
    ): array
    {
        $first = collect($records)->first(fn ($r) => !empty($r->employee_name));
        $rosterEmployee = self::employeeFromRoster($employeeId, $course, $type);

        return [
            'id'   => $employeeId,
            'name' => $first?->employee_name
                ?? $rosterEmployee?->employee_name
                ?? 'Employee #' . $employeeId,
            'type' => self::employeeType($first?->employee_type ?? $type),
        ];
    }

    private static function employeeFromRoster(
        int $employeeId,
        string $course,
        ?string $type
    ): ?object {
        $table = match (self::employeeTypeCode($type)) {
            'FT' => 'fulltime_timesheets',
            'PT' => 'parttime_timesheets',
            'ST' => 'staff_timesheets',
            'UT' => 'utility_timesheets',
            default => null,
        };

        if (!$table || !Schema::hasTable($table) || !Schema::hasColumn($table, 'employee_name')) {
            return null;
        }

        try {
            $base = DB::table($table)->select(['id', 'employee_name']);

            if (Schema::hasColumn($table, 'department')) {
                $base->whereIn(DB::raw('UPPER(TRIM(department))'), Departments::codesFor($course));
            }

            if (Schema::hasColumn($table, 'employee_id')) {
                $employee = (clone $base)
                    ->where('employee_id', $employeeId)
                    ->orderByDesc('id')
                    ->first();

                if ($employee) {
                    return $employee;
                }
            }

            return $base->where('id', $employeeId)->first();
        } catch (Throwable $e) {
            return null;
        }
    }

    /** Normalise a stored time to HH:MM, or null. */
    public static function time(?string $raw): ?string
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        try {
            return Carbon::parse($raw)->format('H:i');
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Stable employee-type label used in attendance identity keys.
     */
    public static function employeeType(?string $raw): ?string
    {
        $value = trim((string) $raw);
        if ($value === '') {
            return null;
        }

        return match (self::employeeTypeKey($value)) {
            'FT', 'FULLTIME', 'FULLTIMER' => 'Fulltime',
            'PT', 'PARTTIME', 'PARTTIMER' => 'Parttime',
            'ST', 'STAFF'                 => 'Staff',
            'UT', 'UTILITY'               => 'Utility',
            default                       => $value,
        };
    }

    /**
     * Normalized comparison key that is portable across MySQL and SQLite.
     */
    public static function employeeTypeKey(?string $raw): string
    {
        return strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', trim((string) $raw)));
    }

    public static function employeeTypeCode(?string $raw): ?string
    {
        return match (self::employeeTypeKey($raw)) {
            'FT', 'FULLTIME', 'FULLTIMER' => 'FT',
            'PT', 'PARTTIME', 'PARTTIMER' => 'PT',
            'ST', 'STAFF'                 => 'ST',
            'UT', 'UTILITY'               => 'UT',
            default                       => null,
        };
    }

    /**
     * Metrics shared by the cutoff editor and the monthly CSC form.
     *
     * @return array{
     *   has_entry:bool, present:bool, worked:int, lateness:int, undertime:int,
     *   overtime:int, total_hours:float
     * }
     */
    public static function metrics(
        ?string $amIn,
        ?string $amOut,
        ?string $pmIn,
        ?string $pmOut,
        ?string $status = null
    ): array {
        $worked = self::workedMinutes($amIn, $amOut, $pmIn, $pmOut);
        $hasEntry = (bool) ($amIn || $amOut || $pmIn || $pmOut);
        $statusKey = strtolower(trim((string) $status));
        $present = $hasEntry || in_array($statusKey, ['present', 'late', 'half_day'], true);
        $lateness = self::minutesAfter($amIn, self::AM_ARRIVAL)
            + self::minutesAfter($pmIn, self::PM_ARRIVAL);
        $scheduledUndertime = $lateness
            + self::minutesBefore($amOut, self::AM_DEPARTURE)
            + self::minutesBefore($pmOut, self::PM_DEPARTURE);

        return [
            'has_entry'   => $hasEntry,
            'present'     => $present,
            'worked'      => $worked,
            'lateness'    => $lateness,
            'undertime'   => $present
                ? max(0, self::REQUIRED_MINUTES - $worked, $scheduledUndertime)
                : 0,
            'overtime'    => self::minutesAfter($amOut, self::AM_DEPARTURE)
                + self::minutesAfter($pmOut, self::PM_DEPARTURE),
            'total_hours' => round($worked / 60, 2),
        ];
    }

    /**
     * Minutes actually worked across both halves of the day.
     *
     * A half with only one of its two punches contributes nothing: guessing the
     * missing side would invent hours nobody recorded.
     */
    public static function workedMinutes(?string $amIn, ?string $amOut, ?string $pmIn, ?string $pmOut): int
    {
        return self::span($amIn, $amOut) + self::span($pmIn, $pmOut);
    }

    private static function span(?string $from, ?string $to): int
    {
        if (!$from || !$to) {
            return 0;
        }

        try {
            $start = Carbon::createFromFormat('H:i', $from);
            $end   = Carbon::createFromFormat('H:i', $to);

            // A departure earlier than the arrival is a data-entry slip, not a
            // negative day — count it as zero rather than subtracting time.
            return $end->lessThanOrEqualTo($start) ? 0 : $start->diffInMinutes($end);
        } catch (Throwable $e) {
            return 0;
        }
    }

    private static function minutesAfter(?string $actual, string $scheduled): int
    {
        if (!$actual) {
            return 0;
        }

        try {
            $actualTime = Carbon::createFromFormat('H:i', substr($actual, 0, 5));
            $scheduledTime = Carbon::createFromFormat('H:i', $scheduled);

            return $actualTime->greaterThan($scheduledTime)
                ? (int) $scheduledTime->diffInMinutes($actualTime)
                : 0;
        } catch (Throwable $e) {
            return 0;
        }
    }

    private static function minutesBefore(?string $actual, string $scheduled): int
    {
        if (!$actual) {
            return 0;
        }

        try {
            $actualTime = Carbon::createFromFormat('H:i', substr($actual, 0, 5));
            $scheduledTime = Carbon::createFromFormat('H:i', $scheduled);

            return $actualTime->lessThan($scheduledTime)
                ? (int) $actualTime->diffInMinutes($scheduledTime)
                : 0;
        } catch (Throwable $e) {
            return 0;
        }
    }

    /** "7 h 30 m" for a minute count, or an em dash at zero. */
    public static function humanMinutes(int $minutes): string
    {
        if ($minutes <= 0) {
            return '—';
        }

        $h = intdiv($minutes, 60);
        $m = $minutes % 60;

        return trim(($h > 0 ? "{$h}h " : '') . ($m > 0 ? "{$m}m" : ''));
    }
}
