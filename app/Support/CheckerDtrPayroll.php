<?php

namespace App\Support;

use App\Models\Holiday;
use App\Models\Setting;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Pays the eight-hour groups from the attendance checker's register (the
 * CSC Form No. 48 DTR) instead of from their scheduled timesheet hours.
 *
 * The checker portal and payroll used to be two separate systems: a checker
 * encoded every day for their department, and then an administrator typed the
 * same attendance again as timesheet hours before Send Payslips could use it.
 * With this switched on (System Settings → Payroll), Send Payslips reads the
 * register directly.
 *
 * ── Who it covers ────────────────────────────────────────────────────────────
 *
 * Full-time instructors, staff and utility workers: the groups that work the
 * prescribed 8:00–12:00 / 1:00–5:00 day. Part-time instructors are paid for
 * their teaching load, and an evening class measured against an 8-to-5 day
 * would read as a late, incomplete day — so they keep their timesheet hours.
 * Watchmen and admin personnel have no checker register at all.
 *
 * An employee already on web Time In / Time Out payroll (AttendancePayroll) is
 * left alone: that was switched on for them explicitly, and paying the same
 * days from two sources would pay them twice.
 *
 * ── What a day is worth ──────────────────────────────────────────────────────
 *
 * The same figures the DTR shows, so a payslip can always be checked against
 * the printed form:
 *
 *  - present / late:   480 minutes minus undertime (late arrival and early
 *                      departure both count, as they do on Form 48). Time
 *                      before 8:00 or after 5:00 is not paid.
 *  - half day:         240 minutes minus undertime.
 *  - official business: a full day — it is service rendered, just elsewhere.
 *  - absent, leave, holiday: unpaid. Holidays match the timesheets, which
 *                      already leave them out; there is no leave-credit
 *                      system for leave to draw on.
 *
 * ── When it refuses ──────────────────────────────────────────────────────────
 *
 * Every Monday–Friday of the cut-off that is not a holiday must have an entry,
 * and no entry may be half-filled. A register the checker has not finished
 * would otherwise underpay quietly, which is the one failure a payroll run
 * must never have. All problems in the batch are reported together, before a
 * single email is sent.
 */
final class CheckerDtrPayroll
{
    public const SETTING = 'payroll_uses_checker_dtr';

    /** Payroll type => the checker register's employee-type code. */
    public const TYPE_CODES = [
        'Fulltime' => 'FT',
        'Staff'    => 'ST',
        'Utility'  => 'UT',
    ];

    /** Types whose rate is per hour; the rest are paid per day. */
    private const HOURLY_CODES = ['FT'];

    /** Every spelling an employee_type column may hold for each code. */
    private const TYPE_ALIASES = [
        'FT' => ['FT', 'FULLTIME', 'FULLTIMER'],
        'ST' => ['ST', 'STAFF'],
        'UT' => ['UT', 'UTILITY'],
    ];

    private const UNPAID_STATUSES = ['absent', 'leave', 'holiday'];

    /** How many employees one error message lists before summarising. */
    private const MESSAGE_LIMIT = 5;

    public static function enabled(): bool
    {
        try {
            return (string) Setting::get(self::SETTING, '0') === '1';
        } catch (Throwable $e) {
            // No settings table yet: behave exactly as before this existed.
            return false;
        }
    }

    public static function covers(array $payload): bool
    {
        return isset(self::TYPE_CODES[$payload['type'] ?? ''])
            && isset($payload['timesheet'])
            && empty($payload['attendance_snapshot']);
    }

