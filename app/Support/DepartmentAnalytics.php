<?php

namespace App\Support;

use App\Models\FulltimeTimesheet;
use App\Models\ParttimeTimesheet;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Headcount per academic department, for the dashboard's Department Analytics chart.
 *
 * Only the four academic departments appear here, and only instructors are
 * counted. Staff, Utility, Watchman and Admin Personnel are college-wide roles
 * that belong to no department — the timesheet tables for the first two even had
 * their `department` column dropped (see the 2025_01_17 and 2025_08_23
 * migrations), so any value still sitting there is a leftover default of 'BSIT'
 * rather than a real assignment. Counting them inflated every bar.
 *
 * The previous implementation discovered its own axis by unioning the codes
 * found in the `departments` table with whatever appeared in the timesheets.
 * That surfaced BSED and BEED as separate bars once the 2025_09_16 migration
 * split Education in two, and it silently dropped a department to zero bars when
 * nobody was assigned to it. The axis is fixed here instead.
 */
final class DepartmentAnalytics
{
    /**
     * The four academic departments, in the order used everywhere else in the
     * admin UI (sidebar Department menu, master-list filter).
     *
     * `codes` lists every value that can represent the department in a
     * timesheet's `department` column. Education needs three: the timesheet
     * enum only ever allowed 'EDUCATION', but the departments table renamed that
     * row to 'BSED' and added 'BEED', and the fulltime/parttime edit forms offer
     * both of those as options — so rows can carry any of the three.
     */
    private const DEPARTMENTS = [
        ['code' => 'BSIT',      'name' => 'BSIT',      'codes' => ['BSIT']],
        ['code' => 'BSBA',      'name' => 'BSBA',      'codes' => ['BSBA']],
        ['code' => 'BSHM',      'name' => 'BSHM',      'codes' => ['BSHM']],
        ['code' => 'EDUCATION', 'name' => 'Education', 'codes' => ['EDUCATION', 'BSED', 'BEED']],
    ];

    /**
     * Distinct full-time and part-time instructors per department.
     *
     * Always returns all four departments, including any with a headcount of
     * zero, so the chart's x-axis and the "Departments" stat card stay stable.
     *
     * @return Collection<int, array{name:string, code:string, fulltime:int, parttime:int, total:int}>
     */
    public static function build(): Collection
    {
        try {
            return collect(self::DEPARTMENTS)->map(function (array $dept): array {
                $fulltime = self::countInstructors(FulltimeTimesheet::class, $dept['codes']);
                $parttime = self::countInstructors(ParttimeTimesheet::class, $dept['codes']);

                return [
                    'name'     => $dept['name'],
                    'code'     => $dept['code'],
                    'fulltime' => $fulltime,
                    'parttime' => $parttime,
                    'total'    => $fulltime + $parttime,
                ];
            });
        } catch (Throwable $e) {
            return collect();
        }
    }

    /**
     * Count distinct named employees in a timesheet table for the given codes.
     *
     * Counting by name rather than by row because one employee has a timesheet
     * row per pay period.
     *
     * @param  class-string  $model
     * @param  list<string>  $codes
     */
    private static function countInstructors(string $model, array $codes): int
    {
        return $model::whereIn('department', $codes)
            ->whereNotNull('employee_name')
            ->where('employee_name', '!=', '')
            ->distinct()
            ->count('employee_name');
    }
}
