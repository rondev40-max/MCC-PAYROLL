<?php

namespace App\Http\Controllers;

use App\Models\AttendanceHistory;
use App\Models\Department;
use App\Models\ParttimeTimesheet;
use App\Models\StaffTimesheet;
use App\Models\UtilityTimesheet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceHistoryController extends Controller
{
    private const TIMESHEET_TYPES = [
        ParttimeTimesheet::class => 'Part-time',
        StaffTimesheet::class    => 'Staff',
        UtilityTimesheet::class  => 'Utility',
    ];

    private const DAY_NAMES = [
        'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday',
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // SYNC
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Intended to be called from a scheduled Artisan command, NOT from index().
     * Calling it on every page load caused performance issues and overwrote
     * historical records with the current week's date.
     *
     *   php artisan attendance:sync-timesheets  (schedule: daily or weekly)
     */
    public function syncTimesheetData(): void
    {
        foreach (self::TIMESHEET_TYPES as $modelClass => $employeeType) {
            $modelClass::chunk(200, function ($records) use ($employeeType) {
                foreach ($records as $record) {
                    $this->processTimesheet($record, $employeeType);
                }
            });
        }
    }

    private function processTimesheet($record, string $employeeType): void
    {
        $days = is_array($record->days) ? $record->days : json_decode($record->days, true);
        if (!is_array($days)) return;

        // FIX #6: If the record has no week_start column, log a warning and
        // skip it entirely rather than silently mapping it to the current week
        // and corrupting historical data.
        if (!isset($record->week_start)) {
            Log::warning("processTimesheet: skipping [{$record->email}] — no week_start field on record.");
            return;
        }

        $periodStart = Carbon::parse($record->week_start)->startOfWeek();

        foreach ($days as $day => $hours) {
            if (empty($hours) || $hours <= 0) continue;

            $dayNumber = array_search(strtolower($day), self::DAY_NAMES);
            if ($dayNumber === false) continue;

            $attendanceDate = $periodStart->copy()->addDays($dayNumber);

            try {
                AttendanceHistory::updateOrCreate(
                    [
                        'email'           => $record->email,
                        'attendance_date' => $attendanceDate->toDateString(),
                    ],
                    [
                        'employee_name' => $record->employee_name,
                        'employee_type' => $employeeType,
                        'designation'   => $record->designation,
                        'department'    => $record->department,
                        'is_present'    => true,
                        'hours_worked'  => $hours,
                    ]
                );
            } catch (\Exception $e) {
                Log::warning("processTimesheet: skipping [{$record->email}] on {$attendanceDate->toDateString()} – " . $e->getMessage());
            }
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query     = AttendanceHistory::query();
        $dateRange = $request->get('date_range', 'month');
        $startDate = Carbon::parse($request->get('start_date', now()));

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        $this->applyDateFilter($query, $dateRange, $startDate);

        if ($dateRange === 'day') {
            $records = $query->orderBy('employee_name')->paginate(25);
        } else {
            $records = $query
                ->groupBy('email', 'employee_name', 'department')
                ->select([
                    'email',
                    'employee_name',
                    'department',
                    DB::raw('COUNT(CASE WHEN is_present = 1 THEN 1 END) as present_days'),
                    DB::raw('COUNT(*) as total_days'),
                ])
                ->orderBy('employee_name')
                ->paginate(25);
        }

        $departments = Department::pluck('name');

        return view('admin.history', [
            'attendanceHistory' => $records,
            'departments'       => $departments,
            'tableReady'        => true,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DETAILS
    // ──────────────────────────────────────────────────────────────────────────

    public function getDetails(string $email, Request $request)
    {
        $dateRange = $request->get('date_range', 'month');
        $startDate = Carbon::parse($request->get('start_date', now()));

        $query = AttendanceHistory::where('email', $email);
        $this->applyDateFilter($query, $dateRange, $startDate);
        $records = $query->orderBy('attendance_date')->get();

        $employee = $records->first();
        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'No records found.']);
        }

        $timesheet = $this->getTimesheetForEmployee($employee->employee_type, $email);

        $html = view('admin.partials.attendance-details', [
            'employee'  => $employee,
            'timesheet' => $timesheet,
            'records'   => $records,
            'stats'     => [
                'totalDays'   => $records->count(),
                'presentDays' => $records->where('is_present', true)->count(),
                'daysWorked'  => $timesheet ? $this->calculateDaysWorked($timesheet->days) : 0,
            ],
            'dateRange' => $dateRange,
            'startDate' => $startDate,
        ])->render();

        return response()->json(['success' => true, 'html' => $html]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ──────────────────────────────────────────────────────────────────────────

    private function applyDateFilter($query, string $dateRange, Carbon $startDate): void
    {
        switch ($dateRange) {
            case 'day':
                $query->whereDate('attendance_date', $startDate);
                break;

            case 'week':
                $query->whereBetween('attendance_date', [
                    $startDate->copy()->startOfWeek(),
                    $startDate->copy()->endOfWeek(),
                ]);
                break;

            default: // month
                $query->whereBetween('attendance_date', [
                    $startDate->copy()->startOfMonth(),
                    $startDate->copy()->endOfMonth(),
                ]);
        }
    }

    private function getTimesheetForEmployee(string $employeeType, string $email)
    {
        $modelClass = array_search($employeeType, self::TIMESHEET_TYPES);

        if (!$modelClass) {
            Log::warning("getTimesheetForEmployee: unknown type [{$employeeType}] for [{$email}]");
            return null;
        }

        return $modelClass::where('email', $email)->first();
    }

    private function calculateDaysWorked($days): int
    {
        if (!$days) return 0;
        $days = is_array($days) ? $days : json_decode($days, true);
        if (!is_array($days)) return 0;

        return collect($days)->filter(fn($hours) => !empty($hours) && $hours > 0)->count();
    }
}