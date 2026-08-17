<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\FulltimeTimesheet;
use App\Models\ParttimeTimesheet;
use App\Models\StaffTimesheet;
use App\Models\UtilityTimesheet;
use App\Models\WatchmanTimesheet;
use App\Models\AdminPersonnelTimesheet;
use App\Models\User;
use App\Models\Department;
use App\Support\DepartmentAnalytics;


class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::all();
        
        $userName = $request->session()->get('user_name', 'Guest');
        
        $stats = $this->getEmployeeStatistics();
        $departmentAnalysis = $this->getDepartmentAnalytics();

        $userDepartment = null;
        $user = Auth::user();

        if ($user) {
            $userDepartment = $user->course; 
        }
        
        $attendanceUsers = User::where('role', 'attendance_checker')->get();

        return view('admin.dashboard', [
            'employees'                 => $employees,
            'userName'                  => $userName,
            'totalEmployees'            => $stats['totalEmployees'],
            'totalFulltimeInstructors'  => $stats['totalFulltimeInstructors'],
            'totalParttimeInstructors'  => $stats['totalParttimeInstructors'],
            'totalStaff'                => $stats['totalStaff'],
            'totalUtility'              => $stats['totalUtility'],
            'totalWatchman'             => $stats['totalWatchman'],
            'totalAdminPersonnel'       => $stats['totalAdminPersonnel'],
            'departmentAnalysis'        => $departmentAnalysis,
            'departmentCount'           => $departmentAnalysis->count(),
            'userDepartment'            => $userDepartment,
            'attendanceUsers'           => $attendanceUsers,
        ]);
    }
    
    /**
     * Search for employees across all timesheet tables
     */
    public function search(Request $request)
    {
        $query = $request->get('query', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $results = collect();
        $queryLower = strtolower($query);
        
        // BUG FIX #1: str_contains arguments were reversed.
        // str_contains($haystack, $needle) — $queryLower is the haystack, keyword is the needle.
        $typeMatches = [];
        if (str_contains($queryLower, 'fulltime') || str_contains($queryLower, 'full-time') || str_contains($queryLower, 'full time')) {
            $typeMatches[] = 'fulltime';
        }
        if (str_contains($queryLower, 'parttime') || str_contains($queryLower, 'part-time') || str_contains($queryLower, 'part time')) {
            $typeMatches[] = 'parttime';
        }
        if (str_contains($queryLower, 'staff')) {
            $typeMatches[] = 'staff';
        }
        if (str_contains($queryLower, 'utility')) {
            $typeMatches[] = 'utility';
        }
        
        // Search in FulltimeTimesheet
        if (empty($typeMatches) || in_array('fulltime', $typeMatches)) {
            $fulltimeQuery = FulltimeTimesheet::select('employee_name', 'designation')->distinct();
            if (empty($typeMatches)) {
                $fulltimeQuery->where('employee_name', 'LIKE', "%{$query}%");
            }
            $results = $results->concat($fulltimeQuery->get()->map(function ($employee) {
                return [
                    'name'        => $employee->employee_name,
                    'type'        => 'Full-time Instructor',
                    'designation' => $employee->designation,
                    'route'       => route('fulltime.index'),
                ];
            }));
        }
        
        // Search in ParttimeTimesheet
        if (empty($typeMatches) || in_array('parttime', $typeMatches)) {
            $parttimeQuery = ParttimeTimesheet::select('employee_name', 'designation')->distinct();
            if (empty($typeMatches)) {
                $parttimeQuery->where('employee_name', 'LIKE', "%{$query}%");
            }
            $results = $results->concat($parttimeQuery->get()->map(function ($employee) {
                return [
                    'name'        => $employee->employee_name,
                    'type'        => 'Part-time Instructor',
                    'designation' => $employee->designation,
                    'route'       => route('parttime.index'),
                ];
            }));
        }
        
        // Search in StaffTimesheet
        if (empty($typeMatches) || in_array('staff', $typeMatches)) {
            $staffQuery = StaffTimesheet::select('employee_name', 'designation')->distinct();
            if (empty($typeMatches)) {
                $staffQuery->where('employee_name', 'LIKE', "%{$query}%");
            }
            $results = $results->concat($staffQuery->get()->map(function ($employee) {
                return [
                    'name'        => $employee->employee_name,
                    'type'        => 'Staff',
                    'designation' => $employee->designation,
                    'route'       => route('staff.index'),
                ];
            }));
        }
        
        // Search in UtilityTimesheet
        if ((empty($typeMatches) || in_array('utility', $typeMatches)) && Schema::hasColumn('utility_timesheets', 'employee_name')) {
            $utilityQuery = UtilityTimesheet::select('employee_name', 'designation')->distinct();
            if (empty($typeMatches)) {
                $utilityQuery->where('employee_name', 'LIKE', "%{$query}%");
            }
            $results = $results->concat($utilityQuery->get()->map(function ($employee) {
                return [
                    'name'        => $employee->employee_name,
                    'type'        => 'Utility',
                    'designation' => $employee->designation,
                    'route'       => route('utility.index'),
                ];
            }));
        }
        
        // Search in Employee table
        if (empty($typeMatches)) {
            $employees = Employee::where('name', 'LIKE', "%{$query}%")
                ->select('name', 'position')
                ->get()
                ->map(function ($employee) {
                    return [
                        'name'        => $employee->name,
                        'type'        => $employee->position,
                        'designation' => $employee->position,
                        'route'       => route('employees.index'),
                    ];
                });
            $results = $results->concat($employees);
        }
        
        $results = $results
            ->reject(function ($item) {
                return isset($item['name']) && strtolower(trim($item['name'])) === 'john smith';
            })
            ->unique('name')
            ->take(15);
        
        return response()->json($results->values());
    }
    
    /**
     * Get comprehensive employee statistics
     */
    private function getEmployeeStatistics()
    {
        $totalFulltimeInstructors = $this->countUniqueEmployees(FulltimeTimesheet::class);
        $totalParttimeInstructors = $this->countUniqueEmployees(ParttimeTimesheet::class);
        $totalStaff                = $this->countUniqueEmployees(StaffTimesheet::class);
        $totalUtility              = $this->countUniqueEmployees(UtilityTimesheet::class);
        $totalWatchman             = $this->countUniqueEmployees(WatchmanTimesheet::class);
        $totalAdminPersonnel       = $this->countUniqueEmployees(AdminPersonnelTimesheet::class);
        $totalEmployees            = $this->calculateTotalUniqueEmployees();

        return compact(
            'totalEmployees',
            'totalFulltimeInstructors',
            'totalParttimeInstructors',
            'totalStaff',
            'totalUtility',
            'totalWatchman',
            'totalAdminPersonnel'
        );
    }

    /**
     * Get department-level analytics for the four academic departments.
     */
    private function getDepartmentAnalytics()
    {
        return DepartmentAnalytics::build();
    }


    /**
     * Count unique employees in a timesheet table by name
     */
    private function countUniqueEmployees($modelClass)
    {
        try {
            $tableName = (new $modelClass)->getTable();
            
            if (!Schema::hasColumn($tableName, 'employee_name')) {
                return 0;
            }
            
            return $modelClass::whereNotNull('employee_name')
                ->where('employee_name', '!=', '')
                ->distinct()
                ->count('employee_name');
                
        } catch (\Exception $e) {
            \Log::error("Error counting employees in {$modelClass}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calculate total unique employees across all timesheet tables
     */
    private function calculateTotalUniqueEmployees()
    {
        try {
            $fulltimeNames = Schema::hasColumn('fulltime_timesheets', 'employee_name')
                ? FulltimeTimesheet::select('employee_name')->whereNotNull('employee_name')->where('employee_name', '!=', '')->distinct()->pluck('employee_name')
                : collect();

            $parttimeNames = Schema::hasColumn('parttime_timesheets', 'employee_name')
                ? ParttimeTimesheet::select('employee_name')->whereNotNull('employee_name')->where('employee_name', '!=', '')->distinct()->pluck('employee_name')
                : collect();

            $staffNames = Schema::hasColumn('staff_timesheets', 'employee_name')
                ? StaffTimesheet::select('employee_name')->whereNotNull('employee_name')->where('employee_name', '!=', '')->distinct()->pluck('employee_name')
                : collect();

            $utilityNames = Schema::hasColumn('utility_timesheets', 'employee_name')
                ? UtilityTimesheet::select('employee_name')->whereNotNull('employee_name')->where('employee_name', '!=', '')->distinct()->pluck('employee_name')
                : collect();

            // watchman_timesheets arrived in the Aug 2026 migration, so it is
            // guarded like the rest rather than assumed present.
            $watchmanNames = Schema::hasColumn('watchman_timesheets', 'employee_name')
                ? WatchmanTimesheet::select('employee_name')->whereNotNull('employee_name')->where('employee_name', '!=', '')->distinct()->pluck('employee_name')
                : collect();

            $adminPersonnelNames = Schema::hasColumn('admin_personnel_timesheets', 'employee_name')
                ? AdminPersonnelTimesheet::select('employee_name')->whereNotNull('employee_name')->where('employee_name', '!=', '')->distinct()->pluck('employee_name')
                : collect();

            return $fulltimeNames
                ->concat($parttimeNames)
                ->concat($staffNames)
                ->concat($utilityNames)
                ->concat($watchmanNames)
                ->concat($adminPersonnelNames)
                ->unique()
                ->count();

        } catch (\Exception $e) {
            \Log::error("Error calculating total unique employees: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * API endpoint for real-time employee statistics
     */
    public function getEmployeeStats()
    {
        return response()->json($this->getEmployeeStatistics());
    }

    // --- Master List Add Employee ---

    public function masterListAddForm(Request $request)
    {
        try {
            $departments = \App\Models\Department::active()->get();
        } catch (\Exception $e) {
            $departments = collect();
        }
        return view('admin.master-list-add', compact('departments'));
    }

    public function masterListAddStore(Request $request)
    {
        $data = $request->validate([
            'employee_name' => 'required|string|max:255',
            'email'         => 'nullable|email',
            'employee_type' => 'required|in:fulltime,parttime,staff,utility',
            'designation'   => 'required|string|max:100',
            'staff_type'    => 'nullable|string|max:100',
            'admin_type'    => 'nullable|string|max:100',
            'prov_abr'      => 'nullable|string|max:10',
            'department'    => 'required|string|max:50',
            'days'          => 'array',
            'days.*'        => 'nullable|numeric|min:0|max:24',
            'details'       => 'nullable|string',
            'total_hour'    => 'nullable|numeric|min:0',
            'rate_per_hour' => 'nullable|numeric|min:0',
            'deduction'     => 'nullable|numeric|min:0',
        ]);

        // If a staff_type or admin_type was selected, use it as the designation
        $designation = $data['designation'];
        if (!empty($data['staff_type'])) {
            $designation = $data['staff_type'];
        } elseif (!empty($data['admin_type'])) {
            $designation = $data['admin_type'];
        }

        $employee = \App\Models\Employee::create([
            'name'        => $data['employee_name'],
            'email'       => $data['email'] ?? null,
            'position'    => match ($data['employee_type']) {
                'fulltime' => 'Full-time Instructor',
                'parttime' => 'Part-time Instructor',
                'staff'    => 'Staff',
                'utility'  => 'Utility',
            },
            'hourly_salary' => $data['rate_per_hour'] ?? 0,
            'department_id' => null,
        ]);

        // BUG FIX #2: Removed the reference to undefined $data['days_json'].
        // Only use the validated 'days' array, defaulting to an empty array.
        $payload = [
            'employee_id'       => $employee->id,
            'employee_name'     => $data['employee_name'],
            'email'             => $data['email'] ?? null,
            'designation'       => $designation,
            'prov_abr'          => $data['prov_abr'] ?? null,
            'department'        => $data['department'],
            'days'              => json_encode($data['days'] ?? []),
            'details'           => $data['details'] ?? null,
            'total_hour'        => $data['total_hour'] ?? 0,
            'rate_per_hour'     => $data['rate_per_hour'] ?? 0,
            'deduction'         => $data['deduction'] ?? 0,
            'total_honorarium'  => max(0, (($data['total_hour'] ?? 0) * ($data['rate_per_hour'] ?? 0)) - ($data['deduction'] ?? 0)),
        ];


        $redirect = route('dashboard');

        switch ($data['employee_type']) {
            case 'fulltime':
                \App\Models\FulltimeTimesheet::create($payload);
                $redirect = route('fulltime.index');
                break;
            case 'parttime':
                \App\Models\ParttimeTimesheet::create($payload);
                $redirect = route('parttime.index');
                break;
            case 'staff':
                \App\Models\StaffTimesheet::create($payload);
                $redirect = route('staff.index');
                break;
            case 'utility':
                \App\Models\UtilityTimesheet::create($payload);
                $redirect = route('utility.index');
                break;
        }

        return redirect($redirect)->with('success', 'Employee added successfully.');
    }

    /**
     * Get detailed employee statistics for debugging
     */
    public function getDetailedEmployeeStats()
    {
        try {
            $stats = $this->getEmployeeStatistics();
            
            $detailed = [
                'summary'   => $stats,
                'breakdown' => [
                    'fulltime_names' => $this->getEmployeeNames(FulltimeTimesheet::class),
                    'parttime_names' => $this->getEmployeeNames(ParttimeTimesheet::class),
                    'staff_names'    => $this->getEmployeeNames(StaffTimesheet::class),
                    'utility_names'  => $this->getEmployeeNames(UtilityTimesheet::class),
                ],
                'table_info' => [
                    'fulltime_table_exists' => Schema::hasTable('fulltime_timesheets'),
                    'parttime_table_exists' => Schema::hasTable('parttime_timesheets'),
                    'staff_table_exists'    => Schema::hasTable('staff_timesheets'),
                    'utility_table_exists'  => Schema::hasTable('utility_timesheets'),
                ],
            ];
            
            return response()->json($detailed);
            
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Failed to get detailed statistics',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get employee names from a specific timesheet table
     */
    private function getEmployeeNames($modelClass)
    {
        try {
            $tableName = (new $modelClass)->getTable();
            
            if (!Schema::hasColumn($tableName, 'employee_name')) {
                return [];
            }
            
            return $modelClass::whereNotNull('employee_name')
                ->where('employee_name', '!=', '')
                ->distinct()
                ->pluck('employee_name')
                ->sort()
                ->values()
                ->toArray();
                
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get instructors by rate
     */
    public function getInstructorsByRate(Request $request)
    {
        $minRate = $request->get('rate_range');
        
        if (!is_numeric($minRate) || $minRate <= 0) {
            return response()->json([
                'error'       => true,
                'message'     => 'Invalid or missing rate value provided. Must be a positive number.',
                'instructors' => [],
            ], 400);
        }

        $minRate = (float) $minRate;

        try {
            // BUG FIX #4: ->distinct('employee_name') is not supported by Eloquent.
            // Use ->distinct() without arguments after selecting the needed columns,
            // then deduplicate in PHP via ->unique('name') on the final collection.
            $fulltimeInstructors = FulltimeTimesheet::select('employee_name', 'designation', 'rate_per_hour')
                ->where('rate_per_hour', '=', $minRate)
                ->distinct()
                ->get()
                ->map(function ($instructor) {
                    return [
                        'name'        => $instructor->employee_name,
                        'designation' => $instructor->designation,
                        'rate'        => $instructor->rate_per_hour,
                        'type'        => 'Full-time Instructor',
                    ];
                });
                
            $parttimeInstructors = ParttimeTimesheet::select('employee_name', 'designation', 'rate_per_hour')
                ->where('rate_per_hour', '=', $minRate)
                ->distinct()
                ->get()
                ->map(function ($instructor) {
                    return [
                        'name'        => $instructor->employee_name,
                        'designation' => $instructor->designation,
                        'rate'        => $instructor->rate_per_hour,
                        'type'        => 'Part-time Instructor',
                    ];
                });
            
            $uniqueInstructors = $fulltimeInstructors
                ->concat($parttimeInstructors)
                ->unique('name')
                ->sortBy('name')
                ->values();
            
            return response()->json([
                'rate_range'  => $minRate,
                'count'       => $uniqueInstructors->count(),
                'instructors' => $uniqueInstructors,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error'       => 'Failed to fetch instructors by rate',
                'message'     => $e->getMessage(),
                'instructors' => [],
            ], 500);
        }
    }

    /**
     * Show master list for all employees
     */
    public function masterList(Request $request)
    {
        $selectedDepartment  = $request->get('department', 'all');
        $selectedEmployeeType = $request->get('employee_type', 'all');

        $employees = collect();

        // --- Full-time Instructors ---
        if ($selectedEmployeeType === 'all' || $selectedEmployeeType === 'fulltime') {
            $query = FulltimeTimesheet::query();
            if ($selectedDepartment !== 'all') {
                if ($selectedDepartment === 'EDUCATION') {
                    $query->whereIn('department', ['BSED', 'BEED']);
                } else {
                    $query->where('department', $selectedDepartment);
                }
            }
            $employees = $employees->concat(
                $query->select('id', 'employee_name', 'email', 'designation', 'department', 'rate_per_hour as rate', 'created_at', DB::raw("'Full-time Instructor' as type"))
                    ->get()
                    ->groupBy('employee_name')
                    ->map->first()
                    ->values()
            );
        }

        // --- Part-time Instructors ---
        if ($selectedEmployeeType === 'all' || $selectedEmployeeType === 'parttime') {
            $query = ParttimeTimesheet::query();
            if ($selectedDepartment !== 'all') {
                if ($selectedDepartment === 'EDUCATION') {
                    $query->whereIn('department', ['BSED', 'BEED']);
                } else {
                    $query->where('department', $selectedDepartment);
                }
            }
            $employees = $employees->concat(
                $query->select('id', 'employee_name', 'email', 'designation', 'department', 'rate_per_hour as rate', 'created_at', DB::raw("'Part-time Instructor' as type"))
                    ->get()
                    ->groupBy('employee_name')
                    ->map->first()
                    ->values()
            );
        }

        // --- Staff (no department filter; they belong to no specific dept) ---
        if ($selectedEmployeeType === 'all' || $selectedEmployeeType === 'staff') {
            $employees = $employees->concat(
                StaffTimesheet::select('id', 'employee_name', DB::raw('null as email'), 'designation', DB::raw('null as department'), 'rate_per_day as rate', 'created_at', DB::raw("'Staff' as type"))
                    ->get()
                    ->groupBy('employee_name')
                    ->map->first()
                    ->values()
            );
        }

        // --- Utility (no department filter) ---
        if ($selectedEmployeeType === 'all' || $selectedEmployeeType === 'utility') {
            $employees = $employees->concat(
                UtilityTimesheet::select('id', 'employee_name', DB::raw('null as email'), 'designation', DB::raw('null as department'), 'rate_per_day as rate', 'created_at', DB::raw("'Utility' as type"))
                    ->get()
                    ->groupBy('employee_name')
                    ->map->first()
                    ->values()
            );
        }

        $employees   = $employees->sortBy('employee_name')->values();
        $departments = ['BSIT', 'BSBA', 'BSHM', 'EDUCATION'];

        return view('admin.master-list', compact('employees', 'selectedDepartment', 'departments', 'selectedEmployeeType'));
    }

    /**
     * Delete selected employees from master list
     */
    public function deleteSelected(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'required|integer',
            'type'  => 'required|in:staff,utility,fulltime,parttime',
        ]);

        try {
            $ids          = $request->input('ids');
            $type         = $request->input('type');
            $deletedCount = 0;
            
            switch ($type) {
                case 'fulltime':
                    $deletedCount = FulltimeTimesheet::whereIn('id', $ids)->delete();
                    break;
                case 'parttime':
                    $deletedCount = ParttimeTimesheet::whereIn('id', $ids)->delete();
                    break;
                case 'staff':
                    $deletedCount = StaffTimesheet::whereIn('id', $ids)->delete();
                    break;
                case 'utility':
                    $deletedCount = UtilityTimesheet::whereIn('id', $ids)->delete();
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted $deletedCount employees from the $type timesheet.",
            ]);

        } catch (\Exception $e) {
            \Log::error("Delete Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete selected entries: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get print data for selected employees
     */
    public function getPrintData(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'required|integer',
            'type'  => 'required|in:staff,utility,fulltime,parttime',
        ]);

        try {
            $ids       = $request->input('ids');
            $type      = $request->input('type');
            $employees = collect();

            switch ($type) {
                case 'staff':
                    $employees = StaffTimesheet::whereIn('id', $ids)->orderBy('employee_name')->get()
                        ->map(function ($e) {
                            $e->type         = 'Staff';
                            $e->department   = null;
                            $e->rate_per_day = $e->rate_per_day ?? 0;
                            $e->total_days   = $e->total_days ?? 0;
                            return $e;
                        });
                    break;
                case 'utility':
                    $employees = UtilityTimesheet::whereIn('id', $ids)->orderBy('employee_name')->get()
                        ->map(function ($e) {
                            $e->type         = 'Utility';
                            $e->department   = null;
                            $e->rate_per_day = $e->rate_per_day ?? 0;
                            $e->total_days   = $e->total_days ?? 0;
                            return $e;
                        });
                    break;
                case 'fulltime':
                    $employees = FulltimeTimesheet::whereIn('id', $ids)->orderBy('department')->orderBy('employee_name')->get()
                        ->map(function ($e) {
                            $e->type         = 'Full-time Instructor';
                            $e->department   = $e->department ?? 'N/A';
                            $e->rate_per_day = $e->rate_per_hour ?? 0;
                            $e->total_days   = $e->total_hour ?? 0;
                            return $e;
                        });
                    break;
                case 'parttime':
                    $employees = ParttimeTimesheet::whereIn('id', $ids)->orderBy('department')->orderBy('employee_name')->get()
                        ->map(function ($e) {
                            $e->type         = 'Part-time Instructor';
                            $e->department   = $e->department ?? 'N/A';
                            $e->rate_per_day = $e->rate_per_hour ?? 0;
                            $e->total_days   = $e->total_hour ?? 0;
                            return $e;
                        });
                    break;
            }

            return response()->json([
                'success'   => true,
                'employees' => $employees,
                'type'      => $type,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting print data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Edit employee in master list
     * BUG FIX #3: Was only passing $id to the view without fetching the actual record.
     * Now resolves the employee from the correct timesheet based on a 'type' query param.
     */
    public function editMasterList(Request $request, $id)
    {
        $type     = $request->get('type', 'fulltime');
        $employee = null;

        switch ($type) {
            case 'fulltime':
                $employee = FulltimeTimesheet::findOrFail($id);
                break;
            case 'parttime':
                $employee = ParttimeTimesheet::findOrFail($id);
                break;
            case 'staff':
                $employee = StaffTimesheet::findOrFail($id);
                break;
            case 'utility':
                $employee = UtilityTimesheet::findOrFail($id);
                break;
            default:
                abort(404, 'Invalid employee type.');
        }

        try {
            $departments = \App\Models\Department::active()->get();
        } catch (\Exception $e) {
            $departments = collect();
        }

        return view('admin.master-list-edit', compact('employee', 'type', 'departments'));
    }

    /**
     * Update employee in master list
     */
    public function updateMasterList(Request $request, $id)
    {
        $type = $request->get('type', 'fulltime');

        $data = $request->validate([
            'email'         => 'nullable|email',
            'prov_abr'      => 'nullable|string|max:10',
            'details'       => 'nullable|string',
            'total_hour'    => 'nullable|numeric|min:0',
            'rate_per_hour' => 'nullable|numeric|min:0',
            'deduction'     => 'nullable|numeric|min:0',
        ]);

        try {
            $updateData = [
                'email'             => $data['email'] ?? null,
                'prov_abr'          => $data['prov_abr'] ?? null,
                'details'           => $data['details'] ?? null,
                'total_hour'        => $data['total_hour'] ?? 0,
                'rate_per_hour'     => $data['rate_per_hour'] ?? 0,
                'deduction'         => $data['deduction'] ?? 0,
                'total_honorarium'  => max(0, (($data['total_hour'] ?? 0) * ($data['rate_per_hour'] ?? 0)) - ($data['deduction'] ?? 0)),
            ];

            switch ($type) {
                case 'fulltime':
                    FulltimeTimesheet::findOrFail($id)->update($updateData);
                    $redirect = route('fulltime.index');
                    break;
                case 'parttime':
                    ParttimeTimesheet::findOrFail($id)->update($updateData);
                    $redirect = route('parttime.index');
                    break;
                case 'staff':
                    StaffTimesheet::findOrFail($id)->update($updateData);
                    $redirect = route('staff.index');
                    break;
                case 'utility':
                    UtilityTimesheet::findOrFail($id)->update($updateData);
                    $redirect = route('utility.index');
                    break;
                default:
                    abort(404, 'Invalid employee type.');
            }

            return redirect($redirect)->with('success', 'Employee updated successfully.');
        } catch (\Exception $e) {
            \Log::error("Error updating employee: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to update employee: ' . $e->getMessage());
        }
    }
}