<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FulltimeTimesheet;
use App\Models\Department;
use App\Models\Holiday; 
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class FulltimeTimesheetController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $period = $request->get('period', 'auto'); // 'auto', '1-15', '16-end'

        // NOTE: Sa index, kinukuha mo ang ALL timesheets. Pwede mo rin i-filter ito by period/month/year.
        // Pero para mas madaling i-manage sa index view (kung may filtering doon), hayaan muna natin ang FulltimeTimesheet::all();
        $timesheets = FulltimeTimesheet::all(); 

        // Generate days for the selected month
        $baseDate = \Carbon\Carbon::create($year, $month, 1);
        $daysInMonth = $baseDate->daysInMonth;

        // Determine period based on selection or auto-detection
        if ($period === '1-15') {
            $startDay = 1;
            $endDay = 15;
        } elseif ($period === '16-end') {
            $startDay = 16;
            $endDay = $daysInMonth;
        } else { // 'auto'
            // Auto-determine period based on current date if viewing current month/year
            $currentDate = now();
            $isCurrentMonthYear = ($month == $currentDate->month && $year == $currentDate->year);

            if ($isCurrentMonthYear) {
                $currentDay = $currentDate->day;
                $startDay = ($currentDay <= 15) ? 1 : 16;
                $endDay = ($currentDay <= 15) ? 15 : $daysInMonth;
            } else {
                // For past/future months, default to second half (16-end)
                $startDay = 16;
                $endDay = $daysInMonth;
            }
            $period = ($startDay === 1) ? '1-15' : '16-end';
        }

        // --- HOLIDAY LOGIC: Kukunin ang lahat ng holiday dates ---
        $holidays = Holiday::whereYear('date', $year)
                            ->whereMonth('date', $month)
                            ->pluck('date') // Kunin lang ang 'date' column
                            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d')) // I-format sa 'Y-m-d'
                            ->toArray();
        
        $days = [];
        $abbrMap = ['Mon'=>'M','Tue'=>'T','Wed'=>'W','Thu'=>'TH','Fri'=>'F','Sat'=>'S','Sun'=>'S'];

        for ($d = $startDay; $d <= $endDay; $d++) {
            if ($d <= $daysInMonth) {
                $dayDate = $baseDate->copy()->day($d);
                $days[] = [
                    'number' => $d,
                    'abbr' => $abbrMap[$dayDate->format('D')] ?? '',
                    'date' => $dayDate->format('Y-m-d')
                ];
            }
        }

        // ✅ Ipinasa ang 'holidays' array sa view
        return view('fulltime.index', compact('timesheets', 'days', 'month', 'year', 'startDay', 'period', 'holidays'));
    }

    public function create()
    {
        $departments = Department::active()->get();

        $periodOptions = [
            '1-15' => '1-15 (Days 1 to 15)',
            '16-30' => '16-30 (Days 16 to end of month)',
        ];

        return view('fulltime.create', compact('departments', 'periodOptions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_name' => 'required',
            'email' => 'nullable|email',
            'designation' => 'required|in:instructor,utility,staff',
            'prev_abs' => 'nullable|numeric',
            'department' => 'nullable|string',
            'date' => 'nullable|date',
            'period' => ['nullable', Rule::in(['1-15', '16-end'])],
            'days' => 'nullable|array',
            'details' => 'nullable|string',
            'total_hour' => 'nullable|numeric',
            'rate_per_hour' => 'nullable|numeric',
            'deduction' => 'nullable|numeric',
        ]);

        if (!isset($data['date']) || empty($data['date'])) {
            $data['date'] = now()->toDateString();
        }

        // Map create form days[] (1=Mon ... 6=Sat) into weekday columns
        $daysInput = $request->input('days', []);
        $weekdayMap = [1 => 'mon_hours', 2 => 'tue_hours', 3 => 'wed_hours', 4 => 'thu_hours', 5 => 'fri_hours', 6 => 'sat_hours'];

        foreach ($weekdayMap as $idx => $col) {
            $value = isset($daysInput[$idx]) && $daysInput[$idx] !== '' ? floatval($daysInput[$idx]) : 0;
            $data[$col] = $value;
        }

        // Sunday is not present in create form; ensure default
        $data['sun_hours'] = 0;

        // working_days array (list of weekdays that have >0 hours)
        $workingDays = [];
        foreach ($weekdayMap as $idx => $col) {
            if ($data[$col] > 0) {
                $workingDays[] = substr($col, 0, 3); // mon, tue, ...
            }
        }
        $data['working_days'] = json_encode($workingDays);
        $data['number_of_days'] = count($workingDays);

        // --- START: Holiday/Period-Aware Recalculation for total_hour and total_honorarium ---
        
        // 1. Determine Period Context
        $timesheetDate = Carbon::parse($data['date'] ?? now()); 
        $month = (int) $timesheetDate->month;
        $year = (int) $timesheetDate->year;
        $periodSetting = ($timesheetDate->day <= 15) ? '1-15' : '16-end'; 

        $daysInMonth = $timesheetDate->daysInMonth;
        if ($periodSetting === '1-15') {
            $startDay = 1;
            $endDay = 15;
        } else { // '16-end'
            $startDay = 16;
            $endDay = $daysInMonth;
        }

        // 2. Fetch Holidays for the Month
        $holidays = Holiday::whereYear('date', $year)
                           ->whereMonth('date', $month)
                           ->pluck('date')
                           ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
                           ->toArray();

        // 3. Holiday-Aware Gross Hours Calculation (Hours actually worked within the period)
        $grossHours = 0;
        $baseDate = Carbon::create($year, $month, 1);
        $weekdayMapFull = ['Mon'=>'mon_hours','Tue'=>'tue_hours','Wed'=>'wed_hours','Thu'=>'thu_hours','Fri'=>'fri_hours','Sat'=>'sat_hours','Sun'=>'sun_hours'];

        for ($d = $startDay; $d <= $endDay; $d++) {
            if ($d <= $daysInMonth) {
                $dayDate = $baseDate->copy()->day($d);
                $currentDate = $dayDate->format('Y-m-d');
                $weekdayName = $dayDate->format('D'); 

                $columnField = $weekdayMapFull[$weekdayName] ?? null; 
                
                // Only count hours that are NOT Sunday and NOT holidays for the specified period
                if ($columnField && $weekdayName !== 'Sun' && !in_array($currentDate, $holidays)) {
                    // Use the hours set in the $data array (from form input)
                    $hours = floatval($data[$columnField] ?? 0);
                    $grossHours += $hours;
                } 
            }
        }

        // 4. Final Recalculation (Total Hour and Honorarium)
        $data['prev_abs'] = floatval($data['prev_abs'] ?? 0);
        $data['rate_per_hour'] = floatval($data['rate_per_hour'] ?? 0);
        $data['deduction'] = floatval($data['deduction'] ?? 0); 

        // Calculate Payable Hours (Gross Hours for the period - Previous Absences)
        $data['total_hour'] = max(0, $grossHours - $data['prev_abs']); 

        // Calculate Total Honorarium
        $grossHonorarium = $data['total_hour'] * $data['rate_per_hour'];
        $data['total_honorarium'] = max(0, $grossHonorarium - $data['deduction']);
        // --- END: Holiday/Period-Aware Recalculation ---

        // Add month, year, and period to the data being saved
        $data['month'] = $month;
        $data['year'] = $year;
        $data['period'] = $periodSetting;

        FulltimeTimesheet::create($data);
        return redirect()->route('fulltime.index')->with('success', 'Timesheet added!');
    }

    public function edit($id)
    {
        $timesheet = FulltimeTimesheet::findOrFail($id);
        $departments = Department::active()->get();

        $departmentOptions = [
            'BSIT' => 'BSIT',
            'BSBA' => 'BSBA',
            'BSHM' => 'BSHM',
            'BSED' => 'BSED',
            'BEED' => 'BEED',
        ];

        $periodOptions = [
            '1-15' => '1-15 (Days 1 to 15)',
            '16-30' => '16-30 (Days 16 to end of month)',
        ];

        return view('fulltime.edit', compact('timesheet', 'departments', 'departmentOptions', 'periodOptions'));
    }

    public function update(Request $request, $id)
    {
        $timesheet = FulltimeTimesheet::findOrFail($id);
        $data = $request->validate([
            'employee_name' => 'required',
            'email' => 'nullable|email',
            'designation' => 'required|in:instructor,utility,staff',
            'prev_abs' => 'nullable',
            'department' => 'nullable|string',
            'date' => 'nullable|date',
            'period' => ['nullable', Rule::in(['1-15', '16-30'])],
            'days' => 'nullable|array',
            'details' => 'nullable',
            'total_hour' => 'nullable|numeric',
            'rate_per_hour' => 'nullable|numeric',
            'deduction' => 'nullable|numeric',
        ]);

        if (!isset($data['date']) || empty($data['date'])) {
            $data['date'] = now()->toDateString();
        }

        // Map incoming 'days' to weekday columns (if provided)
        $daysInput = $request->input('days', []);
        $weekdayMap = [1 => 'mon_hours', 2 => 'tue_hours', 3 => 'wed_hours', 4 => 'thu_hours', 5 => 'fri_hours', 6 => 'sat_hours'];

        foreach ($weekdayMap as $idx => $col) {
            if (isset($daysInput[$idx]) && $daysInput[$idx] !== '') {
                $data[$col] = floatval($daysInput[$idx]);
            }
        }

        // Ensure all weekly columns have a value (using existing value if not in request)
        $fullWeeklyColumns = ['mon_hours','tue_hours','wed_hours','thu_hours','fri_hours','sat_hours','sun_hours'];
        
        // Recalculate working_days list and number_of_days
        $workingDays = [];
        foreach ($fullWeeklyColumns as $col) {
            // Get the most current value (from $data if updated, otherwise from $timesheet)
            $val = $data[$col] ?? $timesheet->$col ?? 0;
            
            if ($val > 0) {
                $workingDays[] = substr($col, 0, 3);
            }
            // Ensure $data contains the latest hours for recalculation below
            $data[$col] = $val;
        }
        $data['working_days'] = json_encode($workingDays);
        $data['number_of_days'] = count($workingDays);

        // --- START: Holiday/Period-Aware Recalculation for total_hour and total_honorarium ---
        
        // 1. Determine Period Context (Use $data if present, otherwise $timesheet)
        $timesheetDate = Carbon::parse($data['date'] ?? $timesheet->date ?? now()); 
        $month = $timesheetDate->month;
        $year = $timesheetDate->year;
        $periodSetting = $data['period'] ?? $timesheet->period ?? '16-30'; 

        $daysInMonth = $timesheetDate->daysInMonth;
        if ($periodSetting === '1-15') {
            $startDay = 1;
            $endDay = 15;
        } else { // '16-30'
            $startDay = 16;
            $endDay = $daysInMonth;
        }

        // 2. Fetch Holidays for the Month
        $holidays = Holiday::whereYear('date', $year)
                           ->whereMonth('date', $month)
                           ->pluck('date')
                           ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
                           ->toArray();

        // 3. Holiday-Aware Gross Hours Calculation (Hours actually worked within the period)
        $grossHours = 0;
        $baseDate = Carbon::create($year, $month, 1);
        $weekdayMapFull = ['Mon'=>'mon_hours','Tue'=>'tue_hours','Wed'=>'wed_hours','Thu'=>'thu_hours','Fri'=>'fri_hours','Sat'=>'sat_hours','Sun'=>'sun_hours'];

        for ($d = $startDay; $d <= $endDay; $d++) {
            if ($d <= $daysInMonth) {
                $dayDate = $baseDate->copy()->day($d);
                $currentDate = $dayDate->format('Y-m-d');
                $weekdayName = $dayDate->format('D'); 

                $columnField = $weekdayMapFull[$weekdayName] ?? null; 
                
                // Only count hours that are NOT Sunday and NOT holidays for the specified period
                if ($columnField && $weekdayName !== 'Sun' && !in_array($currentDate, $holidays)) {
                    // Use the hours set in the $data array (which now contains all latest hours)
                    $hours = floatval($data[$columnField] ?? 0);
                    $grossHours += $hours;
                } 
            }
        }

        // 4. Final Recalculation (Total Hour and Honorarium)

        // Use $data if provided in request, otherwise use $timesheet's current value
        $prevAbsHours = floatval($data['prev_abs'] ?? $timesheet->prev_abs ?? 0);
        $ratePerHour = floatval($data['rate_per_hour'] ?? $timesheet->rate_per_hour ?? 0);
        $deduction = floatval($data['deduction'] ?? $timesheet->deduction ?? 0); 

        // Calculate Payable Hours
        $data['total_hour'] = max(0, $grossHours - $prevAbsHours); 

        // Calculate Total Honorarium
        $grossHonorarium = $data['total_hour'] * $ratePerHour;
        $data['total_honorarium'] = max(0, $grossHonorarium - $deduction);
        // --- END: Holiday/Period-Aware Recalculation ---
        
        $timesheet->update($data);
        return redirect()->route('fulltime.index')->with('success', 'Timesheet updated!');
    }

    public function destroy($id)
    {
        $timesheet = FulltimeTimesheet::findOrFail($id);
        $timesheet->delete();
        return redirect()->route('fulltime.index')->with('success', 'Timesheet deleted!');
    }

    // days functionality removed
    public function updateDay(Request $request, $id)
    {
        return response()->json([
            'success' => false,
            'message' => 'Days functionality has been removed.'
        ], 410);
    }

    public function updateField(Request $request, $id)
    {
        $timesheet = FulltimeTimesheet::findOrFail($id);
        $field = $request->input('field');
        $value = $request->input('value');
        
        // Define fields for recalculation check
        $recalculationFields = ['prev_abs', 'rate_per_hour', 'deduction'];
        $weeklyFields = ['mon_hours', 'tue_hours', 'wed_hours', 'thu_hours', 'fri_hours', 'sat_hours', 'sun_hours'];
        
        // Allowed fields for update
        $allowedFields = array_merge($recalculationFields, $weeklyFields, [
            'employee_name', 'designation', 'prev_abs', 'department', 'period', 
            'number_of_days', 'details', 'rate_per_hour', 'deduction', 
            'total_hour', 'total_honorarium'
        ]);
        
        if (!in_array($field, $allowedFields)) {
            return response()->json(['success' => false, 'message' => 'Invalid field'], 400);
        }
        
        // Update the field with the new value
        // Note: For numerical fields, ensure value is cast to float
        if (in_array($field, array_merge($recalculationFields, $weeklyFields))) {
            $timesheet->$field = floatval($value);
        } else {
             // For text fields like employee_name, department, details, total_hour (if manually set)
             $timesheet->$field = $value;
        }

        // --- START OF HOLIDAY-AWARE RECALCULATION LOGIC ---
        // Recalculate if any dependent field is updated (hours, rate, deduction, prov_abr)
        if (in_array($field, $recalculationFields) || in_array($field, $weeklyFields)) {

            // 1. Determine Period Context
            $timesheetDate = Carbon::parse($timesheet->date ?? now()); 
            $month = $timesheetDate->month;
            $year = $timesheetDate->year;
            $periodSetting = $timesheet->period ?? '16-30'; 

            $daysInMonth = $timesheetDate->daysInMonth;
            if ($periodSetting === '1-15') {
                $startDay = 1;
                $endDay = 15;
            } else { // '16-30'
                $startDay = 16;
                $endDay = $daysInMonth;
            }

            // 2. Fetch Holidays for the Month
            $holidays = Holiday::whereYear('date', $year)
                                 ->whereMonth('date', $month)
                                 ->pluck('date') 
                                 ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
                                 ->toArray();
            
            // 3. Holiday-Aware Gross Hours Calculation
            $grossHours = 0;
            $baseDate = Carbon::create($year, $month, 1);
            $weekdayMapFull = ['Mon'=>'mon_hours','Tue'=>'tue_hours','Wed'=>'wed_hours','Thu'=>'thu_hours','Fri'=>'fri_hours','Sat'=>'sat_hours','Sun'=>'sun_hours'];

            for ($d = $startDay; $d <= $endDay; $d++) {
                if ($d <= $daysInMonth) {
                    $dayDate = $baseDate->copy()->day($d);
                    $currentDate = $dayDate->format('Y-m-d');
                    $weekdayName = $dayDate->format('D'); 

                    $columnField = $weekdayMapFull[$weekdayName] ?? null; 
                    
                    // Only count hours that are NOT Sunday and NOT holidays for the specified period
                    if ($columnField && $weekdayName !== 'Sun' && !in_array($currentDate, $holidays)) {
                        
                        // Use the latest value that was just set on the $timesheet object
                        $hours = floatval($timesheet->$columnField ?? 0);
                        $grossHours += $hours;
                    } 
                }
            }
            
            // 4. Final Recalculation (Total Hour and Honorarium)
            
            // Ensure we use the latest values from the $timesheet object
            $prevAbsHours = floatval($timesheet->prev_abs ?? 0);
            $ratePerHour = floatval($timesheet->rate_per_hour ?? 0);
            $deduction = floatval($timesheet->deduction ?? 0); 
            
            // Calculate Payable Hours
            $timesheet->total_hour = max(0, $grossHours - $prevAbsHours); 

            // Calculate Total Honorarium
            $grossHonorarium = $timesheet->total_hour * $ratePerHour;
            $timesheet->total_honorarium = max(0, $grossHonorarium - $deduction);
            
            // 5. Update working_days and number_of_days based on weekly fields only
            $workingDays = [];
            foreach ($weeklyFields as $col) {
                // Use the updated value from $timesheet
                $val = floatval($timesheet->$col ?? 0);
                if ($val > 0) {
                    $workingDays[] = substr($col, 0, 3);
                }
            }
            $timesheet->working_days = json_encode($workingDays);
            $timesheet->number_of_days = count($workingDays); 
            // --- END OF HOLIDAY-AWARE RECALCULATION LOGIC ---

        }
        
        $timesheet->save();

        // Return the final calculated totals for the client-side to update the table
        return response()->json([
            'success' => true,
            'total_hour' => number_format($timesheet->total_hour, 2, '.', ''), 
            'total_honorarium' => number_format($timesheet->total_honorarium, 2, '.', '')
        ]);
    }
    
    /**
     * Handles the printing of the timesheet grid.
     * It uses the logic from index() to determine the current period.
     */
    public function printAll(Request $request)
    {
        // Kopyahin ang logic para sa pagkuha ng period, days, at holidays
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $period = $request->get('period', 'auto'); // 'auto', '1-15', '16-end'

        // 1. DETERMINE PERIOD
        $baseDate = \Carbon\Carbon::create($year, $month, 1);
        $daysInMonth = $baseDate->daysInMonth;

        if ($period === '1-15') {
            $startDay = 1;
            $endDay = 15;
            $periodLabel = '1 - 15';
        } elseif ($period === '16-end') {
            $startDay = 16;
            $endDay = $daysInMonth;
            $periodLabel = '16 - ' . $endDay;
        } else { // 'auto'
            $currentDate = now();
            $isCurrentMonthYear = ($month == $currentDate->month && $year == $currentDate->year);

            if ($isCurrentMonthYear) {
                $currentDay = $currentDate->day;
                $startDay = ($currentDay <= 15) ? 1 : 16;
                $endDay = ($currentDay <= 15) ? 15 : $daysInMonth;
            } else {
                $startDay = 16;
                $endDay = $daysInMonth;
            }
            $period = ($startDay === 1) ? '1-15' : '16-end';
            $periodLabel = ($startDay === 1) ? '1 - 15' : '16 - ' . $endDay;
        }

        $startDate = $baseDate->copy()->day($startDay);
        $endDate = $baseDate->copy()->day($endDay);
        
        // Final period display string for the header
        $periodDisplay = $startDate->format('F j') . ' - ' . $endDate->format('j, Y');

        // 2. FETCH DATA (TIMESHEETS & HOLIDAYS)
        $timesheets = FulltimeTimesheet::all(); 
        
        $holidays = Holiday::whereYear('date', $year)
                            ->whereMonth('date', $month)
                            ->pluck('date')
                            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
                            ->toArray();

        // 3. GENERATE DAYS ARRAY
        $days = [];
        $abbrMap = ['Mon'=>'M','Tue'=>'T','Wed'=>'W','Thu'=>'TH','Fri'=>'F','Sat'=>'S','Sun'=>'S'];

        for ($d = $startDay; $d <= $endDay; $d++) {
            if ($d <= $daysInMonth) {
                $dayDate = $baseDate->copy()->day($d);
                $days[] = [
                    'number' => $d,
                    'abbr' => $abbrMap[$dayDate->format('D')] ?? '',
                    'date' => $dayDate->format('Y-m-d')
                ];
            }
        }
        
        // 4. PASS TO VIEW
        // Ipapasa ang lahat ng kailangang variables para gumana ang print.blade.php
        return view('fulltime.print', [
            'timesheets' => $timesheets,
            'days'       => $days,
            'holidays'   => $holidays,
            'period'     => $periodDisplay, // Ang string para sa header
        ]);
    }
}