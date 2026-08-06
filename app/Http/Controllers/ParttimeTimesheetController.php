<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ParttimeTimesheet;
use App\Models\Department;
use App\Models\Holiday;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ParttimeTimesheetController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $period = $request->get('period', 'auto'); // 'auto', '1-15', '16-end'

        // For part-time, we show all records regardless of their internal date for simplicity in the view.
        // Filtering can be added if needed.
        $timesheets = ParttimeTimesheet::all();

        // Generate days for the selected month
        $baseDate = Carbon::create($year, $month, 1);
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
        $abbrMap = ['Mon'=>'Mon','Tue'=>'Tue','Wed'=>'Wed','Thu'=>'Thu','Fri'=>'Fri','Sat'=>'Sat','Sun'=>'Sun'];

        for ($i = $startDay; $i <= $endDay; $i++) {
            if ($i <= $daysInMonth) {
                $dayDate = $baseDate->copy()->day($i);
                $dayAbbr = $dayDate->format('D');

                // For part-time, default hours are typically 0 unless a schedule is set.
                // We will handle the value logic inside the view based on what's saved.
                $days[] = [
                    'number' => $i,
                    'abbr' => $abbrMap[$dayAbbr] ?? '',
                    'date' => $dayDate->format('Y-m-d'),
                    'default_hours' => 0, // Part-time default is 0
                ];
            }
        }

        return view('parttime.index', compact('timesheets', 'days', 'month', 'year', 'period', 'startDay', 'holidays'));
    }

    // ---

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::active()->get();

        $departmentOptions = [
            'BSIT' => 'BSIT',
            'BSBA' => 'BSBA',
            'BSHM' => 'BSHM',
            'BSED' => 'BSED',
            'BEED' => 'BEED',
        ];

        return view('parttime.create', compact('departments', 'departmentOptions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validation
        $data = $request->validate([
            'employee_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'designation' => 'required|string|max:255',
            'prev_abs' => 'nullable|string|max:50',
            'department' => 'required|string|max:255',
            'date' => 'nullable|date',
            'details' => 'nullable|string',
            'total_hour' => 'nullable|numeric',
            'rate_per_hour' => 'nullable|numeric',
            'deduction' => 'nullable|numeric|min:0',
            'days' => 'nullable|array',
            'mon_hours' => 'nullable|numeric',
            'tue_hours' => 'nullable|numeric',
            'wed_hours' => 'nullable|numeric',
            'thu_hours' => 'nullable|numeric',
            'fri_hours' => 'nullable|numeric',
            'sat_hours' => 'nullable|numeric',
            'sun_hours' => 'nullable|numeric',
        ]);

        // 2. Prepare Data and Calculations
        if (!isset($data['date']) || empty($data['date'])) {
            $data['date'] = now()->toDateString();
        }
        
        // I-map ang 'days' array mula sa create form (1=Mon, 2=Tue, etc.) sa mga kaukulang columns
        $daysInput = $request->input('days', []);
        $weekdayMap = [1 => 'mon_hours', 2 => 'tue_hours', 3 => 'wed_hours', 4 => 'thu_hours', 5 => 'fri_hours', 6 => 'sat_hours'];

        foreach ($weekdayMap as $idx => $col) {
            $value = isset($daysInput[$idx]) && is_numeric($daysInput[$idx]) ? floatval($daysInput[$idx]) : 0;
            $data[$col] = $value;
        }
        // Siguraduhing 0 ang Sunday dahil wala ito sa create form
        $data['sun_hours'] = 0;

        // --- START: Holiday/Period-Aware Recalculation ---
        $timesheetDate = Carbon::parse($data['date']);
        $month = $timesheetDate->month;
        $year = $timesheetDate->year;
        
        $dayOfMonth = $timesheetDate->day;
        $periodSetting = ($dayOfMonth <= 15) ? '1-15' : '16-end';

        $daysInMonth = $timesheetDate->daysInMonth;
        $startDay = ($periodSetting === '1-15') ? 1 : 16;
        $endDay = ($periodSetting === '1-15') ? 15 : $daysInMonth;
        
        $holidays = Holiday::whereYear('date', $year)
                           ->whereMonth('date', $month)
                           ->pluck('date')
                           ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
                           ->toArray();
        
        $grossHours = 0;
        $baseDate = Carbon::create($year, $month, 1);
        $weekdayMapFull = ['Mon'=>'mon_hours','Tue'=>'tue_hours','Wed'=>'wed_hours','Thu'=>'thu_hours','Fri'=>'fri_hours','Sat'=>'sat_hours','Sun'=>'sun_hours'];

        for ($d = $startDay; $d <= $endDay; $d++) {
            if ($d <= $daysInMonth) {
                $dayDate = $baseDate->copy()->day($d);
                $currentDate = $dayDate->format('Y-m-d');
                $weekdayName = $dayDate->format('D');
                $columnField = $weekdayMapFull[$weekdayName] ?? null;

                if ($columnField && $weekdayName !== 'Sun' && !in_array($currentDate, $holidays)) {
                    $hours = floatval($data[$columnField] ?? 0);
                    $grossHours += $hours;
                }
            }
        }

        $data['rate_per_hour'] = floatval($data['rate_per_hour'] ?? 0);
        $data['deduction'] = floatval($data['deduction'] ?? 0);

        $data['total_hour'] = $grossHours;
        $grossHonorarium = $data['total_hour'] * $data['rate_per_hour'];
        $data['total_honorarium'] = max(0, $grossHonorarium - $data['deduction']);
        // --- END: Recalculation ---

        // Idagdag ang month at year sa data para mai-save sa database
        $data['month'] = $month;
        $data['year'] = $year;

        // Add the auto-determined period to the data to be saved
        $data['period'] = $periodSetting;

        // 3. Create the Record
        $timesheet = ParttimeTimesheet::create($data);

        // 4. Redirect with Success Message
        return redirect()->route('parttime.index')->with('success', 'Part-time Timesheet created successfully for ' . $timesheet->employee_name . '!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // ... (Logic for show)
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $timesheet = ParttimeTimesheet::findOrFail($id);
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
            '16-end' => '16-End (Days 16 to end of month)',
        ];

        return view('parttime.edit', compact('timesheet', 'departments', 'departmentOptions', 'periodOptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $timesheet = ParttimeTimesheet::findOrFail($id);
        
        // 1. Validation
        $data = $request->validate([
            'employee_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'designation' => 'required|string|max:255',
            'prev_abs' => 'nullable|string|max:50',
            'department' => 'required|string|max:255',
            'date' => 'nullable|date',
            'period' => ['nullable', Rule::in(['1-15', '16-end'])],
            'details' => 'nullable|string',
            'total_hour' => 'nullable|numeric',
            'rate_per_hour' => 'nullable|numeric',
            'deduction' => 'nullable|numeric|min:0',
            'days' => 'nullable|array',
            'mon_hours' => 'nullable|numeric',
            'tue_hours' => 'nullable|numeric',
            'wed_hours' => 'nullable|numeric',
            'thu_hours' => 'nullable|numeric',
            'fri_hours' => 'nullable|numeric',
            'sat_hours' => 'nullable|numeric',
            'sun_hours' => 'nullable|numeric',
        ]);

        // 2. Prepare Data and Calculations
        if (!isset($data['date']) || empty($data['date'])) {
            $data['date'] = $timesheet->date ?? now()->toDateString();
        }
        
        // I-map ang 'days' array mula sa edit form (kung meron) sa mga kaukulang columns
        $daysInput = $request->input('days', []);
        $weekdayMap = [1 => 'mon_hours', 2 => 'tue_hours', 3 => 'wed_hours', 4 => 'thu_hours', 5 => 'fri_hours', 6 => 'sat_hours'];

        foreach ($weekdayMap as $idx => $col) {
            if (isset($daysInput[$idx]) && is_numeric($daysInput[$idx])) {
                $data[$col] = floatval($daysInput[$idx]);
            }
        }

        // Force Sunday to be 0 on update as well
        $data['sun_hours'] = 0;

        // --- START: Holiday/Period-Aware Recalculation ---
        $timesheetDate = Carbon::parse($data['date'] ?? $timesheet->date);
        $month = $timesheetDate->month;
        $year = $timesheetDate->year;
        $periodSetting = $data['period'] ?? $timesheet->period ?? '16-end';
        
        $daysInMonth = $timesheetDate->daysInMonth;
        $startDay = ($periodSetting === '1-15') ? 1 : 16;
        $endDay = ($periodSetting === '1-15') ? 15 : $daysInMonth;
        
        $holidays = Holiday::whereYear('date', $year)
                           ->whereMonth('date', $month)
                           ->pluck('date')
                           ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
                           ->toArray();
        
        $grossHours = 0;
        $baseDate = Carbon::create($year, $month, 1);
        $weekdayMapFull = ['Mon'=>'mon_hours','Tue'=>'tue_hours','Wed'=>'wed_hours','Thu'=>'thu_hours','Fri'=>'fri_hours','Sat'=>'sat_hours','Sun'=>'sun_hours'];

        for ($d = $startDay; $d <= $endDay; $d++) {
            if ($d <= $daysInMonth) {
                $dayDate = $baseDate->copy()->day($d);
                $currentDate = $dayDate->format('Y-m-d');
                $weekdayName = $dayDate->format('D');
                $columnField = $weekdayMapFull[$weekdayName] ?? null;

                if ($columnField && $weekdayName !== 'Sun' && !in_array($currentDate, $holidays)) {
                    // Gamitin ang bagong value mula sa form, o ang luma kung walang bago
                    $hours = floatval($data[$columnField] ?? $timesheet->$columnField ?? 0);
                    $grossHours += $hours;
                }
            }
        }

        $ratePerHour = floatval($data['rate_per_hour'] ?? $timesheet->rate_per_hour ?? 0);
        $deduction = floatval($data['deduction'] ?? $timesheet->deduction ?? 0);

        // Siguraduhing kasama ang rate at deduction sa i-uupdate na data
        $data['rate_per_hour'] = $ratePerHour;
        $data['deduction'] = $deduction;

        $data['total_hour'] = $grossHours;
        $grossHonorarium = $data['total_hour'] * $ratePerHour;
        $data['total_honorarium'] = max(0, $grossHonorarium - $deduction);
        // --- END: Recalculation ---

        // 3. Update the Record
        $timesheet->update($data);

        // 4. Redirect with Success Message
        return redirect()->route('parttime.index')->with('success', 'Part-time Timesheet updated successfully for ' . $timesheet->employee_name . '!');
    }


    /**
     * Remove the specified resource from storage.
     * Ibinabalik ang nawawalang destroy method.
     */
    public function destroy(string $id)
    {
        $timesheet = ParttimeTimesheet::findOrFail($id);
        $timesheet->delete();

        return redirect()->route('parttime.index')->with('success', 'Part-time Timesheet deleted successfully!');
    }

    // --- New/Updated Methods for AJAX Updates ---

    /**
     * Update a single field (prov_abr, details, rate_per_hour, deduction).
     */
    public function updateField(Request $request, ParttimeTimesheet $timesheet)
    {
        $field = $request->input('field');
        $value = $request->input('value');

        $recalculationFields = ['rate_per_hour', 'deduction'];
        $weeklyFields = ['mon_hours', 'tue_hours', 'wed_hours', 'thu_hours', 'fri_hours', 'sat_hours'];
        $allowedFields = array_merge($recalculationFields, $weeklyFields, ['prev_abs', 'details', 'employee_name', 'designation', 'department', 'period', 'total_hour', 'total_honorarium']);
        
        if (!in_array($field, $allowedFields)) {
            return response()->json(['success' => false, 'message' => 'Invalid field'], 400);
        }

        // HARD RULE: Huwag payagan ang anumang update sa 'sun_hours'
        // This is a safeguard, although sun_hours is not in $weeklyFields
        if ($field === 'sun_hours') {
            $timesheet->sun_hours = 0;
            $timesheet->save();
            return response()->json(['success' => true, 'message' => 'Sunday hours are always zero.']);
        }
        $timesheet->$field = in_array($field, array_merge($recalculationFields, $weeklyFields)) ? floatval($value) : $value;
        
        // Double-check to ensure Sunday remains 0
        if ($timesheet->sun_hours != 0) {
            $timesheet->sun_hours = 0;
        }

        // --- HOLIDAY-AWARE RECALCULATION ---
        // Recalculate if a field affecting totals is changed
        $date = Carbon::parse($timesheet->date ?? now());
        $month = $date->month;
        $year = $date->year;
        $periodSetting = $timesheet->period ?? '16-end';

        $daysInMonth = $date->daysInMonth;
        $startDay = ($periodSetting === '1-15') ? 1 : 16;
        $endDay = ($periodSetting === '1-15') ? 15 : $daysInMonth;

        $holidays = Holiday::whereYear('date', $year)
                           ->whereMonth('date', $month)
                           ->pluck('date')
                           ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
                           ->toArray();

        $grossHours = 0;
        $baseDate = Carbon::create($year, $month, 1);
        $weekdayMapFull = ['Mon'=>'mon_hours','Tue'=>'tue_hours','Wed'=>'wed_hours','Thu'=>'thu_hours','Fri'=>'fri_hours','Sat'=>'sat_hours','Sun'=>'sun_hours'];

        for ($d = $startDay; $d <= $endDay; $d++) {
            if ($d <= $daysInMonth) {
                $dayDate = $baseDate->copy()->day($d);
                $currentDate = $dayDate->format('Y-m-d');
                $weekdayName = $dayDate->format('D');
                $columnField = $weekdayMapFull[$weekdayName] ?? null;

                if ($columnField && $weekdayName !== 'Sun' && !in_array($currentDate, $holidays)) {
                    $hours = floatval($timesheet->$columnField ?? 0);
                    $grossHours += $hours;
                }
            }
        }

        $timesheet->total_hour = $grossHours;
        $grossHonorarium = $timesheet->total_hour * $timesheet->rate_per_hour;
        $timesheet->total_honorarium = max(0, $grossHonorarium - $timesheet->deduction);

        $timesheet->save();

        return response()->json([
            'success' => true,
            'message' => "Field {$field} updated successfully.",
            'total_hour' => $timesheet->total_hour, // Ipadala bilang number
            'total_honorarium' => $timesheet->total_honorarium, // Ipadala bilang number
        ]);
    }

    public function updateDay(Request $request, ParttimeTimesheet $timesheet)
    {
        $day = $request->input('day');
        $hours = $request->input('hours');

        // NOTE: This method is now redundant because updateField can handle 'mon_hours', etc.
        // However, we can keep it as a fallback or for specific logic if needed in the future.
        // For now, let's ensure its recalculation logic is also correct.

        // --- RECALCULATION LOGIC (Same as updateField) ---
        $date = Carbon::parse($timesheet->date);
        $month = $date->month;
        $year = $date->year;
        $periodSetting = $timesheet->period ?? '16-end';

        $daysInMonth = $date->daysInMonth;
        $startDay = ($periodSetting === '1-15') ? 1 : 16;
        $endDay = ($periodSetting === '1-15') ? 15 : $daysInMonth;

        $holidays = Holiday::whereYear('date', $year)
                           ->whereMonth('date', $month)
                           ->pluck('date')
                           ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
                           ->toArray();

        // Alamin kung anong weekday column ang ia-update
        $dayDateToUpdate = Carbon::create($year, $month, $day);
        $weekdayToUpdate = strtolower($dayDateToUpdate->format('D')) . '_hours'; // e.g., 'mon_hours'
        
        // HARD RULE: If the day being updated is Sunday, force to 0 and do not proceed.
        if ($weekdayToUpdate === 'sun_hours') {
            $timesheet->sun_hours = 0;
            $timesheet->save();
            return response()->json(['success' => true, 'message' => 'Sunday hours are always zero.']);
        }

        // I-update ang specific na column
        if (in_array($weekdayToUpdate, ['mon_hours', 'tue_hours', 'wed_hours', 'thu_hours', 'fri_hours', 'sat_hours'])) {
            $timesheet->$weekdayToUpdate = floatval($hours);
        }

        $grossHours = 0;
        $baseDate = Carbon::create($year, $month, 1);
        $weekdayMapFull = ['Mon'=>'mon_hours','Tue'=>'tue_hours','Wed'=>'wed_hours','Thu'=>'thu_hours','Fri'=>'fri_hours','Sat'=>'sat_hours','Sun'=>'sun_hours'];

        for ($d = $startDay; $d <= $endDay; $d++) {
            if ($d <= $daysInMonth) {
                $dayDate = $baseDate->copy()->day($d);
                $currentDate = $dayDate->format('Y-m-d');
                $weekdayName = $dayDate->format('D');
                $columnField = $weekdayMapFull[$weekdayName] ?? null;

                if ($columnField && $weekdayName !== 'Sun' && !in_array($currentDate, $holidays)) {
                    $h = floatval($timesheet->$columnField ?? 0);
                    $grossHours += $h;
                }
            }
        }

        $timesheet->total_hour = $grossHours;
        $grossHonorarium = $timesheet->total_hour * $timesheet->rate_per_hour;
        $timesheet->total_honorarium = max(0, $grossHonorarium - $timesheet->deduction);

        $timesheet->save();

        return response()->json([
            'success' => true,
            'message' => "Day {$day} updated successfully.",
            'total_hour' => $timesheet->total_hour,
            'total_honorarium' => $timesheet->total_honorarium,
        ]);
    }

    public function printAll(Request $request)
    {
        // Replicate logic from FulltimeTimesheetController@printAll
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $period = $request->get('period', 'auto');
    
        $baseDate = Carbon::create($year, $month, 1);
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
                $startDay = ($currentDate->day <= 15) ? 1 : 16;
                $endDay = ($currentDate->day <= 15) ? 15 : $daysInMonth;
            } else {
                $startDay = 16; // Default to second half for non-current months
                $endDay = $daysInMonth;
            }
            $period = ($startDay === 1) ? '1-15' : '16-end';
            $periodLabel = ($startDay === 1) ? '1 - 15' : '16 - ' . $endDay;
        }
    
        $startDate = $baseDate->copy()->day($startDay);
        $endDate = $baseDate->copy()->day($endDay);
        $periodDisplay = $startDate->format('F j') . ' - ' . $endDate->format('j, Y');
    
        $timesheets = ParttimeTimesheet::all();
        $holidays = Holiday::whereYear('date', $year)
                            ->whereMonth('date', $month)
                            ->pluck('date')
                            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
                            ->toArray();
    
        $days = [];
        $abbrMap = ['Mon'=>'Mon','Tue'=>'Tue','Wed'=>'Wed','Thu'=>'Thu','Fri'=>'Fri','Sat'=>'Sat','Sun'=>'Sun'];
        for ($d = $startDay; $d <= $endDay; $d++) {
            if ($d <= $daysInMonth) {
                $dayDate = $baseDate->copy()->day($d);
                $days[] = ['number' => $d, 'abbr' => $abbrMap[$dayDate->format('D')] ?? '', 'date' => $dayDate->format('Y-m-d')];
            }
        }
    
        return view('parttime.print', [
            'timesheets' => $timesheets,
            'days'       => $days,
            'holidays'   => $holidays,
            'period'     => $periodDisplay,
        ]);
    }
}