    /**
     * Price every covered payload in one or more batches from the DTR.
     *
     * Batches share one error report, so the administrator gets the whole list
     * of what to finish instead of discovering it one Send click at a time.
     *
     * @param  list<Collection>  $batches
     * @return list<Collection>
     *
     * @throws ValidationException
     */
    public static function applyAll(array $batches, Carbon $start, Carbon $end): array
    {
        $anyCovered = collect($batches)->contains(
            fn (Collection $batch) => $batch->contains(fn ($payload) => self::covers($payload))
        );

        if (!self::enabled() || !$anyCovered) {
            return $batches;
        }

        self::assertWholeCutoff($start, $end);

        $holidays = self::holidays($start, $end);
        $problems = [];

        $priced = array_map(function (Collection $batch) use ($start, $end, $holidays, &$problems) {
            return $batch->map(function (array $payload) use ($start, $end, $holidays, &$problems) {
                if (!self::covers($payload)) {
                    return $payload;
                }

                $summary = self::summarize($payload['timesheet'], $payload['type'], $start, $end, $holidays);
                $who = trim(($payload['employeeName'] ?? 'Employee') . ' (' . ($payload['department'] ?? '—') . ')');

                if ($summary['problems']) {
                    $problems[] = $who . ': ' . implode('; ', $summary['problems']);

                    return $payload;
                }

                if ((float) ($payload['rate'] ?? 0) <= 0) {
                    $problems[] = $who . ': set a payroll rate on the timesheet first';

                    return $payload;
                }

                return self::price($payload, $summary);
            });
        }, $batches);

        if ($problems) {
            throw ValidationException::withMessages(['attendance' => self::problemMessage($problems)]);
        }

        return $priced;
    }

    /**
     * What the register says about one timesheet row for one cut-off.
     *
     * @param  list<string>|null  $holidays  Y-m-d dates; looked up when null
     * @return array{
     *   code: string, hourly: bool, paid_minutes: int, units: float,
     *   hours_by_date: array<string, float>, days: array<string, array>,
     *   problems: list<string>
     * }
     */
    public static function summarize(object $timesheet, string $type, Carbon $start, Carbon $end, ?array $holidays = null): array
    {
        $code = self::TYPE_CODES[$type] ?? null;
        if ($code === null) {
            throw new \InvalidArgumentException("The checker register does not cover {$type} employees.");
        }

        $holidays ??= self::holidays($start, $end);
        $records = self::records($timesheet, $code, $start, $end);

        $missing = [];
        $incomplete = [];
        $paidTotal = 0;
        $hoursByDate = [];
        $days = [];

        foreach (CarbonPeriod::create($start->copy()->startOfDay(), $end->copy()->startOfDay()) as $date) {
            $key = $date->toDateString();
            $record = $records[$key] ?? null;

            if ($record === null) {
                if (self::isWorkingDay($date, $holidays)) {
                    $missing[] = $date->format('M j');
                }
                continue;
            }

            $day = self::day($record);

            if ($day['incomplete']) {
                $incomplete[] = $date->format('M j');
                continue;
            }

            $paidTotal += $day['paid_minutes'];
            $hoursByDate[$key] = round($day['paid_minutes'] / 60, 2);
            $days[$key] = $day;
        }

        $problems = [];
        if ($missing) {
            $problems[] = 'no entry for ' . self::dateList($missing);
        }
        if ($incomplete) {
            $problems[] = 'unfinished time entries on ' . self::dateList($incomplete);
        }

        $hourly = in_array($code, self::HOURLY_CODES, true);

        return [
            'code'          => $code,
            'hourly'        => $hourly,
            'paid_minutes'  => $paidTotal,
            'units'         => $hourly ? $paidTotal / 60 : $paidTotal / Dtr::REQUIRED_MINUTES,
            'hours_by_date' => $hoursByDate,
            'days'          => $days,
            'problems'      => $problems,
        ];
    }

