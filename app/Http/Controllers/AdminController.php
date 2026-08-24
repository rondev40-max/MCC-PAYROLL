<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Mail\PayslipMail;
use App\Models\FulltimeTimesheet;
use App\Models\ParttimeTimesheet;
use App\Models\StaffTimesheet;
use App\Models\UtilityTimesheet;
use App\Models\WatchmanTimesheet;
use App\Models\AdminPersonnelTimesheet;
use App\Models\PayslipHistory;
use App\Models\Department;
use App\Support\DepartmentAnalytics;
use App\Support\WageLiquidation;

use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon; 
use PDF;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $stats = $this->getEmployeeStatistics();
        $departmentAnalysis = $this->getDepartmentAnalytics();

        $userDepartment = null;
        $user = Auth::user();

        if ($user) {
            $userDepartment = $user->course; 
        }

        $staffPayPeriod = '1-15 and 16-30/31';

        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Two column vocabularies. The original four tables spell the
        // deductions out (withholding_tax / gsis / philhealth / pag_ibig / sss);
        // the watchman and admin-personnel tables added in Aug 2026 use the
        // shorter *_amount names and have no gsis column at all. Summing them
        // with the first expression would fail on an unknown column, which is
        // why they were simply left out of this total until now.
        $legacyTax = 'COALESCE(withholding_tax, 0) + COALESCE(gsis, 0) + COALESCE(philhealth, 0) + COALESCE(pag_ibig, 0) + COALESCE(sss, 0)';
        $amountTax = 'COALESCE(tax_amount, 0) + COALESCE(sss_amount, 0) + COALESCE(phic_amount, 0) + COALESCE(hdmf_amount, 0)';

        $deductionSources = [
            [FulltimeTimesheet::class,        $legacyTax],
            [ParttimeTimesheet::class,        $legacyTax],
            [StaffTimesheet::class,           $legacyTax],
            [UtilityTimesheet::class,         $legacyTax],
            [WatchmanTimesheet::class,        $amountTax],
            [AdminPersonnelTimesheet::class,  $amountTax],
        ];

        $totalGovtDeductions = 0;
        foreach ($deductionSources as [$model, $expression]) {
            $table = (new $model)->getTable();

            if (!Schema::hasTable($table)) {
                continue;
            }

            $totalGovtDeductions += $model::where('month', $currentMonth)
                ->where('year', $currentYear)
                ->sum(DB::raw($expression));
        }

        return view('admin.dashboard', [
            'totalEmployees' => $stats['totalEmployees'],
            'totalFulltimeInstructors' => $stats['totalFulltimeInstructors'],
            'totalParttimeInstructors' => $stats['totalParttimeInstructors'],
            'totalStaff' => $stats['totalStaff'],
            'totalUtility' => $stats['totalUtility'],
            'totalWatchman' => $stats['totalWatchman'],
            'totalAdminPersonnel' => $stats['totalAdminPersonnel'],
            'departmentAnalysis' => $departmentAnalysis,
            'departmentCount' => $departmentAnalysis->count(),
            'userDepartment' => $userDepartment,
            'staffPayPeriod' => $staffPayPeriod, 
            'totalGovtDeductions' => $totalGovtDeductions,
        ]);
    }

    /**
     * Get department-level analytics for the four academic departments.
     */
    private function getDepartmentAnalytics()
    {
        return DepartmentAnalytics::build();
    }

    /**
     * Display the activity log page.
     */
    public function activityLog()
    {
        $activities = Activity::with('causer')
            ->latest()
            ->paginate(25);

        return view('admin.activity-log', ['activities' => $activities]);
    }

    /**
     * Display history of sent payslips with optional filters (Old History View)
     */
    public function history(Request $request)
    {
        try {
            $query = PayslipHistory::orderByDesc('sent_at');

            if ($request->filled('email')) {
                $query->where('email', 'like', '%' . $request->email . '%');
            }

            $histories = $query->paginate(20)->appends($request->query());

            return view('admin.history', [
                'histories' => $histories,
                'batches' => collect(), 
                'tableReady' => true,
            ]);
        } catch (\Throwable $e) {
            return view('admin.history', [
                'histories' => collect(),
                'batches' => collect(),
                'tableReady' => false,
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }

    // Soft delete a history record (AJAX or form)
    public function historySoftDelete($id)
    {
        $history = PayslipHistory::find($id);
        if (!$history) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        $history->delete();
        return response()->json(['message' => 'Record moved to trash.']);
    }

    // Soft delete selected history records (for 'Delete Selected' button)
    public function massSoftDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:payslip_histories,id']);

        PayslipHistory::whereIn('id', $request->input('ids'))->delete();

        return response()->json(['message' => count($request->input('ids')) . ' record(s) moved to trash.']);
    }

    // Soft delete ALL history records (for 'Delete All' button in history.blade)
    public function historySoftDeleteAll()
    {
        $count = PayslipHistory::count();
        if ($count === 0) {
            return response()->json(['message' => 'No records found to delete.'], 404);
        }
        
        PayslipHistory::query()->delete();
        
        return response()->json(['message' => "Successfully moved all {$count} record(s) to trash."]);
    }

    // NEW: Permanently delete ALL history records (for 'Delete All Permanently' in trash.blade)
    public function historyForceDeleteAll()
    {
        $count = PayslipHistory::onlyTrashed()->forceDelete(); 

        if ($count === 0) {
            return response()->json(['message' => 'No records found in trash to delete permanently.'], 404);
        }
        
        return response()->json(['message' => "Successfully permanently deleted {$count} record(s) from trash."]);
    }
    
    // Permanently delete a history record
    public function historyForceDelete($id)
    {
        $history = PayslipHistory::withTrashed()->find($id);
        if (!$history) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        $history->forceDelete();
        return response()->json(['message' => 'Record permanently deleted.']);
    }

    // Display trash records (soft deleted)
    public function trash(Request $request)
    {
        try {
            $query = PayslipHistory::withTrashed()->whereNotNull('deleted_at')->orderByDesc('deleted_at');

            if ($request->filled('email')) {
                $query->where('email', 'like', '%' . $request->email . '%');
            }

            $histories = $query->paginate(20)->appends($request->query());

            return view('admin.trash', [
                'histories' => $histories,
                'batches' => collect(), 
                'tableReady' => true,
            ]);
        } catch (\Throwable $e) {
            return view('admin.trash', [
                'histories' => collect(),
                'batches' => collect(),
                'tableReady' => false,
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }

    // Restore a soft deleted history record
    public function historyRestore($id)
    {
        $history = PayslipHistory::withTrashed()->find($id);
        if (!$history) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        $history->restore();
        return response()->json(['message' => 'Record restored to history.']);
    }

    // ------------------------------------------
    // PAYROLL HISTORY (NEW VIEW)
    // ------------------------------------------

    /**
     * Display the payroll history records with filtering by month and year.
     */
    public function payrollHistory(Request $request)
    {
        $selectedMonth = $request->input('month', now()->month);
        $selectedYear = $request->input('year', now()->year);
        $selectedPeriod = $request->input('period', 'all');

        $recordsQuery = PayslipHistory::query()
            ->whereYear('sent_at', $selectedYear)
            ->whereMonth('sent_at', $selectedMonth);
        
        if ($selectedPeriod !== 'all') {
            $periodMap = [
                '1-15' => '1-15',
                '16-end' => '16-end', 
            ];

            $monthNameFilter = Carbon::createFromDate(null, $selectedMonth, 1)->format('F');

            if (array_key_exists($selectedPeriod, $periodMap)) {
                $searchTerm = $periodMap[$selectedPeriod];
                $recordsQuery->where('pay_period', 'LIKE', "{$monthNameFilter} {$searchTerm}, {$selectedYear}");
            }
        }

        // Totals over everything the filter matches, before pagination.
        // The view summed $record->total_honorarium as it rendered rows and
        // labelled the result "GRAND TOTAL HONORARIUM" — but only 15 records
        // are on a page, so the figure was a page subtotal presented as the
        // payroll total, and it changed every time you clicked to page 2.
        $totals = (clone $recordsQuery)
            ->selectRaw('COUNT(*) as record_count')
            ->selectRaw('COALESCE(SUM(total_honorarium), 0) as gross')
            ->selectRaw('COALESCE(SUM(total_deductions), 0) as deductions')
            ->selectRaw('COALESCE(SUM(COALESCE(net_pay, total_honorarium)), 0) as net')
            ->selectRaw('SUM(CASE WHEN error IS NOT NULL THEN 1 ELSE 0 END) as failed')
            ->reorder()
            ->first();

        $records = $recordsQuery
            ->orderBy('sent_at', 'desc')
            ->paginate(15);

        $holidays = \App\Models\Holiday::pluck('date')->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))->toArray();

        foreach ($records as $record) {
            $record->timesheet = $this->findAssociatedTimesheet($record);
            $record->days_in_period = $this->getDaysForPeriod($record->pay_period, $record->employee_type);
        }

        $months = [];
        for ($m=1; $m<=12; $m++) {
            $months[$m] = Carbon::createFromDate(null, $m, 1)->format('F');
        }

        $years = range(now()->year, now()->subYears(5)->year);

        return view('admin.payroll-history', [
            'records' => $records,
            'totals' => $totals,
            'months' => $months,
            'years' => $years,
            'selectedMonth' => (int)$selectedMonth,
            'selectedYear' => (int)$selectedYear,
            'selectedPeriod' => $selectedPeriod,
            'holidays' => $holidays,
        ]);
    }

    /**
     * Export the payroll history records as a PDF file.
     */
    public function exportPayrollHistory(Request $request)
    {
        $selectedMonth = $request->input('month');
        $selectedYear = $request->input('year');
        $selectedPeriod = $request->input('period', 'all');

        if (empty($selectedMonth) || empty($selectedYear)) {
            $selectedMonth = now()->month;
            $selectedYear = now()->year;
        }
        
        $months = [];
        for ($m=1; $m<=12; $m++) {
            $months[$m] = Carbon::createFromDate(null, $m, 1)->format('F');
        }

        $records = PayslipHistory::query()
            ->whereYear('sent_at', $selectedYear)
            ->whereMonth('sent_at', $selectedMonth);
        
        if ($selectedPeriod !== 'all') {
            $periodMap = [
                '1-15' => '1-15',
                '16-end' => '16-end',
            ];

            if (array_key_exists($selectedPeriod, $periodMap)) {
                $searchTerm = $periodMap[$selectedPeriod];
                $monthNameFilter = $months[$selectedMonth];
                $records->where('pay_period', 'LIKE', "{$monthNameFilter} {$searchTerm}, {$selectedYear}");
            }
        }

        $records = $records->orderBy('sent_at', 'desc')->get();
        
        if ($records->isEmpty()) {
             return back()->with('info', 'No records found to export for the selected month/year/period.');
        }

        $monthName = Carbon::createFromDate(null, $selectedMonth, 1)->format('F');
        $filename = "payroll_history_{$monthName}_{$selectedYear}" . ($selectedPeriod !== 'all' ? "_{$selectedPeriod}" : '') . ".pdf";

        $holidays = \App\Models\Holiday::pluck('date')->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))->toArray();

        foreach ($records as $record) {
            $record->timesheet = $this->findAssociatedTimesheet($record);
            $record->days_in_period = $this->getDaysForPeriod($record->pay_period, $record->employee_type);
        }

        $pdf = PDF::loadView('admin.pdf.payroll-history-pdf', [
            'records' => $records,
            'monthName' => $monthName,
            'selectedYear' => $selectedYear,
            'selectedPeriod' => $selectedPeriod,
            'holidays' => $holidays,
        ]);

        $pdf->setPaper('a4', 'landscape'); 

        return $pdf->download($filename);
    }

    // ------------------------------------------
    // SEND PAYSLIPS (FIXED LOGIC FOR ALL TYPES)
    // ------------------------------------------

    /**
     * Send payslips to all employees across all timesheet tables
     * Fixed: Staff and Utility employees now properly processed
     */
    public function sendPayslips(Request $request)
{
    set_time_limit(300);

    $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date',
    ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $year = $startDate->year;
        $month = $startDate->month;

        // Determine period based on start_date's day
        if ($startDate->day <= 15) {
            $staffPeriods = ['1-15'];
        } else {
            $staffPeriods = ['16-end'];
        }

        // IMPORTANT: define staff month/year variables before using them below
        $staffMonth = $startDate->month;
        $staffYear = $startDate->year;
        $staffMonthName = $startDate->format('F'); // e.g., "December"

        $staffPayPeriodForEmail = $startDate->format('F d, Y') . ' - ' . $endDate->format('F d, Y');

        // 2. Determine the pay period string for Fulltime/Parttime
        $payPeriod = $startDate->format('F d, Y') . ' - ' . $endDate->format('F d, Y');

        $recipients = collect();
        $utilityRecordsWithoutEmail = collect();
        $infoMessages = [];

        // --- START: Prepare days and holidays for the selected date range ---
        $holidays = \App\Models\Holiday::whereBetween('date', [$startDate, $endDate])
            ->pluck('date')
            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
            ->toArray();

        $days = [];
        $abbrMap = ['Mon'=>'Mon','Tue'=>'Tue','Wed'=>'Wed','Thu'=>'Thu','Fri'=>'Fri','Sat'=>'Sat','Sun'=>'Sun'];
        $period = new \Carbon\CarbonPeriod($startDate, $endDate);

        foreach ($period as $date) {
            $days[] = [
                'number' => $date->day,
                'abbr' => $abbrMap[$date->format('D')] ?? '',
                'date' => $date->format('Y-m-d')
            ];
        }
        // --- END: Prepare days and holidays ---
        // --- Fulltime (query by month/year derived from date + period) ---
        // Email column can vary between tables/versions (some use `employee_email`).
        // So we do NOT strictly filter by `email` only; we compute a fallback key below.
        $fulltimeRows = FulltimeTimesheet::whereYear('date', $staffYear)
            ->whereMonth('date', $staffMonth)
            ->whereIn('period', $staffPeriods)
            ->orderByDesc('id')
            ->get()
            ->unique('employee_name');

        foreach ($fulltimeRows as $row) {
            $emailKey = trim((string)($row->employee_email ?? $row->email));
            if ($emailKey === '' || $recipients->has($emailKey)) continue;



            $totalHours      = (float)($row->total_hour ?? 0);
            $ratePerHour     = (float)($row->rate_per_hour ?? 0);
            $totalHonorarium = (float)($row->total_honorarium ?? 0);

            if ($totalHonorarium == 0 && $totalHours > 0 && $ratePerHour > 0) {
                $totalHonorarium = $totalHours * $ratePerHour;
            }

            $periodStr           = $row->period ?? ($staffPeriods[0] ?? '1-15');
            $payPeriodForHistory = $staffMonthName . ' ' . $periodStr . ', ' . $staffYear;

            $recipients->put($emailKey, [
                'employeeName'     => $row->employee_name,
                'designation'      => $row->designation,
                'department'       => $row->department,
                'payPeriod'        => $payPeriodForHistory,
                'totalDaysOrHours' => $totalHours . ' hours',
                'rate'             => $ratePerHour,
                'totalHonorarium'  => $totalHonorarium,
                'type'             => 'Fulltime',
                'timesheet'        => $row,
            ]);
        }

        // --- Part-time (has month/year columns) ---
        // Email can be stored in `employee_email` instead of `email`.
        $parttimeRows = ParttimeTimesheet::where('month', $staffMonth)
            ->where('year', $staffYear)
            ->whereIn('period', $staffPeriods)
            ->orderByDesc('id')
            ->get()
            ->unique('employee_name');

        foreach ($parttimeRows as $row) {
            $emailKey = trim((string)($row->employee_email ?? $row->email));
            if ($emailKey === '') continue;
            if ($recipients->has($emailKey)) continue;



            $totalHours      = (float)($row->total_hour ?? 0);
            $ratePerHour     = (float)($row->rate_per_hour ?? 0);
            $totalHonorarium = (float)($row->total_honorarium ?? 0);

            if ($totalHonorarium == 0 && $totalHours > 0 && $ratePerHour > 0) {
                $totalHonorarium = $totalHours * $ratePerHour;
            }

            $periodStr           = $row->period ?? ($staffPeriods[0] ?? '1-15');
            $payPeriodForHistory = $staffMonthName . ' ' . $periodStr . ', ' . $staffYear;

            $recipients->put($emailKey, [
                'employeeName'     => $row->employee_name,
                'designation'      => $row->designation,
                'department'       => $row->department,
                'payPeriod'        => $payPeriodForHistory,
                'totalDaysOrHours' => $totalHours . ' hours',
                'rate'             => $ratePerHour,
                'totalHonorarium'  => $totalHonorarium,
                'type'             => 'Part-time',
                'timesheet'        => $row,
            ]);
        }
        // --- Staff & Utility (FIXED LOGIC) ---
        Log::info('Staff/Utility Query Parameters:', [
            'month_int' => $staffMonth,
            'month_name' => $staffMonthName,
            'year' => $staffYear,
            'periods' => $staffPeriods,
            'pay_period_format' => $staffPayPeriodForEmail,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
        ]);

            // --- Staff ---
        $staffRows = StaffTimesheet::query()
            ->where(function ($query) use ($staffMonth, $staffMonthName) {
                $query->where('month', $staffMonth)
                      ->orWhere('month', 'LIKE', $staffMonthName . '%');
            })
            ->where('year', $staffYear)
            ->whereIn('period', $staffPeriods)
            ->orderByDesc('id')
            ->get();

        Log::info('Staff Records Found:', [
            'count' => $staffRows->count(),
            'query_month' => $staffMonth,
            'query_year' => $staffYear,
            'query_periods' => $staffPeriods
        ]);

        if ($staffRows->isNotEmpty()) {
            $infoMessages[] = "Found {$staffRows->count()} Staff employee(s) for period " . implode(', ', $staffPeriods) . " of {$startDate->format('F Y')}.";
        }

        foreach ($staffRows as $row) {
            if (!$row->email || $recipients->has($row->email)) continue;
            
            // Ensure total_days is properly calculated
            $totalDays = (float)($row->total_days ?? 0);
            $ratePerDay = (float)($row->rate_per_day ?? 0);
            $totalHonorarium = (float)($row->total_honorarium ?? 0);

            // If total_days is missing or zero, try computing from stored `days` JSON or daily hour fields
            if ($totalDays == 0) {
                // Try `days` array (may be JSON stored in `days` column)
                if (!empty($row->days) && is_array($row->days)) {
                    $computedDays = 0;
                    foreach ($row->days as $d) {
                        if (is_numeric($d) && (float)$d > 0) {
                            $computedDays++;
                        } elseif (is_string($d) && trim($d) !== '') {
                            $computedDays++;
                        }
                    }
                    if ($computedDays > 0) {
                        $totalDays = (float)$computedDays;
                    }
                }

                // Fallback: count daily hour columns (mon_hours..sun_hours) with hours > 0
                if ($totalDays == 0) {
                    $dayFields = ['mon_hours','tue_hours','wed_hours','thu_hours','fri_hours','sat_hours','sun_hours'];
                    $computedDays = 0;
                    foreach ($dayFields as $f) {
                        if (!empty($row->$f) && (float)$row->$f > 0) {
                            $computedDays++;
                        }
                    }
                    if ($computedDays > 0) {
                        $totalDays = (float)$computedDays;
                    }
                }
            }

            // If honorarium is 0 but we have days and rate, recalculate (consider deduction)
            if ($totalHonorarium == 0 && $totalDays > 0 && $ratePerDay > 0) {
                $totalHonorarium = $totalDays * $ratePerDay - (float)($row->deduction ?? 0);
                $totalHonorarium = max(0, $totalHonorarium);
            }
            
            // Build pay period string in format "Month 1-15, YYYY" or "Month 16-end, YYYY"
            $periodStr = $row->period ?? ($staffPeriods[0] ?? '1-15');
            $payPeriodForHistory = $staffMonthName . ' ' . $periodStr . ', ' . $staffYear;

            $recipients->put($row->email, [
                'employeeName' => $row->employee_name,
                'designation' => $row->designation,
                'department' => $row->department,
                'payPeriod' => $payPeriodForHistory,
                'totalDaysOrHours' => $totalDays . ' days',
                'rate' => $ratePerDay, 
                'totalHonorarium' => $totalHonorarium, 
                'type' => 'Staff',
                'timesheet' => $row,
            ]);
        }

        // --- Utility (ALL records) ---
        $utilityRows = UtilityTimesheet::with(['employee'])
            ->where(function ($query) use ($staffMonth, $staffMonthName) {
                $query->where('month', $staffMonth)
                      ->orWhere('month', 'LIKE', $staffMonthName . '%');
            })
            ->where('year', $staffYear)
            ->whereIn('period', $staffPeriods)
            ->orderByDesc('id')
            ->get();


        Log::info('Utility Records Found:', [
            'count' => $utilityRows->count(),
            'query_month' => $staffMonth,
            'query_year' => $staffYear,
            'query_periods' => $staffPeriods
        ]);

        if ($utilityRows->isNotEmpty()) {
            $infoMessages[] = "Found {$utilityRows->count()} Utility employee(s) for period " . implode(', ', $staffPeriods) . " of {$startDate->format('F Y')}.";
        }

        foreach ($utilityRows as $row) {
            $emailKey = optional($row->employee)->email;
            
            // Ensure total_days is properly calculated
            $totalDays = (float)($row->total_days ?? 0);
            $ratePerDay = (float)($row->rate_per_day ?? 0);
            $totalHonorarium = (float)($row->total_honorarium ?? 0);

            // If total_days is missing or zero, try computing from stored `days` JSON or daily hour fields
            if ($totalDays == 0) {
                if (!empty($row->days) && is_array($row->days)) {
                    $computedDays = 0;
                    foreach ($row->days as $d) {
                        if (is_numeric($d) && (float)$d > 0) {
                            $computedDays++;
                        } elseif (is_string($d) && trim($d) !== '') {
                            $computedDays++;
                        }
                    }
                    if ($computedDays > 0) {
                        $totalDays = (float)$computedDays;
                    }
                }

                if ($totalDays == 0) {
                    $dayFields = ['mon_hours','tue_hours','wed_hours','thu_hours','fri_hours','sat_hours','sun_hours'];
                    $computedDays = 0;
                    foreach ($dayFields as $f) {
                        if (!empty($row->$f) && (float)$row->$f > 0) {
                            $computedDays++;
                        }
                    }
                    if ($computedDays > 0) {
                        $totalDays = (float)$computedDays;
                    }
                }
            }

            // If honorarium is 0 but we have days and rate, recalculate (consider deduction)
            if ($totalHonorarium == 0 && $totalDays > 0 && $ratePerDay > 0) {
                $totalHonorarium = $totalDays * $ratePerDay - (float)($row->deduction ?? 0);
                $totalHonorarium = max(0, $totalHonorarium);
            }
            
            // May email - send payslip
            
            
                if ($emailKey && !$recipients->has($emailKey)) {

                $periodStr = $row->period ?? ($staffPeriods[0] ?? '1-15');
                $payPeriodForHistory = $staffMonthName . ' ' . $periodStr . ', ' . $staffYear;

                $recipients->put($emailKey, [
                    'employeeName' => $row->employee_name,
                    'designation' => $row->designation,
                    'department' => $row->department,
                    'payPeriod' => $payPeriodForHistory, 
                    'totalDaysOrHours' => $totalDays . ' days',
                    'rate' => $ratePerDay, 
                    'totalHonorarium' => $totalHonorarium, 
                    'type' => 'Utility',
                    'timesheet' => $row,
                ]);
            }
            // Walang email - i-record lang sa history
                elseif (!$emailKey) {
                $periodStr = $row->period ?? ($staffPeriods[0] ?? '1-15');
                $payPeriodForHistory = $staffMonthName . ' ' . $periodStr . ', ' . $staffYear;

                $utilityRecordsWithoutEmail->push([
                    'employeeName' => $row->employee_name,
                    'designation' => $row->designation,
                    'department' => $row->department,
                    'payPeriod' => $payPeriodForHistory, 
                    'totalDaysOrHours' => $totalDays . ' days',
                    'rate' => $ratePerDay, 
                    'totalHonorarium' => $totalHonorarium, 
                    'type' => 'Utility',
                    'timesheet' => $row,
                ]);
            }
        }

        if ($utilityRecordsWithoutEmail->isNotEmpty()) {
            $infoMessages[] = "{$utilityRecordsWithoutEmail->count()} Utility employee(s) without email will be recorded in history only (no email sent).";
        }

        if ($recipients->isEmpty() && $utilityRecordsWithoutEmail->isEmpty()) {
            return back()->with('error', 'No employees found in the selected date range to process payslips.');
        }

        $sent = 0; $failed = 0; $recorded = 0; $errors = [];

        /**
         * The columns every payslip record shares, itemised wage included.
         *
         * The breakdown is copied off the timesheet here, as the payslip is
         * written, and never recomputed afterwards. Timesheets stay editable
         * once a payroll run is done, so rebuilding a payslip later would show
         * the employee different figures from the ones they were emailed.
         */
        $payslipAttributes = function (array $payload, float $rate, float $units, float $gross): array {
            $timesheet = $payload['timesheet'] ?? null;

            return array_merge([
                'name'                => $payload['employeeName'],
                'employee_type'       => $payload['type'],
                'total_honorarium'    => $gross,
                'designation'         => $payload['designation'] ?? 'N/A',
                'rate'                => $rate,
                'rate_unit'           => in_array($payload['type'], ['Staff', 'Utility'], true) ? 'day' : 'hour',
                'pay_period'          => $payload['payPeriod'],
                'total_hours_or_days' => $units,
                'days'                => $units,
                'source_type'         => $payload['type'],
                'source_id'           => $timesheet->id ?? null,
                'sent_at'             => now(),
            ], WageLiquidation::fromTimesheet($timesheet, $gross));
        };

        // --- Process recipients with email (Send Email + Record) ---
        foreach ($recipients as $email => $payload) {
            $rateValue = (float) $payload['rate'];
            $daysHoursValue = (float) filter_var($payload['totalDaysOrHours'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $totalHonorariumClean = (float) $payload['totalHonorarium'];
            $rateUnit = in_array($payload['type'], ['Staff', 'Utility']) ? '/day' : '/hour';
            
            // Format the display values for the email
            $displayDaysHours = $daysHoursValue . (in_array($payload['type'], ['Staff', 'Utility']) ? ' days' : ' hours');
            
            try {
                Mail::to($email)->send(new PayslipMail(
                    employeeName: $payload['employeeName'],
                    designation: $payload['designation'],
                    department: $payload['department'] ?? '', 
                    payPeriod: $payload['payPeriod'],
                    totalDaysOrHours: $displayDaysHours,
                    rate: '₱' . number_format($rateValue, 2) . $rateUnit, 
                    totalHonorarium: '₱' . number_format($totalHonorariumClean, 2), 
                    timesheet: $payload['timesheet'],
                    days: $days,
                    holidays: $holidays
                ));
                $sent++;

                // Log success
                PayslipHistory::create(array_merge(
                    $payslipAttributes($payload, $rateValue, $daysHoursValue, $totalHonorariumClean),
                    ['email' => $email, 'error' => null]
                ));

            } catch (\Throwable $e) {
                Log::error("Failed to send payslip to {$email}: " . $e->getMessage());
                $failed++;
                $errors[] = $email . ': ' . $e->getMessage();

                // Log failure
                PayslipHistory::create(array_merge(
                    $payslipAttributes($payload, $rateValue, $daysHoursValue, $totalHonorariumClean),
                    ['email' => $email, 'error' => $e->getMessage()]
                ));
            }
        }

        // --- Process Utility records WITHOUT email (record only) ---
        foreach ($utilityRecordsWithoutEmail as $payload) {

            $rateValue = (float) $payload['rate'];
            $daysHoursValue = (float) filter_var($payload['totalDaysOrHours'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $totalHonorariumClean = (float) $payload['totalHonorarium'];

            // Try to attach a real email from the UtilityTimesheet payload (if present)
            $emailToStore = $payload['email'] ?? 'No Email Available';

            try {
                PayslipHistory::create(array_merge(
                    $payslipAttributes($payload, $rateValue, $daysHoursValue, $totalHonorariumClean),
                    ['email' => $emailToStore, 'error' => null]
                ));
                $recorded++;
            } catch (\Throwable $e) {
                Log::error("Failed to record Utility without email: " . $e->getMessage());
            }
        }


        // --- Build Final Message ---
        $finalMessage = '';
        if ($sent > 0) {
            $finalMessage .= "Successfully sent {$sent} payslip(s). ";
        }
        if ($failed > 0) {
            $finalMessage .= "Failed to send {$failed} payslip(s). ";
            if (config('app.debug') && !empty($errors)) {
                $finalMessage .= 'Errors: ' . implode(' | ', array_slice($errors, 0, 3));
            }
        }
        if ($recorded > 0) {
            $finalMessage .= "Recorded {$recorded} Utility employee(s) without email in history. ";
        }
        if (!empty($infoMessages)) {
            $finalMessage .= 'Info: ' . implode(' ', $infoMessages);
        }

        if ($sent > 0 && $failed == 0) {
            return back()->with('success', $finalMessage);
        } elseif (($sent > 0 || $recorded > 0) && $failed > 0) {
            return back()->with('warning', $finalMessage);
        } elseif ($sent == 0 && $failed > 0) {
            return back()->with('error', $finalMessage);
        } elseif ($sent > 0 || $recorded > 0) {
            return back()->with('success', $finalMessage);
        }

        return back()->with('info', 'No payslips were processed.');
    }
    
    // ------------------------------------------
    // PRIVATE STATS METHODS
    // ------------------------------------------

    private function getEmployeeStatistics()
    {
        $totalFulltimeInstructors = $this->countUniqueEmployees(FulltimeTimesheet::class);
        $totalParttimeInstructors = $this->countUniqueEmployees(ParttimeTimesheet::class);
        $totalStaff = $this->countUniqueEmployees(StaffTimesheet::class);
        $totalUtility = $this->countUniqueEmployees(UtilityTimesheet::class);
        $totalWatchman = $this->countUniqueEmployees(WatchmanTimesheet::class);
        $totalAdminPersonnel = $this->countUniqueEmployees(AdminPersonnelTimesheet::class);
        $totalEmployees = $this->calculateTotalUniqueEmployees();

        return [
            'totalEmployees' => $totalEmployees,
            'totalFulltimeInstructors' => $totalFulltimeInstructors,
            'totalParttimeInstructors' => $totalParttimeInstructors,
            'totalStaff' => $totalStaff,
            'totalUtility' => $totalUtility,
            'totalWatchman' => $totalWatchman,
            'totalAdminPersonnel' => $totalAdminPersonnel,
        ];
    }

    private function countUniqueEmployees($modelClass)
    {
        try {
            $tableName = (new $modelClass)->getTable();
            return DB::table($tableName)
                ->distinct()
                ->count('employee_name');
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function calculateTotalUniqueEmployees()
    {
        try {
            $allNames = collect();

            $tables = [
                (new FulltimeTimesheet)->getTable(),
                (new ParttimeTimesheet)->getTable(),
                (new StaffTimesheet)->getTable(),
                (new UtilityTimesheet)->getTable(),
                (new WatchmanTimesheet)->getTable(),
                (new AdminPersonnelTimesheet)->getTable(),
            ];

            foreach ($tables as $table) {
                // Guarded per table: without this, one missing table throws and
                // the catch below zeroes the entire headcount. watchman_timesheets
                // in particular only exists after the Aug 2026 migration.
                if (!Schema::hasTable($table)) {
                    continue;
                }

                $names = DB::table($table)->distinct()->pluck('employee_name');
                $allNames = $allNames->merge($names);
            }
            
            return $allNames->unique()->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Finds the associated Timesheet record for a PayslipHistory entry.
     */
    /**
     * Which model holds a given employee type's timesheets.
     *
     * Keyed by the lowercased `employee_type` / `source_type` that
     * sendPayslips() writes onto the payslip.
     */
    private const TIMESHEET_MODELS = [
        'fulltime'        => FulltimeTimesheet::class,
        'part-time'       => ParttimeTimesheet::class,
        'parttime'        => ParttimeTimesheet::class,
        'staff'           => StaffTimesheet::class,
        'utility'         => UtilityTimesheet::class,
        'watchman'        => WatchmanTimesheet::class,
        'admin personnel' => AdminPersonnelTimesheet::class,
    ];

    /**
     * Parse the pay period a payslip actually stores.
     *
     * sendPayslips() writes one format for every employee type:
     * "August 1-15, 2026" or "August 16-end, 2026". Both this and
     * getDaysForPeriod() used to expect "date - date" for fulltime and
     * part-time, split on ' - ', get a single element back and give up — so the
     * daily-hours grid, which is 45% of the payroll history table, showed "No
     * Timesheet" for every fulltime and part-time employee on the payroll.
     *
     * @return array{0: Carbon, 1: Carbon}|null
     */
    private function parsePayPeriod(?string $payPeriod): ?array
    {
        if (!preg_match('/([A-Za-z]+)\s+(\d{1,2})\s*-\s*(\d{1,2}|end),\s*(\d{4})/i', (string) $payPeriod, $m)) {
            return null;
        }

        try {
            $start = Carbon::createFromFormat('F j Y', "{$m[1]} {$m[2]} {$m[4]}")->startOfDay();
        } catch (\Exception $e) {
            return null;
        }

        $end = strcasecmp($m[3], 'end') === 0
            ? $start->copy()->endOfMonth()->startOfDay()
            : $start->copy()->day((int) $m[3]);

        return $end->lt($start) ? null : [$start, $end];
    }

    private function findAssociatedTimesheet($history)
    {
        $type = strtolower((string) $history->employee_type);
        $payPeriod = $history->pay_period;
        $email = $history->email;
        $name = $history->name;

        // Payslips issued since the breakdown migration record exactly which
        // row produced them, so there is nothing to infer. Everything below is
        // the fallback for records written before that.
        if ($history->source_type && $history->source_id) {
            $model = self::TIMESHEET_MODELS[strtolower($history->source_type)] ?? null;

            if ($model && ($found = $model::find($history->source_id))) {
                return $found;
            }
        }

        try {
            if ($type === 'fulltime' || $type === 'part-time' || $type === 'parttime') {
                $range = $this->parsePayPeriod($payPeriod);
                if (!$range) return null;

                [$startDate, $endDate] = $range;

                $model = self::TIMESHEET_MODELS[$type];

                return $model::where(function($query) use ($email, $name) {
                        // Match on either identifier. Keying fulltime off email
                        // alone lost every row whose email was left blank on the
                        // timesheet, which the master list permits.
                        $query->where('employee_name', $name);
                        if (!empty($email)) {
                            $query->orWhere('email', $email);
                        }
                    })
                    ->whereDate('date', '>=', $startDate)
                    ->whereDate('date', '<=', $endDate)
                    ->orderByDesc('id')
                    ->first();

            } elseif ($type === 'staff' || $type === 'utility') {
                // Example: "October 1-15, 2025" or "October 16-end, 2025"
                preg_match('/(\w+)\s+([\d\-end]+),\s+(\d{4})/', $payPeriod, $matches);
                if (count($matches) !== 4) return null;
                
                $monthName = $matches[1];
                $period = $matches[2]; // "1-15" or "16-end"
                $year = (int)$matches[3];
                $month = Carbon::parse($monthName)->month;

                $model = ($type === 'staff') ? StaffTimesheet::class : UtilityTimesheet::class;
                
                // Use email for Staff, name for Utility (as per previous logic)
                $query = $model::where('month', $month)
                    ->where('year', $year)
                    ->where('period', $period);
                
                if ($type === 'staff') {
                    $query->where('email', $email);
                } else { // utility
                    $query->where('employee_name', $name);
                }

                return $query->orderByDesc('id')->first();
            }
        } catch (\Exception $e) {
            Log::error("Error finding associated timesheet for history ID {$history->id}: " . $e->getMessage());
            return null;
        }

        return null;
    }

    /**
     * The calendar days a pay period covers, for the daily-hours grid.
     *
     * One parser for every employee type. This used to branch: staff and
     * utility got a regex matching the format payslips actually store, while
     * fulltime and part-time were split on " - " and returned an empty array
     * every time — so their grid never rendered.
     */
    private function getDaysForPeriod($payPeriod, $employeeType)
    {
        $range = $this->parsePayPeriod($payPeriod);

        if (!$range) {
            return [];
        }

        [$startDate, $endDate] = $range;
        $days = [];

        foreach (new \Carbon\CarbonPeriod($startDate, $endDate) as $date) {
            $days[] = [
                'number'    => $date->day,
                'abbr'      => $date->format('D'),
                'date'      => $date->format('Y-m-d'),
                'is_sunday' => $date->isSunday(),
            ];
        }

        return $days;
    }
}