<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Mail\PayslipMail;
use App\Models\FulltimeTimesheet;
use App\Models\ParttimeTimesheet;
use App\Models\StaffTimesheet;
use App\Models\UtilityTimesheet;
use App\Models\PayslipHistory;

use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon; 
use PDF;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $stats = $this->getEmployeeStatistics();

        $userDepartment = null;
        $user = Auth::user();

        if ($user) {
            $userDepartment = $user->course; 
        }

        $staffPayPeriod = '1-15 and 16-30/31';

        return view('admin.dashboard', [
            'totalEmployees' => $stats['totalEmployees'],
            'totalFulltimeInstructors' => $stats['totalFulltimeInstructors'],
            'totalParttimeInstructors' => $stats['totalParttimeInstructors'],
            'totalStaff' => $stats['totalStaff'],
            'totalUtility' => $stats['totalUtility'],
            'userDepartment' => $userDepartment,
            'staffPayPeriod' => $staffPayPeriod, 
        ]);
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
        $abbrMap = ['Mon'=>'M','Tue'=>'T','Wed'=>'W','Thu'=>'TH','Fri'=>'F','Sat'=>'S','Sun'=>'S'];
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
                PayslipHistory::create([
                    'name' => $payload['employeeName'],
                    'email' => $email,
                    'employee_type' => $payload['type'],
                    'total_honorarium' => $totalHonorariumClean,
                    'designation' => $payload['designation'] ?? 'N/A', 
                    'rate' => $rateValue,
                    'pay_period' => $payload['payPeriod'],
                    'total_hours_or_days' => $daysHoursValue,
                    'days' => $daysHoursValue, 
                    'error' => null,
                    'sent_at' => now(),
                ]);

            } catch (\Throwable $e) {
                Log::error("Failed to send payslip to {$email}: " . $e->getMessage());
                $failed++;
                $errors[] = $email . ': ' . $e->getMessage();

                // Log failure
                PayslipHistory::create([
                    'name' => $payload['employeeName'],
                    'email' => $email,
                    'employee_type' => $payload['type'],
                    'total_honorarium' => $totalHonorariumClean,
                    'designation' => $payload['designation'] ?? 'N/A', 
                    'rate' => $rateValue,
                    'pay_period' => $payload['payPeriod'],
                    'total_hours_or_days' => $daysHoursValue,
                    'days' => $daysHoursValue, 
                    'error' => $e->getMessage(),
                    'sent_at' => now(),
                ]);
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
                PayslipHistory::create([
                    'name' => $payload['employeeName'],
                    'email' => $emailToStore,
                    'employee_type' => $payload['type'],
                    'total_honorarium' => $totalHonorariumClean,
                    'designation' => $payload['designation'] ?? 'N/A', 
                    'rate' => $rateValue,
                    'pay_period' => $payload['payPeriod'],
                    'total_hours_or_days' => $daysHoursValue,
                    'days' => $daysHoursValue, 
                    'error' => null,
                    'sent_at' => now(),
                ]);
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
        $totalEmployees = $this->calculateTotalUniqueEmployees();
        
        return [
            'totalEmployees' => $totalEmployees,
            'totalFulltimeInstructors' => $totalFulltimeInstructors,
            'totalParttimeInstructors' => $totalParttimeInstructors,
            'totalStaff' => $totalStaff,
            'totalUtility' => $totalUtility,
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
                (new UtilityTimesheet)->getTable()
            ];
            
            foreach ($tables as $table) {
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
    private function findAssociatedTimesheet($history)
    {
        $type = strtolower($history->employee_type);
        $payPeriod = $history->pay_period;
        $email = $history->email;
        $name = $history->name;

        try {
            if ($type === 'fulltime' || $type === 'part-time') {
                $dates = explode(' - ', $payPeriod);
                if (count($dates) !== 2) return null;

                $startDate = Carbon::parse(trim($dates[0]));
                $endDate = Carbon::parse(trim($dates[1]));
                
                $model = ($type === 'fulltime') ? FulltimeTimesheet::class : ParttimeTimesheet::class;

                return $model::where(function($query) use ($email, $name, $type) {
                        if ($type === 'fulltime') {
                             $query->where('email', $email);
                        } else {
                            $query->where('employee_name', $name)->orWhere('email', $email);
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
     * Generates the list of days (date, number, abbr) for the given pay period.
     */
    private function getDaysForPeriod($payPeriod, $employeeType)
    {
        $days = [];
        $type = strtolower($employeeType);

        try {
            if ($type === 'fulltime' || $type === 'part-time') {
                $dates = explode(' - ', $payPeriod);
                if (count($dates) !== 2) return [];

                $startDate = Carbon::parse(trim($dates[0]));
                $endDate = Carbon::parse(trim($dates[1]));

                $period = new \Carbon\CarbonPeriod($startDate, $endDate);
                $abbrMap = ['Mon'=>'M','Tue'=>'T','Wed'=>'W','Thu'=>'TH','Fri'=>'F','Sat'=>'S','Sun'=>'S'];

                foreach ($period as $date) {
                    $days[] = [
                        'number' => $date->day,
                        'abbr' => $abbrMap[$date->format('D')] ?? '',
                        'date' => $date->format('Y-m-d'),
                        'is_sunday' => $date->isSunday(),
                    ];
                }

            } elseif ($type === 'staff' || $type === 'utility') {
                // Example: "October 1-15, 2025" or "October 16-end, 2025"
                preg_match('/(\w+)\s+([\d\-end]+),\s+(\d{4})/', $payPeriod, $matches);
                if (count($matches) !== 4) return [];

                $monthName = $matches[1];
                $periodStr = $matches[2]; // "1-15" or "16-end"
                $year = (int)$matches[3];
                $month = Carbon::parse($monthName)->month;

                $periodParts = explode('-', $periodStr);
                $startDay = (int)$periodParts[0];
                
                $endDate = null;
                $startDate = Carbon::createFromDate($year, $month, $startDay);

                if ($periodParts[1] === 'end') {
                    $endDate = $startDate->copy()->endOfMonth();
                } else {
                    $endDay = (int)$periodParts[1];
                    $endDate = Carbon::createFromDate($year, $month, $endDay);
                }

                $period = new \Carbon\CarbonPeriod($startDate, $endDate);
                $abbrMap = ['Mon'=>'M','Tue'=>'T','Wed'=>'W','Thu'=>'TH','Fri'=>'F','Sat'=>'S','Sun'=>'S'];

                foreach ($period as $date) {
                    $days[] = [
                        'number' => $date->day,
                        'abbr' => $abbrMap[$date->format('D')] ?? '',
                        'date' => $date->format('Y-m-d'),
                        'is_sunday' => $date->isSunday(),
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error("Error getting days for pay period '{$payPeriod}': " . $e->getMessage());
            return [];
        }
        
        return $days;
    }
}