    /**
     * Paid minutes for one register row, using the DTR's own arithmetic.
     *
     * @return array{status: string, am_in: ?string, am_out: ?string, pm_in: ?string,
     *   pm_out: ?string, paid_minutes: int, incomplete: bool}
     */
    public static function day(object $record): array
    {
        $status = strtolower(trim((string) ($record->status ?? '')));

        // Rows written before the four CSC columns existed carry only the outer
        // time_in / time_out. Read them the way the DTR does, or the payslip
        // and the printed form would disagree about the same day.
        $hasWork = in_array($status, ['present', 'late', 'half_day'], true)
            || ($status === '' && (float) ($record->hours_rendered ?? 0) > 0);
        $outerOnly = $hasWork
            && empty($record->am_in_time) && empty($record->am_out_time)
            && empty($record->pm_in_time) && empty($record->pm_out_time)
            && !empty($record->time_in) && !empty($record->time_out);

        $amIn  = Dtr::time($outerOnly ? $record->time_in : ($record->am_in_time ?? null));
        $amOut = Dtr::time($outerOnly ? Dtr::AM_DEPARTURE : ($record->am_out_time ?? null));
        $pmIn  = Dtr::time($outerOnly ? Dtr::PM_ARRIVAL : ($record->pm_in_time ?? null));
        $pmOut = Dtr::time($outerOnly ? $record->time_out : ($record->pm_out_time ?? null));

        $day = [
            'status'       => $status,
            'am_in'        => $amIn,
            'am_out'       => $amOut,
            'pm_in'        => $pmIn,
            'pm_out'       => $pmOut,
            'paid_minutes' => 0,
            'incomplete'   => false,
        ];

        if ($status === 'official_business') {
            return ['paid_minutes' => Dtr::REQUIRED_MINUTES] + $day;
        }

        if (in_array($status, self::UNPAID_STATUSES, true)) {
            return $day;
        }

        $metrics = Dtr::metrics($amIn, $amOut, $pmIn, $pmOut, $status ?: null);
        if (!$metrics['present']) {
            return $day;
        }

        // The same test the register uses to badge a row "Needs review".
        $halfDay = $status === 'half_day';
        $punches = count(array_filter([$amIn, $amOut, $pmIn, $pmOut]));
        $backwards = self::backwards($amIn, $amOut) || self::backwards($pmIn, $pmOut);
        $complete = $halfDay
            ? (self::forwards($amIn, $amOut) || self::forwards($pmIn, $pmOut))
            : ($punches === 4 && !$backwards);

        if (!$complete) {
            return ['incomplete' => true] + $day;
        }

        $required = $halfDay ? intdiv(Dtr::REQUIRED_MINUTES, 2) : Dtr::REQUIRED_MINUTES;

        return ['paid_minutes' => max(0, $required - $metrics['undertime'])] + $day;
    }

    /**
     * Replace the payload's scheduled units with the register's.
     *
     * The stored timesheet is not modified: the administrator's rates and
     * deductions stay where they are, and the payslip gets a priced copy.
     */
    private static function price(array $payload, array $summary): array
    {
        $hourly = $summary['hourly'];
        $units = round($summary['units'], $hourly ? 2 : 4);
        // Priced from exact minutes, not from the rounded units shown on the
        // payslip, so 7 h 20 m is never paid as 7.33 h.
        $gross = round($summary['units'] * (float) $payload['rate'], 2);

        $snapshot = clone $payload['timesheet'];
        $snapshot->setAttribute($hourly ? 'total_hour' : 'total_days', $units);
        $snapshot->setAttribute('total_honorarium', $gross);
        // The payslip email's day-by-day grid reads this.
        $snapshot->setAttribute('attendance_hours_by_date', $summary['hours_by_date']);

        $payload['timesheet'] = $snapshot;
        $payload['totalDaysOrHours'] = $units . ($hourly ? ' hours' : ' days');
        $payload['totalHonorarium'] = $gross;
        $payload['attendance_snapshot'] = [
            'source'        => 'checker_dtr',
            'paid_minutes'  => $summary['paid_minutes'],
            'hours_by_date' => $summary['hours_by_date'],
            'days'          => $summary['days'],
        ];

        return $payload;
    }

