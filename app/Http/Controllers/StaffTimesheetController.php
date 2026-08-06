<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StaffTimesheet;
use Carbon\Carbon;
use App\Models\Holiday;

class StaffTimesheetController extends Controller
{
    public function index(Request $request)
    {
        // Tiyakin na ang $month at $year ay palaging valid integers
        $month = (int)$request->get('month', now()->month);
        $year = (int)$request->get('year', now()->year);
        $period = $request->get('period', 'auto'); // 'auto', '1-15', '16-end'

        // Tiyakin na ang buwan ay nasa tamang range (1-12)
        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }

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
            $currentDate = now();
            $isCurrentMonthYear = ($month == $currentDate->month && $year == $currentDate->year);

            if ($isCurrentMonthYear) {
                $currentDay = $currentDate->day;
                $startDay = ($currentDay <= 15) ? 1 : 16;
                $endDay = ($currentDay <= 15) ? 15 : $daysInMonth;
            } else {
                // Default to 2nd half if viewing a past/future month
                $startDay = 16;
                $endDay = $daysInMonth;
            }
            // I-update ang period variable para sa view
            $period = ($startDay === 1) ? '1-15' : '16-end';
        }

        // Kukunin ang mga timesheets na tumutugma sa kasalukuyang napiling month, year, at period.
        $timesheets = StaffTimesheet::where('year', $year)
                        ->where('month', $month)
                        ->where('period', $period)
                        ->get();

        // --- START: Auto-Create Records from Previous Period ---
        // Auto-create only if there are truly no records for the selected period
        if ($timesheets->isEmpty()) {
            // --- PINAHUSAY NA LOGIC: Kopyahin ang pinakahuling record ng BAWAT empleyado ---

            // 1. Kunin ang lahat ng unique na pangalan ng empleyado sa buong table.
            $uniqueEmployeeNames = StaffTimesheet::whereNotNull('employee_name')
                                                 ->where('employee_name', '!=', '')
                                                 ->distinct()
                                                 ->pluck('employee_name');

            if ($uniqueEmployeeNames->isNotEmpty()) {
                foreach ($uniqueEmployeeNames as $name) {
                    // 2. Para sa bawat pangalan, hanapin ang kanilang pinakahuling record.
                    $latestRecordForEmployee = StaffTimesheet::where('employee_name', $name)
                        ->orderBy('year', 'desc')
                        ->orderBy('month', 'desc')
                        ->orderByRaw("FIELD(period, '16-end', '1-15')")
                        ->first();

                    if ($latestRecordForEmployee) {
                        // 3. Kopyahin ang data mula sa nahanap na record papunta sa kasalukuyang period.
                        $dataToCopy = $latestRecordForEmployee->only([
                            'employee_name', 'email', 'designation', 'prev_abs',
                            'rate_per_day', 'deduction', 'prev_abs',
                            'mon_hours', 'tue_hours', 'wed_hours',
                            'thu_hours', 'fri_hours', 'sat_hours', 'sun_hours'
                        ]);

                        // Gumawa ng bagong record para sa kasalukuyang month, year, at period.
                        // Ang `updateOrCreate` ay tinitiyak na hindi magkakaroon ng duplicate.
                        StaffTimesheet::updateOrCreate(
                            [
                                'employee_name' => $name,
                                'month' => $month,
                                'year' => $year,
                                'period' => $period,
                            ],
                            array_merge($dataToCopy, [
                                'date' => $baseDate->copy()->day($startDay)->format('Y-m-d'),
                                'details' => '', // I-reset ang details
                                'total_days' => 0, // I-reset ang calculated fields
                                'total_honorarium' => 0, // I-reset ang calculated fields
                            ])
                        );
                    }
                }
            }

            // 4. I-query ulit ang timesheets para makuha at maipakita ang mga bagong gawang records.
            $timesheets = StaffTimesheet::where('year', (string)$year) // Tiyaking string ang year para sa tamang paghahanap
                ->where('month', $month)
                ->where('period', $period)
                ->get();
        }
        // --- END: Auto-Create Records from Previous Period ---

        // --- HOLIDAY LOGIC: Kukunin ang lahat ng holiday dates (Gumagamit ng $year at $month) ---
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
                $days[] = [
                    'number' => $d,
                    'abbr' => $abbrMap[$dayDate->format('D')] ?? '',
                    'date' => $dayDate->format('Y-m-d')
                ];
            }
        }

        return view('staff.index', compact('timesheets', 'days', 'month', 'year', 'startDay', 'endDay', 'period', 'holidays'));
    }


    public function create()
    {
        return view('staff.create');
    }

    // Walang pagbabago sa store() - Dapat tama na ito sa pag-set ng month/year/period
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'employee_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'designation' => 'required|string',
            'prev_abs' => 'nullable|string',
            'prev_abs' => 'nullable|numeric|min:0',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer',
            'days' => 'nullable|array', // Ito na ngayon ay para sa weekly schedule (1=Mon, 2=Tue, etc.)
            'details' => 'nullable|string',
            'rate_per_day' => 'nullable|numeric',
            'deduction' => 'nullable|numeric',
            'date' => 'nullable|date', // Kailangan para malaman ang month/year
        ]);

        // --- START: Holiday-Aware Calculation Logic ---

        // 1. I-map ang 'days' array (weekly schedule) sa mga columns
        $daysInput = $request->input('days', []);
        $weekdayMap = [1 => 'mon_hours', 2 => 'tue_hours', 3 => 'wed_hours', 4 => 'thu_hours', 5 => 'fri_hours', 6 => 'sat_hours'];

        foreach ($weekdayMap as $idx => $col) {
            // Ang value ay ang oras na in-input, default sa 0 kung wala.
            $value = isset($daysInput[$idx]) && is_numeric($daysInput[$idx]) ? floatval($daysInput[$idx]) : 0;
            $validatedData[$col] = $value;
        }
        $validatedData['sun_hours'] = 0; // Laging 0 ang Sunday

        // 2. Holiday-aware calculation para sa total_days at total_honorarium
        // Gamitin ang month at year mula sa form.
        $month = (int)$validatedData['month'];
        $year = (int)$validatedData['year'];
        // Tiyakin na ang period ay laging naka-set.
        $period = $request->input('period', '1-15');
        // AYOS: Gumawa ng Carbon instance para makuha ang bilang ng araw sa buwan.
        $baseDate = Carbon::create($year, $month, 1);
        $daysInMonth = $baseDate->daysInMonth;
        $startDay = ($period === '1-15') ? 1 : 16;
        $endDay = ($period === '1-15') ? 15 : $daysInMonth;

        $holidays = Holiday::whereYear('date', $year)
                            ->whereMonth('date', $month)
                            ->pluck('date')
                            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
                            ->toArray();

        $totalDays = 0;
        $weekdayMapFull = ['Mon'=>'mon_hours','Tue'=>'tue_hours','Wed'=>'wed_hours','Thu'=>'thu_hours','Fri'=>'fri_hours','Sat'=>'sat_hours','Sun'=>'sun_hours'];

        for ($d = $startDay; $d <= $endDay; $d++) {
            if ($d <= $daysInMonth) {
                $dayDate = $baseDate->copy()->day($d);
                $currentDate = $dayDate->format('Y-m-d');
                $weekdayName = $dayDate->format('D');
                $columnField = $weekdayMapFull[$weekdayName] ?? null;

                // Bilangin lang ang araw kung:
                // 1. May katumbas na column (hindi invalid day)
                // 2. Hindi Linggo
                // 3. Hindi Holiday
                // 4. May naka-input na oras (> 0) para sa araw na iyon sa schedule
                if ($columnField && $weekdayName !== 'Sun' && !in_array($currentDate, $holidays) && ($validatedData[$columnField] ?? 0) > 0) {
                    $totalDays++;
                }
            }
        }

        // 3. Final Calculation
        $prevAbs = (float)($validatedData['prev_abs'] ?? 0);
        $finalDays = max(0, $totalDays - $prevAbs);
        $ratePerDay = (float)($validatedData['rate_per_day'] ?? 0);
        $deduction = (float)($validatedData['deduction'] ?? 0);
        $grossHonorarium = $finalDays * $ratePerDay;

        // 4. I-save ang lahat ng data
        $validatedData['total_days'] = $finalDays;
        $validatedData['total_honorarium'] = max(0, $grossHonorarium - $deduction);
        // --- END: Holiday-Aware Calculation Logic ---

        StaffTimesheet::create($validatedData);
        return redirect()->route('staff.index')->with('success', 'Staff timesheet added!');
    }

    // Walang pagbabago sa edit(), update(), at destroy()
    public function edit($id)
    {
        $timesheet = StaffTimesheet::findOrFail($id);
        return view('staff.edit', compact('timesheet'));
    }

    public function update(Request $request, $id)
    {
        $timesheet = StaffTimesheet::findOrFail($id);
        // Idinagdag ang month, year, at period sa validation
        $validatedData = $request->validate([
            'employee_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'designation' => 'required|string',
            'prev_abs' => 'nullable|string',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer',
            'prev_abs' => 'nullable|numeric|min:0',
            'days' => 'nullable|array',
            'details' => 'nullable|string',
            'rate_per_day' => 'nullable|numeric',
            'deduction' => 'nullable|numeric|min:0',
        ]);

        // --- START: Holiday-Aware Calculation Logic for UPDATE ---

        // 1. I-map ang 'days' array (weekly schedule) sa mga columns
        $daysInput = $request->input('days', []);
        $weekdayMap = [1 => 'mon_hours', 2 => 'tue_hours', 3 => 'wed_hours', 4 => 'thu_hours', 5 => 'fri_hours', 6 => 'sat_hours'];

        foreach ($weekdayMap as $idx => $col) {
            $value = isset($daysInput[$idx]) && is_numeric($daysInput[$idx]) ? floatval($daysInput[$idx]) : 0;
            $validatedData[$col] = $value;
        }
        $validatedData['sun_hours'] = 0; // Laging 0 ang Sunday

        // 2. Holiday-aware calculation para sa total_days at total_honorarium
        // Gamitin ang data mula sa existing record kung walang bago
        // Gamitin ang data mula sa form
        $month = (int)$validatedData['month'];
        $year = (int)$validatedData['year'];
        $period = $request->input('period', $timesheet->period);
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $startDay = ($period === '1-15') ? 1 : 16;
        $endDay = ($period === '1-15') ? 15 : $daysInMonth;

        $holidays = Holiday::whereYear('date', $year)
                            ->whereMonth('date', $month)
                            ->pluck('date')
                            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
                            ->toArray();

        $totalDays = 0;
        $baseDate = Carbon::create($year, $month, 1);
        $weekdayMapFull = ['Mon'=>'mon_hours','Tue'=>'tue_hours','Wed'=>'wed_hours','Thu'=>'thu_hours','Fri'=>'fri_hours','Sat'=>'sat_hours','Sun'=>'sun_hours'];

        for ($d = $startDay; $d <= $endDay; $d++) {
            if ($d <= $daysInMonth) {
                $dayDate = $baseDate->copy()->day($d);
                $currentDate = $dayDate->format('Y-m-d');
                $weekdayName = $dayDate->format('D');
                $columnField = $weekdayMapFull[$weekdayName] ?? null;

                if ($columnField && $weekdayName !== 'Sun' && !in_array($currentDate, $holidays) && ($validatedData[$columnField] ?? 0) > 0) {
                    $totalDays++;
                }
            }
        }

        // 3. Final Calculation
        $prevAbs = (float)($validatedData['prev_abs'] ?? $timesheet->prev_abs ?? 0);
        $finalDays = max(0, $totalDays - $prevAbs);
        $ratePerDay = (float)($validatedData['rate_per_day'] ?? $timesheet->rate_per_day ?? 0);
        $deduction = (float)($validatedData['deduction'] ?? $timesheet->deduction ?? 0);
        $grossHonorarium = $finalDays * $ratePerDay;

        $validatedData['total_days'] = $finalDays;
        $validatedData['total_honorarium'] = max(0, $grossHonorarium - $deduction);
        // --- END: Holiday-Aware Calculation Logic for UPDATE ---

        $timesheet->update($validatedData);
        return redirect()->route('staff.index')->with('success', 'Staff timesheet updated!');
    }

    public function destroy($id)
    {
        $timesheet = StaffTimesheet::findOrFail($id);
        $timesheet->delete();
        return redirect()->route('staff.index')->with('success', 'Staff timesheet deleted!');
    }

    // Walang pagbabago sa updateDay()
    public function updateDay(Request $request, $id)
    {
        $timesheet = StaffTimesheet::findOrFail($id);
        $day = $request->input('day'); // numeric day 16..30
        $hours = $request->input('hours', 0); // For staff, this is 0 or 1 (absent/present)

        // Get current days data
        $days = $timesheet->days;
        if (is_string($days)) {
            $decoded = json_decode($days, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $days = $decoded;
            } else {
                $days = [];
            }
        } elseif (!is_array($days)) {
            $days = [];
        }

        // Update the specific day with present/absent (0 or 1)
        $days[$day] = intval($hours);

        // Remove days with 0 (absent)
        if ($hours == 0) {
            unset($days[$day]);
        }

        // Update the timesheet
        $timesheet->days = json_encode($days);

        // Recalculate total days (count of present days)
        $totalDays = count($days);
        $timesheet->total_days = $totalDays;

        // Recalculate total honorarium
        $timesheet->total_honorarium = ($totalDays * ($timesheet->rate_per_day ?? 0)) - ($timesheet->deduction ?? 0);
        if ($timesheet->total_honorarium < 0) {
            $timesheet->total_honorarium = 0;
        }

        $timesheet->save();

        return response()->json([
            'success' => true,
            'total_days' => $totalDays,
            'total_honorarium' => number_format($timesheet->total_honorarium, 2)
        ]);
    }

    public function updateField(Request $request, $id)
    {
        $timesheet = StaffTimesheet::findOrFail($id);
        $fields = $request->input('fields', []);

        $recalculationFields = ['rate_per_day', 'deduction', 'prev_abs'];
        $weeklyFields = ['mon_hours', 'tue_hours', 'wed_hours', 'thu_hours', 'fri_hours', 'sat_hours', 'sun_hours'];
        $allowedFields = array_merge($recalculationFields, $weeklyFields, ['employee_name', 'designation', 'details']);

        $needsRecalculation = false;

        foreach ($fields as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $timesheet->$field = $value;
                if (in_array($field, $recalculationFields) || in_array($field, $weeklyFields)) {
                    $needsRecalculation = true;
                }
            }
        }

        if ($needsRecalculation) {
            $month = $timesheet->month;
            $year = $timesheet->year;
            $period = $timesheet->period;

            $baseDate = Carbon::create($year, $month, 1);
            $daysInMonth = $baseDate->daysInMonth;
            $startDay = ($period === '1-15') ? 1 : 16;
            $endDay = ($period === '1-15') ? 15 : $daysInMonth;

            $holidays = Holiday::whereYear('date', $year)
                                ->whereMonth('date', $month)
                                ->pluck('date')
                                ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
                                ->toArray();

            $totalDays = 0;
            $weekdayMapFull = ['Mon'=>'mon_hours','Tue'=>'tue_hours','Wed'=>'wed_hours','Thu'=>'thu_hours','Fri'=>'fri_hours','Sat'=>'sat_hours','Sun'=>'sun_hours'];

            for ($d = $startDay; $d <= $endDay; $d++) {
                if ($d <= $daysInMonth) {
                    $dayDate = $baseDate->copy()->day($d);
                    $currentDate = $dayDate->format('Y-m-d');
                    $weekdayName = $dayDate->format('D');
                    $columnField = $weekdayMapFull[$weekdayName] ?? null;

                    if ($columnField && $weekdayName !== 'Sun' && !in_array($currentDate, $holidays) && ($timesheet->$columnField ?? 0) > 0) {
                        $totalDays++;
                    }
                }
            }

            $prevAbs = (float)($timesheet->prev_abs ?? 0);
            $finalDays = max(0, $totalDays - $prevAbs);
            $timesheet->total_days = $finalDays;

            $ratePerDay = (float)($timesheet->rate_per_day ?? 0);
            $deduction = (float)($timesheet->deduction ?? 0);
            $grossHonorarium = $finalDays * $ratePerDay;
            $timesheet->total_honorarium = max(0, $grossHonorarium - $deduction);
        }

        $timesheet->save();

        return response()->json([
            'success' => true,
            'total_days' => $timesheet->total_days,
            'total_honorarium' => number_format($timesheet->total_honorarium, 2, '.', '')
        ]);
    }

    public function printAll(Request $request)
    {
        $month = (int)$request->get('month', now()->month);
        $year = (int)$request->get('year', now()->year);
        $period = $request->get('period', 'auto');

        $baseDate = Carbon::create($year, $month, 1);
        $daysInMonth = $baseDate->daysInMonth;

        if ($period === '1-15') {
            $startDay = 1;
            $endDay = 15;
        } elseif ($period === '16-end') {
            $startDay = 16;
            $endDay = $daysInMonth;
        } else {
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
        }

        $timesheets = StaffTimesheet::where('year', $year)
                        ->where('month', $month)
                        ->where('period', $period)
                        ->get();

        $holidays = Holiday::whereYear('date', $year)->whereMonth('date', $month)->pluck('date')->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))->toArray();

        // --- START: AYOS PARA SA PRINT PAGE ---
        // I-generate ang $days array, katulad ng sa index() method. Ito ang kulang.
        $days = [];
        $abbrMap = ['Mon'=>'Mon','Tue'=>'Tue','Wed'=>'Wed','Thu'=>'Thu','Fri'=>'Fri','Sat'=>'Sat','Sun'=>'Sun'];
        for ($d = $startDay; $d <= $endDay; $d++) {
            if ($d <= $daysInMonth) {
                $dayDate = $baseDate->copy()->day($d);
                $days[] = ['number' => $d, 'abbr' => $abbrMap[$dayDate->format('D')] ?? '', 'date' => $dayDate->format('Y-m-d')];
            }
        }
        // --- END: AYOS PARA SA PRINT PAGE ---

        return view('staff.print', compact('timesheets', 'days', 'holidays', 'month', 'year', 'period'));
    }
}