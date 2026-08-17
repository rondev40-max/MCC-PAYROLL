<?php

namespace App\Support;

use Illuminate\Support\Carbon;
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
 * Undertime here is the deficiency against the required eight hours, so a late
 * arrival and an early departure both land in the same column. The attendance
 * dashboard's own calculateDayMetrics() only ever counted leaving early, which
 * under-reports a day that started at 10:00 and ended exactly at 17:00; that
 * value is kept in the database for the existing screens, and the DTR recomputes
 * from the raw times so the printed form is right.
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

        $records = self::fetch($employeeId, $course, $start, $end, $employeeType);

        $rows          = [];
        $totalUndertime = 0;
        $totalWorked    = 0;
        $daysWithEntry  = 0;

        for ($day = 1; $day <= $end->day; $day++) {
            $date   = $start->copy()->addDays($day - 1);
            $record = $records[$date->toDateString()] ?? null;

            $amIn  = self::time($record?->am_in_time  ?? $record?->time_in ?? null);
            $amOut = self::time($record?->am_out_time ?? null);
            $pmIn  = self::time($record?->pm_in_time  ?? null);
            $pmOut = self::time($record?->pm_out_time ?? $record?->time_out ?? null);

            $worked    = self::workedMinutes($amIn, $amOut, $pmIn, $pmOut);
            $hasEntry  = $amIn || $amOut || $pmIn || $pmOut;
            $undertime = $hasEntry ? max(0, self::REQUIRED_MINUTES - $worked) : 0;

            if ($hasEntry) {
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
            'employee' => self::employee($records, $employeeId, $employeeType),
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
                ->whereRaw('UPPER(TRIM(course)) = ?', [strtoupper(trim($course))])
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()]);

            if ($employeeType) {
                $query->where('employee_type', $employeeType);
            }

            return $query->get()
                ->keyBy(fn ($row) => Carbon::parse($row->date)->toDateString())
                ->all();
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Name and designation, taken from whichever row carries them.
     */
    private static function employee(array $records, int $employeeId, ?string $type): array
    {
        $first = collect($records)->first(fn ($r) => !empty($r->employee_name));

        return [
            'id'   => $employeeId,
            'name' => $first->employee_name ?? 'Employee #' . $employeeId,
            'type' => $first->employee_type ?? $type,
        ];
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