    /**
     * Register rows for one timesheet row, keyed by date.
     *
     * The checker portal identifies a person the way its roster does: by the
     * master-list employee_id when the timesheet has one, otherwise by the
     * timesheet row's own id — plus the department and the employee type, so
     * a full-timer and a staff member sharing an id never merge.
     *
     * @return array<string, object>
     */
    private static function records(object $timesheet, string $code, Carbon $start, Carbon $end): array
    {
        $identity = (int) ($timesheet->employee_id ?? 0) ?: (int) ($timesheet->id ?? 0);
        $department = trim((string) ($timesheet->department ?? ''));

        if ($identity <= 0 || $department === '' || !Schema::hasTable('attendances')) {
            return [];
        }

        $aliases = self::TYPE_ALIASES[$code];

        return DB::table('attendances')
            ->where('employee_id', $identity)
            ->whereIn(DB::raw('UPPER(TRIM(course))'), Departments::codesFor($department))
            ->whereRaw(
                'UPPER(REPLACE(REPLACE(REPLACE(TRIM(COALESCE(employee_type, \'\')), \'-\', \'\'), \' \', \'\'), \'_\', \'\')) IN ('
                    . implode(', ', array_fill(0, count($aliases), '?')) . ')',
                $aliases
            )
            // Date-only bounds, so a row stored with a time part on the last
            // day of the cutoff is still counted.
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString())
            ->orderBy('id')
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->date)->toDateString())
            ->all();
    }

    /** @throws ValidationException */
    private static function assertWholeCutoff(Carbon $start, Carbon $end): void
    {
        $expectedEnd = $start->day === 1 ? $start->copy()->day(15) : $start->copy()->endOfMonth();

        if (!in_array($start->day, [1, 16], true) || !$end->isSameDay($expectedEnd)) {
            throw ValidationException::withMessages([
                'attendance' => "Payroll from the checker's DTR needs one whole cut-off: the 1st to the 15th, or the 16th to the end of the month.",
            ]);
        }
    }

    /** @return list<string> */
    private static function holidays(Carbon $start, Carbon $end): array
    {
        try {
            if (!Schema::hasTable('holidays')) {
                return [];
            }

            return Holiday::whereDate('date', '>=', $start->toDateString())
                ->whereDate('date', '<=', $end->toDateString())
                ->pluck('date')
                ->map(fn ($date) => Carbon::parse($date)->toDateString())
                ->all();
        } catch (Throwable $e) {
            return [];
        }
    }

    private static function isWorkingDay(Carbon $date, array $holidays): bool
    {
        return !$date->isWeekend() && !in_array($date->toDateString(), $holidays, true);
    }

    private static function forwards(?string $from, ?string $to): bool
    {
        return $from !== null && $to !== null && strcmp($to, $from) > 0;
    }

    private static function backwards(?string $from, ?string $to): bool
    {
        return $from !== null && $to !== null && strcmp($to, $from) <= 0;
    }

    /** "Sep 22, Sep 23 and 4 more days" */
    private static function dateList(array $dates): string
    {
        $shown = array_slice($dates, 0, 4);
        $more = count($dates) - count($shown);

        return implode(', ', $shown) . ($more > 0 ? " and {$more} more day" . ($more === 1 ? '' : 's') : '');
    }

    private static function problemMessage(array $problems): string
    {
        $shown = array_slice($problems, 0, self::MESSAGE_LIMIT);
        $more = count($problems) - count($shown);

        return "No payslips were sent. Payroll uses the attendance checker's DTR, and it is not finished for "
            . count($problems) . ' employee' . (count($problems) === 1 ? '' : 's') . ' — '
            . implode(' | ', $shown)
            . ($more > 0 ? " | and {$more} more" : '')
            . ". Ask the attendance checker to complete these days (mark days not worked as Absent), "
            . "or turn off \"Pay from the attendance checker's DTR\" in System Settings.";
    }
}
