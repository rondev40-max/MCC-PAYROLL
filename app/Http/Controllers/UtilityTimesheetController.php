<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UtilityTimesheet;
use App\Models\Employee;
use Carbon\Carbon;
use App\Models\Holiday;
use Illuminate\Validation\Rule;

class UtilityTimesheetController extends Controller
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
                $period = ($startDay === 1) ? '1-15' : '16-end';
            } else {
                // For past/future months, don't force a single half.
                // Use the period selected by the user (keeps consistent filtering for existing rows).
                // If user didn't specify period, fall back to showing 1-15.
                $period = $request->get('period', '1-15');
                if (!in_array($period, ['1-15', '16-end'], true)) {
                    $period = '1-15';
                }
                $startDay = ($period === '1-15') ? 1 : 16;
                $endDay = ($period === '1-15') ? 15 : $daysInMonth;
            }
        }

        // Kukunin ang mga timesheets na tumutugma sa kasalukuyang napiling month, year, at period.
        $timesheets = UtilityTimesheet::where('year', $year)
                        ->where('month', $month)
                        ->where('period', $period)
                        ->get();

        // --- START: Auto-Create Missing Utility Employees for Selected Period ---
        // FIX: Use case-insensitive matching for position field
        // Try multiple common variations of utility position values
        
        $utilityEmployees = Employee::query()
            ->where(function ($query) {
                // Match any position that contains 'Utility' (case-insensitive)
                $query->whereRaw("LOWER(position) LIKE ?", ['%utility%'])
                      ->orWhereRaw("LOWER(position) LIKE ?", ['%staff%']);
            })
            ->get(['id', 'name as employee_name', 'email', 'position', 'hourly_salary']);

        if ($utilityEmployees->isNotEmpty()) {
            // Holidays needed for recalculation
            $holidaysForPeriod = Holiday::whereYear('date', $year)
                ->whereMonth('date', $month)
                ->pluck('date')
                ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
                ->toArray();

            $weekdayMapFull = [
                'Mon'=>'mon_hours','Tue'=>'tue_hours','Wed'=>'wed_hours',
                'Thu'=>'thu_hours','Fri'=>'fri_hours','Sat'=>'sat_hours','Sun'=>'sun_hours'
            ];

            foreach ($utilityEmployees as $emp) {
                // If the current period row is missing, create it by copying latest known week pattern
                $exists = UtilityTimesheet::where('year', $year)
                    ->where('month', $month)
                    ->where('period', $period)
                    ->where('employee_name', $emp->employee_name)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $latestRecordForEmployee = UtilityTimesheet::where('employee_name', $emp->employee_name)
                    ->orderBy('year', 'desc')
                    ->orderBy('month', 'desc')
                    ->orderByRaw("FIELD(period, '16-end', '1-15')")
                    ->first();

                // Build defaults when we have no previous timesheet yet
                $dataToCopy = $latestRecordForEmployee?->only([
                    'employee_name', 'designation', 'prov_abr',
                    'rate_per_day', 'deduction',
                    'mon_hours', 'tue_hours', 'wed_hours',
                    'thu_hours', 'fri_hours', 'sat_hours', 'sun_hours'
                ]) ?? [
                    'employee_name' => $emp->employee_name,
                    'designation' => 'utility',
                    'prov_abr' => 0,
                    'rate_per_day' => (float)($emp->hourly_salary ?? 0),
                    'deduction' => 0,
                    'mon_hours' => 0, 'tue_hours' => 0, 'wed_hours' => 0,
                    'thu_hours' => 0, 'fri_hours' => 0, 'sat_hours' => 0, 'sun_hours' => 0,
                ];

                // HOLIDAY-AWARE RECALCULATION FOR THE NEW PERIOD
                $totalDays = 0;
                for ($d = $startDay; $d <= $endDay; $d++) {
                    if ($d > $daysInMonth) {
                        continue;
                    }

                    $dayDate = $baseDate->copy()->day($d);
                    $currentDate = $dayDate->format('Y-m-d');
                    $weekdayName = $dayDate->format('D');
                    $columnField = $weekdayMapFull[$weekdayName] ?? null;

                    if ($columnField && $weekdayName !== 'Sun' && !in_array($currentDate, $holidaysForPeriod) && (($dataToCopy[$columnField] ?? 0) > 0)) {
                        $totalDays++;
                    }
                }

                $prevAbs = (float)($dataToCopy['prov_abr'] ?? 0);
                $finalDays = max(0, $totalDays - $prevAbs);
                $ratePerDay = (float)($dataToCopy['rate_per_day'] ?? 0);
                $deduction = (float)($dataToCopy['deduction'] ?? 0);
                $grossHonorarium = $finalDays * $ratePerDay;
                $totalHonorarium = max(0, $grossHonorarium - $deduction);

                $newRecordData = array_merge($dataToCopy, [
                    'email' => $emp->email,
                    'total_days' => $finalDays,
                    'total_honorarium' => $totalHonorarium,
                    'month' => $month,
                    'year' => $year,
                    'period' => $period,
                ]);

                UtilityTimesheet::updateOrCreate(
                    [
                        'employee_name' => $emp->employee_name,
                        'month' => $month,
                        'year' => $year,
                        'period' => $period,
                    ],
                    $newRecordData
                );
            }

            // Reload to include newly created rows
            $timesheets = UtilityTimesheet::where('year', $year)
                ->where('month', $month)
                ->where('period', $period)
                ->get();
        }
        // --- END: Auto-Create Missing Utility Employees ---


        // --- HOLIDAY LOGIC ---
        $holidays = Holiday::whereYear('date', $year)
                            ->whereMonth('date', $month)
                            ->pluck('date')
                            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
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

        return view('utility.index', compact('timesheets', 'days', 'month', 'year', 'startDay', 'endDay', 'period', 'holidays'));
    }

    public function create()
    {
        // FIX: Get utility employees to populate dropdown
        $utilityEmployees = Employee::query()
            ->where(function ($query) {
                $query->whereRaw("LOWER(position) LIKE ?", ['%utility%'])
                      ->orWhereRaw("LOWER(position) LIKE ?", ['%staff%']);
            })
            ->pluck('name', 'id')
            ->toArray();

        $month = now()->month;
        $year = now()->year;
        $period = (now()->day <= 15) ? '1-15' : '16-end';

        return view('utility.create', compact('month', 'year', 'period', 'utilityEmployees'));
    }

    public function store(Request $request)
    {
        // FIX: Get approved employee names for validation
        $approvedEmployees = Employee::query()
            ->where(function ($query) {
                $query->whereRaw("LOWER(position) LIKE ?", ['%utility%'])
                      ->orWhereRaw("LOWER(position) LIKE ?", ['%staff%']);
            })
            ->pluck('name')
            ->toArray();

        $validatedData = $request->validate([
            'employee_name' => [
                'required',
                'string',
                Rule::in($approvedEmployees),  // FIX: Must be from approved list
            ],
            'designation' => 'required|in:instructor,utility,staff',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
            'period' => ['required', Rule::in(['1-15', '16-end'])],
            'days' => 'nullable|array',
            'rate_per_day' => 'nullable|numeric|min:0',
            'deduction' => 'nullable|numeric|min:0',
            'prov_abr' => 'nullable|numeric|min:0',
        ]);

        // 1. Map 'days' array to columns
        $daysInput = $request->input('days', []);
        $weekdayMap = [1 => 'mon_hours', 2 => 'tue_hours', 3 => 'wed_hours', 4 => 'thu_hours', 5 => 'fri_hours', 6 => 'sat_hours'];

        foreach ($weekdayMap as $idx => $col) {
            $value = isset($daysInput[$idx]) && is_numeric($daysInput[$idx]) ? floatval($daysInput[$idx]) : 0;
            $validatedData[$col] = $value > 0 ? 1 : 0; // AYOS: Para sa utility, 1 (present) o 0 (absent) lang.
        }
        $validatedData['sun_hours'] = 0;

        // 2. Holiday-aware calculation
        $month = (int)$validatedData['month'];
        $year = (int)$validatedData['year'];
        $period = $validatedData['period'];
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
        $prevAbs = (float)($validatedData['prov_abr'] ?? 0);
        $finalDays = max(0, $totalDays - $prevAbs);
        $ratePerDay = (float)($validatedData['rate_per_day'] ?? 0);
        $deduction = (float)($validatedData['deduction'] ?? 0);
        $grossHonorarium = $finalDays * $ratePerDay;

        $validatedData['total_days'] = $finalDays;
        $validatedData['total_honorarium'] = max(0, $grossHonorarium - $deduction);

        UtilityTimesheet::create($validatedData);

        return redirect()->route('utility.index')->with('success', 'Utility timesheet added successfully!');
    }

    public function edit($id)
    {
        $timesheet = UtilityTimesheet::findOrFail($id);

        // FIX: Get utility employees to populate dropdown
        $utilityEmployees = Employee::query()
            ->where(function ($query) {
                $query->whereRaw("LOWER(position) LIKE ?", ['%utility%'])
                      ->orWhereRaw("LOWER(position) LIKE ?", ['%staff%']);
            })
            ->pluck('name', 'id')
            ->toArray();

        return view('utility.edit', compact('timesheet', 'utilityEmployees'));
    }

    public function update(Request $request, $id)
    {
        $timesheet = UtilityTimesheet::findOrFail($id);

        // FIX: Get approved employee names for validation
        $approvedEmployees = Employee::query()
            ->where(function ($query) {
                $query->whereRaw("LOWER(position) LIKE ?", ['%utility%'])
                      ->orWhereRaw("LOWER(position) LIKE ?", ['%staff%']);
            })
            ->pluck('name')
            ->toArray();

        $validatedData = $request->validate([
            'employee_name' => [
                'required',
                'string',
                Rule::in($approvedEmployees),  // FIX: Must be from approved list
            ],
            'designation' => 'required|in:instructor,utility,staff',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
            'period' => ['required', Rule::in(['1-15', '16-end'])],
            'days' => 'nullable|array',
            'rate_per_day' => 'nullable|numeric|min:0',
            'deduction' => 'nullable|numeric|min:0',
            'prov_abr' => 'nullable|numeric|min:0',
        ]);

        // 1. Map 'days' array to columns
        $daysInput = $request->input('days', []);
        $weekdayMap = [1 => 'mon_hours', 2 => 'tue_hours', 3 => 'wed_hours', 4 => 'thu_hours', 5 => 'fri_hours', 6 => 'sat_hours'];

        foreach ($weekdayMap as $idx => $col) {
            $value = isset($daysInput[$idx]) && is_numeric($daysInput[$idx]) ? floatval($daysInput[$idx]) : 0;
            $validatedData[$col] = $value > 0 ? 1 : 0;
        }
        $validatedData['sun_hours'] = 0;

        // 2. Holiday-aware calculation
        $month = (int)$validatedData['month'];
        $year = (int)$validatedData['year'];
        $period = $validatedData['period'];
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
        $prevAbs = (float)($validatedData['prov_abr'] ?? 0);
        $finalDays = max(0, $totalDays - $prevAbs);
        $ratePerDay = (float)($validatedData['rate_per_day'] ?? 0);
        $deduction = (float)($validatedData['deduction'] ?? 0);
        $grossHonorarium = $finalDays * $ratePerDay;

        $validatedData['total_days'] = $finalDays;
        $validatedData['total_honorarium'] = max(0, $grossHonorarium - $deduction);

        $timesheet->update($validatedData);
        return redirect()->route('utility.index')->with('success', 'Utility timesheet updated!');
    }

    public function destroy($id)
    {
        $timesheet = UtilityTimesheet::findOrFail($id);
        $timesheet->delete();
        return redirect()->route('utility.index')->with('success', 'Utility timesheet deleted!');
    }

    public function updateField(Request $request, $id)
    {
        $timesheet = UtilityTimesheet::findOrFail($id);
        $field = $request->input('field');
        $value = $request->input('value');

        // Validate allowed fields
        $recalculationFields = ['rate_per_day', 'deduction'];
        $weeklyFields = ['mon_hours', 'tue_hours', 'wed_hours', 'thu_hours', 'fri_hours', 'sat_hours', 'sun_hours', 'prov_abr'];
        $allowedFields = array_merge($recalculationFields, $weeklyFields, ['employee_name', 'designation']);

        if (!in_array($field, $allowedFields)) {
            return response()->json(['success' => false, 'message' => 'Invalid field'], 400);
        }

        // Update the field
        if (in_array($field, $weeklyFields)) {
            $timesheet->$field = floatval($value) > 0 ? 1 : 0;
        } else {
            $timesheet->$field = $value;
        }

        // --- START: Holiday-Aware Recalculation ---
        if (in_array($field, $recalculationFields) || in_array($field, $weeklyFields)) {
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

            // Isama ang Previous Absences sa kalkulasyon
            $prevAbs = (float)($timesheet->prov_abr ?? 0);
            $finalDays = max(0, $totalDays - $prevAbs);
            $timesheet->total_days = $finalDays;
            $grossHonorarium = ($timesheet->total_days * $timesheet->rate_per_day) - $timesheet->deduction;
            $timesheet->total_honorarium = max(0, $grossHonorarium);
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

        // --- START: Logic copied from Staff/Fulltime controllers ---
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

        $timesheets = UtilityTimesheet::where('year', $year)
                        ->where('month', $month)
                        ->where('period', $period)
                        ->get();

        $holidays = Holiday::whereYear('date', $year)->whereMonth('date', $month)->pluck('date')->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))->toArray();

        $days = [];
        $abbrMap = ['Mon'=>'M','Tue'=>'T','Wed'=>'W','Thu'=>'TH','Fri'=>'F','Sat'=>'S','Sun'=>'S'];
        for ($d = $startDay; $d <= $endDay; $d++) {
            $dayDate = $baseDate->copy()->day($d);
            $days[] = ['number' => $d, 'abbr' => $abbrMap[$dayDate->format('D')] ?? '', 'date' => $dayDate->format('Y-m-d')];
        }
        // --- END: Logic copied ---

        return view('utility.print', compact('timesheets', 'days', 'holidays', 'month', 'year', 'period'));
    }
}