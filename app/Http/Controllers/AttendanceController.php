<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Support\PasswordHash;
use App\Support\Dtr;
use App\Mail\AttendanceOtpMail;

class AttendanceController extends Controller
{
    private const DEFAULT_TIME_IN  = '08:00:00';
    private const DEFAULT_TIME_OUT = '17:00:00';
    private const DEFAULT_AM_OUT   = '12:00:00';
    private const DEFAULT_PM_IN    = '13:00:00';
    private const DEFAULT_HOURS    = 8;
    private const ATTENDANCE_STATUSES = [
        'present',
        'absent',
        'late',
        'half_day',
        'leave',
        'holiday',
        'official_business',
    ];
    private const WEEK_DAYS        = [
        'monday'    => 0,
        'tuesday'   => 1,
        'wednesday' => 2,
        'thursday'  => 3,
        'friday'    => 4,
        'saturday'  => 5,
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // SESSION / AUTH HELPERS
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * The department this session is allowed to act on, normalised.
     *
     * Trimming matters: the value comes straight from users.course, and a
     * trailing space there made authorizeCourseAccess() fail its strict
     * comparison, which the browser only ever saw as "the register could not be
     * loaded". The DTR screens already normalised this way; the API did not.
     */
    private function getUserCourse(): ?string
    {
        $course = trim((string) session('user_course', ''));

        return $course !== '' ? strtoupper($course) : null;
    }

    private function getUserId(): ?int
    {
        return session('user_id');
    }

    private function isAuthenticated(): bool
    {
        return session()->has('is_attendance') && session('is_attendance') === true;
    }

    private function authorizeCourseAccess(string $requestedCourse): bool
    {
        $userCourse = $this->getUserCourse();

        if (!$userCourse) {
            return false;
        }

        // Normalise both sides. The requested value arrives from a URL segment
        // and the stored one from the database, so neither is guaranteed clean.
        return strtoupper(trim($requestedCourse)) === $userCourse;
    }

    private function unauthenticatedResponse(): JsonResponse
    {
        return response()->json(['error' => 'Unauthenticated.'], 401);
    }

    private function unauthorizedCourseResponse(): JsonResponse
    {
        return response()->json(['error' => 'Unauthorized access to this department.'], 403);
    }

    /**
     * Normalize any submitted date to its payroll half-month.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function cutoffRange(Carbon $date): array
    {
        if ($date->day <= 15) {
            $start = $date->copy()->startOfMonth();
            $end = $start->copy()->day(15)->endOfDay();
        } else {
            $start = $date->copy()->day(16)->startOfDay();
            $end = $date->copy()->endOfMonth()->endOfDay();
        }

        return [$start, $end];
    }

    private function dateWithinCutoff(string $raw, Carbon $start, Carbon $end): ?Carbon
    {
        try {
            $date = Carbon::createFromFormat('Y-m-d', $raw)->startOfDay();

            if ($date->format('Y-m-d') !== $raw || !$date->betweenIncluded($start, $end)) {
                return null;
            }

            return $date;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Resolve a client identity such as FT-12 without collapsing its type.
     *
     * @return array{id:int, type:string, code:string, typed_id:string}|null
     */
    private function employeeIdentity($rawId, ?string $rawType = null): ?array
    {
        $value = trim((string) $rawId);
        $code = null;
        $id = null;

        if (preg_match('/^(FT|PT|ST|UT)[-:]([1-9][0-9]*)$/i', $value, $matches)) {
            $code = strtoupper($matches[1]);
            $id = (int) $matches[2];
        } elseif (ctype_digit($value) && (int) $value > 0) {
            $code = Dtr::employeeTypeCode($rawType);
            $id = (int) $value;
        }

        if (!$code || !$id) {
            return null;
        }

        $payloadCode = Dtr::employeeTypeCode($rawType);
        if ($payloadCode && $payloadCode !== $code) {
            return null;
        }

        $type = Dtr::employeeType($code);

        return [
            'id'       => $id,
            'type'     => $type,
            'code'     => $code,
            'typed_id' => $code . '-' . $id,
        ];
    }

    private function employeeTypeForPrefix(string $prefix): string
    {
        return Dtr::employeeType($prefix) ?? 'Employee';
    }

    private function normalizedTypeSql(string $column): string
    {
        return 'UPPER(REPLACE(REPLACE(REPLACE(TRIM(COALESCE('
            . $column
            . ', \'\')), \'-\', \'\'), \' \', \'\'), \'_\', \'\')) = ?';
    }

    private function attendanceIdentityQuery(
        int $employeeId,
        string $course,
        string $employeeType
    ) {
        return DB::table('attendances')
            ->where('employee_id', $employeeId)
            ->whereRaw('UPPER(TRIM(course)) = ?', [$course])
            ->whereRaw($this->normalizedTypeSql('employee_type'), [
                Dtr::employeeTypeKey($employeeType),
            ]);
    }

    private function historyIdentityQuery(
        int $employeeId,
        string $course,
        string $employeeType
    ) {
        return DB::table('attendance_histories')
            ->where('employee_id', $employeeId)
            ->whereRaw('UPPER(TRIM(department)) = ?', [$course])
            ->whereRaw($this->normalizedTypeSql('employee_type'), [
                Dtr::employeeTypeKey($employeeType),
            ]);
    }

    private function deleteAttendanceDay(
        int $employeeId,
        string $course,
        string $employeeType,
        string $date
    ): int {
        return $this->attendanceIdentityQuery($employeeId, $course, $employeeType)
            ->whereDate('date', $date)
            ->delete();
    }

    private function upsertAttendanceDay(
        int $employeeId,
        string $course,
        string $employeeType,
        string $date,
        array $values
    ): void {
        $matches = $this->attendanceIdentityQuery($employeeId, $course, $employeeType)
            ->whereDate('date', $date)
            ->orderByDesc('id')
            ->pluck('id');

        if ($matches->isNotEmpty()) {
            DB::table('attendances')->where('id', $matches->first())->update($values);

            if ($matches->count() > 1) {
                DB::table('attendances')->whereIn('id', $matches->slice(1)->all())->delete();
            }

            return;
        }

        DB::table('attendances')->insert(array_merge([
            'employee_id'  => $employeeId,
            'course'       => $course,
            'employee_type' => $employeeType,
            'date'         => $date,
            'created_at'   => now(),
        ], $values));
    }

    private function deleteHistoryDay(
        int $employeeId,
        string $course,
        string $employeeType,
        string $date
    ): int {
        if (!Schema::hasTable('attendance_histories')) {
            return 0;
        }

        return $this->historyIdentityQuery($employeeId, $course, $employeeType)
            ->whereDate('attendance_date', $date)
            ->delete();
    }

    private function upsertHistoryDay(
        int $employeeId,
        string $course,
        string $employeeType,
        string $date,
        array $values
    ): void {
        $matches = $this->historyIdentityQuery($employeeId, $course, $employeeType)
            ->whereDate('attendance_date', $date)
            ->orderByDesc('id')
            ->pluck('id');

        if ($matches->isNotEmpty()) {
            DB::table('attendance_histories')->where('id', $matches->first())->update($values);

            if ($matches->count() > 1) {
                DB::table('attendance_histories')->whereIn('id', $matches->slice(1)->all())->delete();
            }

            return;
        }

        DB::table('attendance_histories')->insert(array_merge([
            'employee_id'     => $employeeId,
            'department'      => $course,
            'employee_type'   => $employeeType,
            'attendance_date' => $date,
            'created_at'      => now(),
        ], $values));
    }

    private function syncHistoryDay(
        int $employeeId,
        string $course,
        string $employeeType,
        string $date,
        ?int $userId,
        string $employeeName,
        ?string $email,
        ?string $designation,
        array $punches,
        array $metrics,
        string $status,
        ?string $remarks
    ): void {
        if (!Schema::hasTable('attendance_histories')) {
            return;
        }

        $existing = $this->historyIdentityQuery($employeeId, $course, $employeeType)
            ->whereDate('attendance_date', $date)
            ->orderByDesc('id')
            ->first();

        $values = [
            'employee_name' => $employeeName,
            'email'         => $email ?: $existing?->email,
            'employee_type' => $employeeType,
            'designation'   => $designation ?: $existing?->designation,
            'department'    => $course,
            'is_present'    => $metrics['present'],
            'hours_worked'  => $metrics['total_hours'],
            'time_in'       => $punches['am_in'],
            'time_out'      => $punches['pm_out'],
            'status'        => $status,
            'remarks'       => $remarks,
            'location'      => $existing?->location,
            'user_id'       => $userId,
            'updated_at'    => now(),
        ];

        if (Schema::hasColumn('attendance_histories', 'deleted_at')) {
            $values['deleted_at'] = null;
        }

        $this->upsertHistoryDay(
            $employeeId,
            $course,
            $employeeType,
            $date,
            $values
        );
    }

    /**
     * @return array{am_in:?string, am_out:?string, pm_in:?string, pm_out:?string}|null
     */
    private function dayPunches($dayData): ?array
    {
        if (!is_array($dayData)) {
            $present = (bool) $dayData;

            return [
                'am_in'  => $present ? substr(self::DEFAULT_TIME_IN, 0, 5) : null,
                'am_out' => $present ? substr(self::DEFAULT_AM_OUT, 0, 5) : null,
                'pm_in'  => $present ? substr(self::DEFAULT_PM_IN, 0, 5) : null,
                'pm_out' => $present ? substr(self::DEFAULT_TIME_OUT, 0, 5) : null,
            ];
        }

        $punches = [];
        foreach (['am_in', 'am_out', 'pm_in', 'pm_out'] as $key) {
            $value = trim((string) ($dayData[$key] ?? ''));
            if ($value === '') {
                $punches[$key] = null;
                continue;
            }

            if (!preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $value)) {
                return null;
            }

            $punches[$key] = $value;
        }

        return $punches;
    }

    private function markUserOnline(int $userId, Request $request): void
    {
        $ip     = $request->ip();
        $device = substr($request->header('User-Agent', 'unknown'), 0, 120);

        Cache::put("user-is-online-{$userId}", true, now()->addMinutes(10));
        Cache::put("user-online-info-{$userId}", [
            'ip'     => $ip,
            'device' => $device,
            'at'     => now()->toIso8601String(),
        ], now()->addMinutes(10));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // LOGIN
    // ──────────────────────────────────────────────────────────────────────────

    public function login(Request $request)
    {
        $now = Carbon::now();

        $lockoutUntil = $request->session()->get('attendance_lockout_until');
        if ($lockoutUntil && Carbon::parse($lockoutUntil)->gt($now)) {
            return back()->with('error', 'Too many attempts. Please wait.');
        }

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        $user = DB::table('users')
            ->where('email', $request->email)
            ->where('role', 'attendance_checker')
            ->first();

        if (!$user || !PasswordHash::checkAndUpgradeRow($request->password, $user)) {
            $attempts = (int) $request->session()->get('attendance_attempts', 0) + 1;
            $request->session()->put('attendance_attempts', $attempts);

            if ($attempts >= 3) {
                $request->session()->put(
                    'attendance_lockout_until',
                    $now->addSeconds(60)->toIso8601String()
                );
                return back()->with('error', 'Too many failed attempts. Locked for 60 seconds.');
            }

            return back()->with('error', 'Invalid credentials.');
        }

        $request->session()->forget(['attendance_attempts', 'attendance_lockout_until']);

        // Regenerate session ID on login to prevent session-fixation attacks.
        $request->session()->regenerate();

        $request->session()->put([
            'user_id'       => $user->id,
            'user_name'     => $user->name,
            'user_role'     => 'attendance_checker',
            'user_course'   => $user->course ?? null,
            'is_attendance' => true,
        ]);

        $this->markUserOnline($user->id, $request);

        return redirect('/attendance/dashboard')->with('success', 'Welcome, ' . $user->name);
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['user_id', 'user_name', 'user_role', 'user_course', 'is_attendance']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/attendance/attendlog')->with('success', 'You have been logged out successfully.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DASHBOARD
    // ──────────────────────────────────────────────────────────────────────────

    public function dashboard()
    {
        if (!$this->isAuthenticated()) {
            return redirect()->route('attendance.attendlog.form');
        }

        return view('attendance.dashboard');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DAILY TIME RECORD — CSC Form No. 48
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Roster for a month: everyone in the checker's course who has attendance
     * recorded, with a summary so a DTR that needs attention is visible before
     * you open it.
     */
    public function dtrIndex(Request $request)
    {
        if (!$this->isAuthenticated()) {
            return redirect()->route('attendance.attendlog.form');
        }

        $course = strtoupper(trim((string) ($request->query('course') ?: $this->getUserCourse())));
        $month  = $this->resolveMonth($request->query('month'));

        $employees = collect();

        if ($course && $this->authorizeCourseAccess($course)) {
            $employees = $this->rosterFor($course, $month);
        }

        return view('attendance.dtr-index', [
            'course'      => $course,
            'month'       => $month,
            'employees'   => $employees,
            'monthOptions' => $this->monthOptions(),
        ]);
    }

    /** The editable DTR for one employee and month. */
    public function dtrShow(Request $request, string $course, int $employeeId)
    {
        if (!$this->isAuthenticated()) {
            return redirect()->route('attendance.attendlog.form');
        }

        $course = strtoupper(trim($course));

        if (!$this->authorizeCourseAccess($course)) {
            abort(403, 'You do not have access to this department.');
        }

        $month = $this->resolveMonth($request->query('month'));
        $type  = Dtr::employeeType($request->query('type'));

        if (!Dtr::employeeTypeCode($type)) {
            abort(422, 'A valid employee type is required.');
        }

        return view('attendance.dtr', [
            'course'       => $course,
            'employeeId'   => $employeeId,
            'employeeType' => $type,
            'dtr'          => Dtr::build($employeeId, $course, $month, $type),
            'monthOptions' => $this->monthOptions(),
        ]);
    }

    /** Print view — CSC Form No. 48 as issued, nothing else on the page. */
    public function dtrPrint(Request $request, string $course, int $employeeId)
    {
        if (!$this->isAuthenticated()) {
            return redirect()->route('attendance.attendlog.form');
        }

        $course = strtoupper(trim($course));

        if (!$this->authorizeCourseAccess($course)) {
            abort(403, 'You do not have access to this department.');
        }

        $month = $this->resolveMonth($request->query('month'));
        $type = Dtr::employeeType($request->query('type'));

        if (!Dtr::employeeTypeCode($type)) {
            abort(422, 'A valid employee type is required.');
        }

        return view('attendance.dtr-print', [
            'course' => $course,
            'dtr'    => Dtr::build($employeeId, $course, $month, $type),
        ]);
    }

    /**
     * Persist edits made on the DTR screen.
     *
     * Writes the same columns the dashboard's bulk save uses, so both screens
     * remain interchangeable views of one table.
     */
    public function dtrSave(Request $request, string $course, int $employeeId)
    {
        if (!$this->isAuthenticated()) {
            return redirect()->route('attendance.attendlog.form');
        }

        $course = strtoupper(trim($course));

        if (!$this->authorizeCourseAccess($course)) {
            abort(403, 'You do not have access to this department.');
        }

        $validated = $request->validate([
            'month'          => ['required', 'date_format:Y-m'],
            'employee_name'  => ['nullable', 'string', 'max:255'],
            'employee_type'  => ['required', 'string', 'max:50'],
            'days'           => ['array'],
            'days.*'         => [function (string $attribute, $value, $fail) {
                if (!is_array($value)) {
                    return;
                }

                $hasPunches = collect(['am_in', 'am_out', 'pm_in', 'pm_out'])
                    ->contains(fn ($key) => trim((string) ($value[$key] ?? '')) !== '');
                $hasRemarks = trim((string) ($value['remarks'] ?? '')) !== '';
                $hasStatus = trim((string) ($value['status'] ?? '')) !== '';

                if ($hasRemarks && !$hasStatus && !$hasPunches) {
                    $fail('Choose a status when adding remarks without time entries.');
                }
            }],
            'days.*.am_in'   => ['nullable', 'date_format:H:i'],
            'days.*.am_out'  => ['nullable', 'date_format:H:i'],
            'days.*.pm_in'   => ['nullable', 'date_format:H:i'],
            'days.*.pm_out'  => ['nullable', 'date_format:H:i'],
            'days.*.status'  => [
                'nullable',
                'string',
                'in:' . implode(',', self::ATTENDANCE_STATUSES),
            ],
            'days.*.remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $month  = Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth();
        $userId = $this->getUserId();
        $saved  = 0;
        $identity = $this->employeeIdentity((string) $employeeId, $validated['employee_type']);

        if (!$identity) {
            abort(422, 'A valid employee type is required.');
        }

        $employeeType = $identity['type'];

        DB::beginTransaction();

        try {
            foreach ($validated['days'] ?? [] as $day => $times) {
            $day = (int) $day;

            // A day number outside the month is either a stale form or someone
            // poking at the payload; either way it must not create a row.
            if ($day < 1 || $day > $month->daysInMonth) {
                continue;
            }

            $date = $month->copy()->addDays($day - 1)->toDateString();
            $punches = $this->dayPunches($times);
            if ($punches === null) {
                continue;
            }

            $statusProvided = is_array($times) && array_key_exists('status', $times);
            $remarksProvided = is_array($times) && array_key_exists('remarks', $times);
            $statusValue = $statusProvided
                ? strtolower(trim((string) ($times['status'] ?? '')))
                : null;
            $remarksValue = $remarksProvided ? trim((string) ($times['remarks'] ?? '')) : null;
            $metrics = Dtr::metrics(
                $punches['am_in'],
                $punches['am_out'],
                $punches['pm_in'],
                $punches['pm_out'],
                $statusValue
            );
            $hasMetadata = $statusValue !== null && $statusValue !== ''
                || $remarksValue !== null && $remarksValue !== '';

            // Clearing every field on a day removes the record rather than
            // leaving an empty row that still counts as "present".
            if (!$metrics['has_entry'] && !$hasMetadata) {
                $this->deleteAttendanceDay($identity['id'], $course, $employeeType, $date);
                $this->deleteHistoryDay($identity['id'], $course, $employeeType, $date);
                continue;
            }

            $existing = $this->attendanceIdentityQuery($identity['id'], $course, $employeeType)
                ->whereDate('date', $date)
                ->orderByDesc('id')
                ->first();
            $status = $statusProvided
                ? ($statusValue ?: ($metrics['has_entry'] ? 'present' : 'absent'))
                : ($existing?->status ?? ($metrics['has_entry'] ? 'present' : 'absent'));
            $remarks = $remarksProvided
                ? ($remarksValue ?: null)
                : ($existing?->remarks ?? null);
            $employeeName = $validated['employee_name']
                ?? $existing?->employee_name
                ?? 'Employee #' . $identity['id'];

            $this->upsertAttendanceDay($identity['id'], $course, $employeeType, $date, [
                'user_id'            => $userId,
                'time_in'            => $punches['am_in'],
                'time_out'           => $punches['pm_out'],
                'am_in_time'         => $punches['am_in'],
                'am_out_time'        => $punches['am_out'],
                'pm_in_time'         => $punches['pm_in'],
                'pm_out_time'        => $punches['pm_out'],
                'hours_rendered'     => $metrics['total_hours'],
                'lateness_minutes'   => $metrics['lateness'],
                'undertime_minutes'  => $metrics['undertime'],
                'overtime_minutes'   => $metrics['overtime'],
                'total_hours'        => $metrics['total_hours'],
                'status'             => $status,
                'remarks'            => $remarks,
                'employee_name'      => $employeeName,
                'employee_type'      => $employeeType,
                'updated_at'         => now(),
            ]);
            $this->syncHistoryDay(
                $identity['id'],
                $course,
                $employeeType,
                $date,
                $userId,
                $employeeName,
                null,
                null,
                $punches,
                $metrics,
                $status,
                $remarks
            );

                $saved++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect()
            ->route('attendance.dtr.show', [
                'course'     => $course,
                'employeeId' => $employeeId,
                'month'      => $month->format('Y-m'),
                'type'       => $employeeType,
            ])
            ->with('success', "Daily Time Record saved ({$saved} day" . ($saved === 1 ? '' : 's') . ').');
    }

    /** Employees with attendance in this course for the month, plus a summary. */
    private function rosterFor(string $course, Carbon $month)
    {
        try {
            $rows = Schema::hasTable('attendances')
                ? DB::table('attendances')
                    ->select([
                        'id',
                        'employee_id',
                        'employee_name',
                        'employee_type',
                        'date',
                        'total_hours',
                        'hours_rendered',
                    ])
                    ->whereRaw('UPPER(TRIM(course)) = ?', [$course])
                    ->whereBetween('date', [
                        $month->copy()->startOfMonth()->toDateString(),
                        $month->copy()->endOfMonth()->toDateString(),
                    ])
                    ->get()
                : collect();

            $summaries = $rows
                ->groupBy(fn ($row) => $row->employee_id . '|' . Dtr::employeeTypeKey($row->employee_type))
                ->map(function ($group) {
                    $latestByDate = $group->sortBy('id')->keyBy('date');
                    $identityRow = $group->sortByDesc('id')->first();

                    return (object) [
                        'employee_id'   => (int) $identityRow->employee_id,
                        'employee_name' => $group->pluck('employee_name')->filter()->last(),
                        'employee_type' => Dtr::employeeType($identityRow->employee_type),
                        'days_recorded' => $latestByDate->count(),
                        'hours'         => $latestByDate->sum(
                            fn ($row) => (float) $row->total_hours > 0
                                ? (float) $row->total_hours
                                : (float) ($row->hours_rendered ?? 0)
                        ),
                    ];
                });

            $roster = $this->attendanceRoster($course)
                ->mapWithKeys(function ($employee) use ($summaries) {
                    $id = (int) $employee['raw_id'];
                    $type = Dtr::employeeType($employee['employee_type']);
                    $key = $id . '|' . Dtr::employeeTypeKey($type);
                    $summary = $summaries->get($key);

                    return [$key => (object) [
                        'employee_id'   => $id,
                        'employee_name' => $summary?->employee_name ?: $employee['employee_name'],
                        'employee_type' => $type,
                        'days_recorded' => $summary?->days_recorded ?? 0,
                        'hours'         => $summary?->hours ?? 0,
                    ]];
                });

            $summaries->each(function ($summary, $key) use ($roster) {
                if (!Dtr::employeeTypeCode($summary->employee_type)) {
                    $matchingKeys = $roster
                        ->filter(fn ($employee) => $employee->employee_id === $summary->employee_id)
                        ->keys();

                    if ($matchingKeys->count() === 1) {
                        $employee = $roster->get($matchingKeys->first());
                        $employee->employee_name = $summary->employee_name ?: $employee->employee_name;
                        $employee->days_recorded = $summary->days_recorded;
                        $employee->hours = $summary->hours;
                    } else {
                        Log::warning('Skipped DTR roster row without a resolvable employee type.', [
                            'employee_id' => $summary->employee_id,
                        ]);
                    }

                    return;
                }

                if (!$roster->has($key)) {
                    $roster->put($key, $summary);
                }
            });

            return $roster
                ->sortBy('employee_name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values();
        } catch (\Exception $e) {
            Log::warning('DTR roster failed: ' . $e->getMessage());
            return collect();
        }
    }

    /** Parse ?month=YYYY-MM, falling back to the current month. */
    private function resolveMonth(?string $raw): Carbon
    {
        if ($raw) {
            try {
                return Carbon::createFromFormat('Y-m', $raw)->startOfMonth();
            } catch (\Exception $e) {
                // Fall through to today.
            }
        }

        return Carbon::now()->startOfMonth();
    }

    /** The last 12 months, for the period picker. */
    private function monthOptions(): array
    {
        $options = [];
        $cursor  = Carbon::now()->startOfMonth();

        for ($i = 0; $i < 12; $i++) {
            $options[$cursor->format('Y-m')] = $cursor->format('F Y');
            $cursor->subMonth();
        }

        return $options;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PASSWORD RESET / RECOVERY
    // ──────────────────────────────────────────────────────────────────────────

    public function showForgotForm()
    {
        return view('attendance.forgot_password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            $user = DB::table('users')
                ->where('email', $request->email)
                ->where('role', 'attendance_checker')
                ->first();

            // SECURITY: don't reveal whether an email is registered. Whether the
            // account exists or not, we respond identically (and only actually
            // send mail when it exists), so this endpoint can't be used to
            // enumerate valid attendance-checker accounts.
            if (!$user) {
                Log::warning('Password reset attempt for non-existent user', [
                    'email' => $request->email,
                    'ip' => $request->ip(),
                ]);

                $request->session()->put('reset_email_pending', $request->email);
                return redirect()->route('attendance.reset.form')
                    ->with('success', 'If that email is registered, a 6-digit code has been sent. It is valid for 10 minutes.');
            }

            $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Build the update payload. A brand-new code always starts with a
            // clean attempt/lock budget; the columns are written only when the
            // migration that adds them has run, so the flow still works if not.
            $payload = [
                'otp_hash'   => Hash::make($otp),
                'expires_at' => now()->addMinutes(10),
                'used_at'    => null,
                'updated_at' => now(),
            ];
            if (Schema::hasColumn('attendance_password_otps', 'attempts')) {
                $payload['attempts'] = 0;
            }
            if (Schema::hasColumn('attendance_password_otps', 'locked_until')) {
                $payload['locked_until'] = null;
            }

            // Only stamp created_at when inserting a fresh row so re-requests
            // don't keep rewriting the original creation time.
            $exists = DB::table('attendance_password_otps')->where('email', $request->email)->exists();
            if (!$exists) {
                $payload['created_at'] = now();
            }

            DB::table('attendance_password_otps')->updateOrInsert(
                ['email' => $request->email],
                $payload
            );

            $request->session()->put('reset_email_pending', $request->email);

            try {
                Mail::to($request->email)->send(new AttendanceOtpMail($otp, $user->name));
                Log::info('OTP sent for password reset', ['email' => $request->email]);
            } catch (\Exception $e) {
                Log::error('Failed to send OTP email: ' . $e->getMessage());
                return back()->with('error', 'Failed to send the verification code. Please try again.')->withInput();
            }

            // FIX: previously this returned back() to the forgot-password page,
            // leaving the user with no way to actually enter the code. Now we
            // advance them straight to the OTP-entry screen with the email
            // remembered in the session so the field is pre-filled.
            return redirect()->route('attendance.reset.form')
                ->with('success', 'A 6-digit code has been sent to your email. It is valid for 10 minutes.');

        } catch (\Exception $e) {
            Log::error('sendOtp failed: ' . $e->getMessage());
            return back()->with('error', 'An error occurred. Please try again.')->withInput();
        }
    }

    public function showResetForm(Request $request)
    {
        // FIX: reset_password.blade references $email. Resolve it from the
        // session set during sendOtp (falling back to old input on a failed
        // verify) and pass it explicitly so the view can no longer 500 on an
        // undefined variable. With no pending request, send the user back to
        // the start rather than showing an OTP box for an email we don't have.
        $email = $request->session()->get('reset_email_pending') ?? old('email');

        if (!$email) {
            return redirect()->route('attendance.forgot.form')
                ->with('error', 'Please request a verification code first.');
        }

        return view('attendance.reset_password', ['email' => $email]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        try {
            $email  = $request->email;
            $record = DB::table('attendance_password_otps')
                ->where('email', $email)
                ->first();

            if (!$record) {
                Log::warning('OTP verification failed - no record', ['email' => $email]);
                return back()->with('error', 'No verification request found for this email. Please request a new code.')->withInput();
            }

            $hasLockCols = Schema::hasColumn('attendance_password_otps', 'attempts')
                && Schema::hasColumn('attendance_password_otps', 'locked_until');

            // Hard stop once the attempt budget for this code has been spent,
            // regardless of what is entered, until the lock window elapses.
            if ($hasLockCols && !empty($record->locked_until) && Carbon::parse($record->locked_until)->isFuture()) {
                $minutesLeft = max(1, (int) ceil(Carbon::now()->diffInSeconds(Carbon::parse($record->locked_until), true) / 60));
                return back()->with('error', "Too many incorrect attempts. Please wait about {$minutesLeft} minute(s), or request a new code.")->withInput();
            }

            // A code may only be used once.
            if (!empty($record->used_at)) {
                return back()->with('error', 'This code has already been used. Please request a new one.')->withInput();
            }

            if (Carbon::parse($record->expires_at)->isPast()) {
                DB::table('attendance_password_otps')->where('email', $email)->delete();
                return back()->with('error', 'The code has expired. Please request a new one.')->withInput();
            }

            // Wrong code: count the attempt and lock the code after too many
            // consecutive misses. The underlying password_verify is already
            // constant-time, so this closes the brute-force gap the flow
            // previously had.
            if (!PasswordHash::check($request->otp, $record->otp_hash)) {
                Log::warning('Invalid OTP attempt', ['email' => $email, 'ip' => $request->ip()]);

                if ($hasLockCols) {
                    $maxAttempts = 5;
                    $attempts    = (int) ($record->attempts ?? 0) + 1;

                    if ($attempts >= $maxAttempts) {
                        DB::table('attendance_password_otps')->where('email', $email)->update([
                            'attempts'     => 0,
                            'locked_until' => Carbon::now()->addMinutes(15),
                            'updated_at'   => now(),
                        ]);
                        return back()->with('error', 'Too many incorrect attempts. Please wait 15 minutes, or request a new code.')->withInput();
                    }

                    DB::table('attendance_password_otps')->where('email', $email)->update([
                        'attempts'   => $attempts,
                        'updated_at' => now(),
                    ]);

                    $remaining = $maxAttempts - $attempts;
                    return back()->with('error', "Invalid code. {$remaining} attempt(s) remaining before a temporary lock.")->withInput();
                }

                return back()->with('error', 'Invalid code. Please try again.')->withInput();
            }

            // SUCCESS: consume the code (single-use) and clear the lock budget.
            $update = ['used_at' => now(), 'updated_at' => now()];
            if ($hasLockCols) {
                $update['attempts']     = 0;
                $update['locked_until'] = null;
            }
            DB::table('attendance_password_otps')->where('email', $email)->update($update);

            // Regenerate the session id on this privilege transition to prevent
            // session-fixation, then open a time-boxed window to set a password.
            $request->session()->regenerate();
            $request->session()->forget('reset_email_pending');
            $request->session()->put([
                'otp_verified'    => true,
                'reset_email'     => $email,
                'otp_verified_at' => now()->timestamp,
            ]);

            return redirect()->route('attendance.change.form')->with('success', 'Code verified. You can now set a new password.');

        } catch (\Exception $e) {
            Log::error('verifyOtp failed: ' . $e->getMessage());
            return back()->with('error', 'An error occurred. Please try again.')->withInput();
        }
    }

    /**
     * The password-reset window is valid only for a short time after the OTP
     * was verified, so a stale "verified" session can't be reused much later.
     */
    private function hasValidResetSession(): bool
    {
        if (!session('otp_verified') || !session('reset_email')) {
            return false;
        }

        $verifiedAt = session('otp_verified_at');
        if ($verifiedAt && (now()->timestamp - (int) $verifiedAt) > 900) { // 15 minutes
            session()->forget(['otp_verified', 'reset_email', 'otp_verified_at']);
            return false;
        }

        return true;
    }

    public function showChangePasswordForm()
    {
        if (!$this->hasValidResetSession()) {
            return redirect()->route('attendance.forgot.form')->with('error', 'Please verify your email first.');
        }

        return view('attendance.change_password', ['email' => session('reset_email')]);
    }

    public function resetPassword(Request $request)
    {
        if (!$this->hasValidResetSession()) {
            return redirect()->route('attendance.forgot.form')->with('error', 'Session expired. Please start over.');
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $email = session('reset_email');

            $updated = DB::table('users')
                ->where('email', $email)
                ->where('role', 'attendance_checker')
                ->update(['password' => Hash::make($request->password)]);

            if ($updated) {
                DB::table('attendance_password_otps')
                    ->where('email', $email)
                    ->update(['used_at' => now()]);

                $request->session()->forget(['otp_verified', 'reset_email', 'otp_verified_at', 'reset_email_pending']);

                Log::info('Attendance user password reset successfully', ['email' => $email]);
                return redirect()->route('attendance.attendlog.form')
                    ->with('success', 'Password reset successfully. Please log in with your new password.');
            } else {
                return back()->with('error', 'Failed to reset password. Please try again.');
            }

        } catch (\Exception $e) {
            Log::error('resetPassword failed: ' . $e->getMessage());
            return back()->with('error', 'An error occurred. Please try again.');
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // API: COURSE COUNTS
    // ──────────────────────────────────────────────────────────────────────────

    public function getCourseCounts(Request $request): JsonResponse
    {
        if (!$this->isAuthenticated()) {
            return $this->unauthenticatedResponse();
        }

        try {
            $course = strtoupper(trim((string) $request->query(
                'course',
                $this->getUserCourse() ?? ''
            )));

            if (!$course) {
                return response()->json(['count' => 0]);
            }

            if (!$this->authorizeCourseAccess($course)) {
                return $this->unauthorizedCourseResponse();
            }

            return response()->json(['count' => $this->attendanceRoster($course)->count()]);

        } catch (\Exception $e) {
            Log::error('getCourseCounts: ' . $e->getMessage());
            return response()->json(['count' => 0]);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // API: ATTENDANCE DATA - FIXED VERSION
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * FIX #1: Safe column selection with fallback values
     * - Don't assume employee_type exists in all tables
     * - Use CASE statement to infer type from table
     * - Handle JSON decode errors gracefully
     * - Add null checks for all fields
     */
    public function getAttendanceData(string $course, Request $request): JsonResponse
    {
        $userId = $this->getUserId();
        $userCourse = $this->getUserCourse();
        $isAuthenticated = $this->isAuthenticated();

        // Debug logging
        Log::info('getAttendanceData called', [
            'requested_course' => $course,
            'user_id' => $userId,
            'user_course' => $userCourse,
            'is_authenticated' => $isAuthenticated,
        ]);

        if (!$this->isAuthenticated()) {
            Log::warning('getAttendanceData: Not authenticated');
            return $this->unauthenticatedResponse();
        }

        try {
            $course = strtoupper(trim($course));

            if (!$this->authorizeCourseAccess($course)) {
                Log::warning('Unauthorized course access attempt', [
                    'user_id'   => $this->getUserId(),
                    'requested' => $course,
                    'user'      => $this->getUserCourse(),
                    'ip'        => $request->ip(),
                ]);
                return response()->json(['error' => 'Unauthorized access to this department.'], 403);
            }

            // Determine the cutoff_start from request (for merging saved times)
            $cutoffStart = null;
            if ($request->has('cutoff_start')) {
                try {
                    $rawCutoff = (string) $request->query('cutoff_start');
                    $cutoffStart = Carbon::createFromFormat('Y-m-d', $rawCutoff)->startOfDay();
                    if ($cutoffStart->format('Y-m-d') !== $rawCutoff) {
                        throw new \InvalidArgumentException('Invalid cutoff date.');
                    }
                } catch (\Exception $e) {
                    return response()->json(['error' => 'cutoff_start must use YYYY-MM-DD.'], 422);
                }
            }

            $data = $this->attendanceRoster($course);

            // Merge saved CSC attendance times if cutoff_start is provided
            if ($cutoffStart) {
                $data = $this->mergeSavedTimes($data, $course, $cutoffStart);
            }

            Log::info('getAttendanceData retrieved', [
                'course'   => $course,
                'count'    => $data->count(),
            ]);

            return response()->json($data);

        } catch (\Exception $e) {
            Log::error('getAttendanceData failed: ' . $e->getMessage(), [
                'course' => $course,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Failed to retrieve attendance data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper: Safe query with column checking
     */
    private function attendanceRoster(string $course): \Illuminate\Support\Collection
    {
        $mapper = fn (string $prefix) => function ($record) use ($prefix) {
            $canonicalId = (int) ($record->employee_id ?? 0);
            $identityId = $canonicalId > 0 ? $canonicalId : (int) $record->id;
            $employeeType = $this->employeeTypeForPrefix($prefix);

            return [
                'id'            => $prefix . '-' . $identityId,
                'email'         => $record->email ?? null,
                'employee_name' => $record->employee_name ?? 'Unknown',
                'designation'   => $record->designation ?? '',
                'employee_type' => $employeeType,
                'days'          => $this->safeDecode($record->days ?? '{}'),
                'raw_id'        => $identityId,
                'source_id'     => (int) $record->id,
                'source_type'   => $prefix,
            ];
        };

        return $this->safeQuery('fulltime_timesheets', $course, 'FT', $mapper('FT'))
            ->concat($this->safeQuery('parttime_timesheets', $course, 'PT', $mapper('PT')))
            ->concat($this->safeQuery('staff_timesheets', $course, 'ST', $mapper('ST')))
            ->concat($this->safeQuery('utility_timesheets', $course, 'UT', $mapper('UT')))
            ->unique('id')
            ->sortBy('employee_name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    private function safeQuery(string $table, string $course, string $prefix, $mapper)
    {
        try {
            $query = DB::table($table)
                ->whereRaw('UPPER(TRIM(department)) = ?', [$course]);

            // Get available columns
            $columns = ['id', 'email', 'employee_name', 'designation', 'days'];

            // Add employee_type only if it exists
            if (Schema::hasColumn($table, 'employee_type')) {
                $columns[] = 'employee_type';
            }

            if (Schema::hasColumn($table, 'employee_id')) {
                $columns[] = 'employee_id';
            }

            return $query->select($columns)->orderByDesc('id')->get()->map($mapper);
        } catch (\Exception $e) {
            Log::warning("safeQuery failed for {$table}: " . $e->getMessage());
            return collect();
        }
    }

    /**
     * Helper: Merge saved per-day CSC times from attendances table.
     * Adds a 'saved_times' key to each employee entry, keyed by weekday name.
     */
    private function mergeSavedTimes($data, string $course, Carbon $cutoffStart): \Illuminate\Support\Collection
    {
        try {
            [$cutoffStart, $cutoffEnd] = $this->cutoffRange($cutoffStart);

            // Build list of dates for the cutoff period
            $cutoffDates = [];
            $currentDate = $cutoffStart->copy();
            while ($currentDate->lte($cutoffEnd)) {
                $cutoffDates[] = $currentDate->toDateString();
                $currentDate->addDay();
            }

            // Gather all raw employee IDs from the data
            $rawIds = $data->pluck('raw_id')->filter()->unique()->values()->toArray();

            if (empty($rawIds)) {
                return $data;
            }

            // Fetch all saved attendance rows for this cutoff/course
            $savedRows = DB::table('attendances')
                ->whereRaw('UPPER(TRIM(course)) = ?', [$course])
                ->whereIn('employee_id', $rawIds)
                ->whereBetween('date', [
                    $cutoffStart->toDateString(),
                    $cutoffEnd->toDateString(),
                ])
                ->select([
                    'id', 'employee_id', 'employee_type', 'date',
                    'time_in', 'time_out',
                    'am_in_time', 'am_out_time', 'pm_in_time', 'pm_out_time',
                    'lateness_minutes', 'undertime_minutes', 'overtime_minutes',
                    'total_hours', 'hours_rendered',
                    'status', 'remarks',
                ])
                ->orderBy('id')
                ->get();

            $typeCounts = $data->groupBy('raw_id')->map(
                fn ($employees) => $employees
                    ->pluck('employee_type')
                    ->map(fn ($type) => Dtr::employeeTypeKey($type))
                    ->unique()
                    ->count()
            );

            $indexed = [];
            foreach ($savedRows as $row) {
                $typeKey = Dtr::employeeTypeKey($row->employee_type);
                $indexed[$row->employee_id][$typeKey][$row->date] = $row;
            }

            return $data->map(function ($emp) use ($indexed, $cutoffDates, $typeCounts) {
                $rawId = $emp['raw_id'];
                $typeKey = Dtr::employeeTypeKey($emp['employee_type']);
                $savedTimes = [];

                foreach ($cutoffDates as $dateStr) {
                    $row = $indexed[$rawId][$typeKey][$dateStr] ?? null;
                    if (!$row && ($typeCounts[$rawId] ?? 0) === 1) {
                        $row = $indexed[$rawId][''][$dateStr] ?? null;
                    }

                    if ($row) {
                        $legacyStatus = strtolower(trim((string) ($row->status ?? '')));
                        $legacyHasWork = in_array($legacyStatus, ['present', 'late', 'half_day'], true)
                            || ($legacyStatus === '' && (float) ($row->hours_rendered ?? 0) > 0);
                        $legacyOuterOnly = $legacyHasWork
                            && !$row->am_in_time
                            && !$row->am_out_time
                            && !$row->pm_in_time
                            && !$row->pm_out_time
                            && $row->time_in
                            && $row->time_out;
                        $amIn = $row->am_in_time ?: ($legacyOuterOnly ? $row->time_in : null);
                        $amOut = $row->am_out_time ?: ($legacyOuterOnly ? Dtr::AM_DEPARTURE : null);
                        $pmIn = $row->pm_in_time ?: ($legacyOuterOnly ? Dtr::PM_ARRIVAL : null);
                        $pmOut = $row->pm_out_time ?: ($legacyOuterOnly ? $row->time_out : null);
                        $savedTimes[$dateStr] = [
                            'am_in'              => $amIn ? substr($amIn, 0, 5) : '',
                            'am_out'             => $amOut ? substr($amOut, 0, 5) : '',
                            'pm_in'              => $pmIn ? substr($pmIn, 0, 5) : '',
                            'pm_out'             => $pmOut ? substr($pmOut, 0, 5) : '',
                            'lateness_minutes'   => $row->lateness_minutes ?? 0,
                            'undertime_minutes'  => $row->undertime_minutes ?? 0,
                            'overtime_minutes'   => $row->overtime_minutes ?? 0,
                            'total_hours'        => (float) $row->total_hours > 0
                                ? $row->total_hours
                                : ($row->hours_rendered ?? 0),
                            'status'             => $row->status ?? 'present',
                            'remarks'            => $row->remarks ?? null,
                        ];
                    } else {
                        $savedTimes[$dateStr] = null;
                    }
                }

                $emp['saved_times'] = $savedTimes;
                return $emp;
            });
        } catch (\Exception $e) {
            Log::warning('mergeSavedTimes failed: ' . $e->getMessage());
            return $data;
        }
    }

    /**
     * Helper: Get employee type safely
     */
    private function getEmployeeType($record, string $prefix): string
    {
        // If employee_type exists and has a value, use it
        if (isset($record->employee_type) && !empty($record->employee_type)) {
            return $record->employee_type;
        }

        // Otherwise, infer from prefix
        return match ($prefix) {
            'FT' => 'Fulltime',
            'PT' => 'Parttime',
            'ST' => 'Staff',
            'UT' => 'Utility',
            default => 'Employee',
        };
    }

    /**
     * Helper: Safe JSON decode with error handling
     */
    private function safeDecode(?string $json): array
    {
        if (!$json) {
            return [];
        }

        try {
            $decoded = json_decode($json, true);
            return is_array($decoded) ? $decoded : [];
        } catch (\Exception $e) {
            Log::warning('JSON decode failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Helper: Calculate CSC attendance metrics for a single day.
     * Returns [lateness, undertime, overtime, totalHours].
     */
    private function calculateDayMetrics(?string $amIn, ?string $amOut, ?string $pmIn, ?string $pmOut): array
    {
        $metrics = Dtr::metrics($amIn, $amOut, $pmIn, $pmOut);

        return [
            'lateness'    => $metrics['lateness'],
            'undertime'   => $metrics['undertime'],
            'overtime'    => $metrics['overtime'],
            'total_hours' => $metrics['total_hours'],
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // API: SAVE ATTENDANCE - FIXED VERSION
    // ──────────────────────────────────────────────────────────────────────────

    public function saveAttendance(Request $request): JsonResponse
    {
        if (!$this->isAuthenticated()) {
            return $this->unauthenticatedResponse();
        }

        $validated = $request->validate([
            'course'          => 'required|string|max:50',
            'cutoff_start'    => 'required|date_format:Y-m-d',
            'attendance_data' => 'required|array',
            'attendance_data.*' => 'array',
            'attendance_data.*.id' => [
                'required',
                'regex:/^(?:(?:FT|PT|ST|UT)[-:])?[1-9][0-9]*$/i',
            ],
            'attendance_data.*.type' => 'nullable|string|max:50',
            'attendance_data.*.employee_type' => 'nullable|string|max:50',
            'attendance_data.*.employee_name' => 'nullable|string|max:255',
            'attendance_data.*.name' => 'nullable|string|max:255',
            'attendance_data.*.email' => 'nullable|email|max:255',
            'attendance_data.*.designation' => 'nullable|string|max:255',
            'attendance_data.*.attendance' => 'required|array',
            'attendance_data.*.attendance.*' => [function (string $attribute, $value, $fail) {
                if (!is_array($value)) {
                    return;
                }

                $hasPunches = collect(['am_in', 'am_out', 'pm_in', 'pm_out'])
                    ->contains(fn ($key) => trim((string) ($value[$key] ?? '')) !== '');
                $hasRemarks = trim((string) ($value['remarks'] ?? '')) !== '';
                $hasStatus = trim((string) ($value['status'] ?? '')) !== '';

                if ($hasRemarks && !$hasStatus && !$hasPunches) {
                    $fail('Choose a status when adding remarks without time entries.');
                }
            }],
            'attendance_data.*.attendance.*.am_in' => 'nullable|date_format:H:i',
            'attendance_data.*.attendance.*.am_out' => 'nullable|date_format:H:i',
            'attendance_data.*.attendance.*.pm_in' => 'nullable|date_format:H:i',
            'attendance_data.*.attendance.*.pm_out' => 'nullable|date_format:H:i',
            'attendance_data.*.attendance.*.status' => [
                'nullable',
                'string',
                'in:' . implode(',', self::ATTENDANCE_STATUSES),
            ],
            'attendance_data.*.attendance.*.remarks' => 'nullable|string|max:1000',
        ]);

        try {
            $course = strtoupper(trim($validated['course']));
            if (!$this->authorizeCourseAccess($course)) {
                return $this->unauthorizedCourseResponse();
            }

            $cutoffDate = Carbon::createFromFormat('Y-m-d', $validated['cutoff_start']);
            [$cutoffStartDate, $cutoffEndDate] = $this->cutoffRange($cutoffDate);
            $userId        = $this->getUserId();
            $saved         = 0;
            $skipped       = 0;
            $deleted       = 0;

            // Ensure attendances table has required columns
            $this->ensureAttendancesTableStructure();

            foreach ($validated['attendance_data'] as $employee) {
                $empType = $employee['type'] ?? $employee['employee_type'] ?? null;
                $identity = $this->employeeIdentity($employee['id'] ?? null, $empType);

                if (!$identity) {
                    Log::warning('Invalid employee ID skipped', [
                        'original_id' => $employee['id'] ?? null,
                        'course' => $course,
                    ]);
                    $skipped++;
                    continue;
                }

                $empName = $employee['name'] ?? $employee['employee_name'] ?? null;

                if (!$empName) {
                    Log::warning('Missing employee name', [
                        'employee_id' => $identity['typed_id'],
                        'course' => $course,
                    ]);
                    $skipped++;
                    continue;
                }

                $employeeDays = $employee['attendance'] ?? [];

                foreach ($employeeDays as $date => $dayData) {
                    $attendanceDate = $this->dateWithinCutoff(
                        (string) $date,
                        $cutoffStartDate,
                        $cutoffEndDate
                    );
                    if (!$attendanceDate) {
                        $skipped++;
                        continue;
                    }
                    $date = $attendanceDate->toDateString();

                    $punches = $this->dayPunches($dayData);
                    if ($punches === null) {
                        $skipped++;
                        continue;
                    }

                    $statusProvided = is_array($dayData) && array_key_exists('status', $dayData);
                    $remarksProvided = is_array($dayData) && array_key_exists('remarks', $dayData);
                    $status = $statusProvided
                        ? strtolower(trim((string) ($dayData['status'] ?? '')))
                        : null;
                    $remarks = $remarksProvided
                        ? trim((string) ($dayData['remarks'] ?? ''))
                        : null;
                    $metrics = Dtr::metrics(
                        $punches['am_in'],
                        $punches['am_out'],
                        $punches['pm_in'],
                        $punches['pm_out'],
                        $status
                    );

                    if (!$metrics['has_entry'] && !$status && !$remarks) {
                        $deleted += $this->deleteAttendanceDay(
                            $identity['id'],
                            $course,
                            $identity['type'],
                            $date
                        );
                        $this->deleteHistoryDay(
                            $identity['id'],
                            $course,
                            $identity['type'],
                            $date
                        );
                        continue;
                    }

                    try {
                        DB::beginTransaction();
                        $existing = $this->attendanceIdentityQuery(
                            $identity['id'],
                            $course,
                            $identity['type']
                        )->whereDate('date', $date)->orderByDesc('id')->first();
                        $finalStatus = $statusProvided
                            ? ($status ?: ($metrics['has_entry'] ? 'present' : 'absent'))
                            : ($existing?->status ?? ($metrics['has_entry'] ? 'present' : 'absent'));
                        $finalRemarks = $remarksProvided
                            ? ($remarks ?: null)
                            : ($existing?->remarks ?? null);

                        $this->upsertAttendanceDay(
                            $identity['id'],
                            $course,
                            $identity['type'],
                            $date,
                            [
                                'user_id'            => $userId,
                                'time_in'            => $punches['am_in'],
                                'time_out'           => $punches['pm_out'],
                                'am_in_time'         => $punches['am_in'],
                                'am_out_time'        => $punches['am_out'],
                                'pm_in_time'         => $punches['pm_in'],
                                'pm_out_time'        => $punches['pm_out'],
                                'hours_rendered'     => $metrics['total_hours'],
                                'lateness_minutes'   => $metrics['lateness'],
                                'undertime_minutes'  => $metrics['undertime'],
                                'overtime_minutes'   => $metrics['overtime'],
                                'total_hours'        => $metrics['total_hours'],
                                'status'             => $finalStatus,
                                'remarks'            => $finalRemarks,
                                'employee_name'      => $empName,
                                'employee_type'      => $identity['type'],
                                'updated_at'         => now(),
                            ]
                        );
                        $this->syncHistoryDay(
                            $identity['id'],
                            $course,
                            $identity['type'],
                            $date,
                            $userId,
                            $empName,
                            $employee['email'] ?? null,
                            $employee['designation'] ?? null,
                            $punches,
                            $metrics,
                            $finalStatus,
                            $finalRemarks
                        );
                        DB::commit();
                        $saved++;
                    } catch (\Throwable $e) {
                        if (DB::transactionLevel() > 0) {
                            DB::rollBack();
                        }
                        Log::warning("Failed to save attendance for {$empName}: " . $e->getMessage());
                        $skipped++;
                    }
                }
            }

            Log::info('Attendance saved', [
                'course' => $course,
                'saved' => $saved,
                'skipped' => $skipped,
                'deleted' => $deleted,
                'user_id' => $userId,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Attendance saved successfully ($saved records).",
                'saved'   => $saved,
                'skipped' => $skipped,
                'deleted' => $deleted,
            ]);

        } catch (\Exception $e) {
            Log::error('saveAttendance failed: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to save attendance: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Ensure attendances table has required columns (FIX #2)
     */
    private function ensureAttendancesTableStructure(): void
    {
        try {
            if (!Schema::hasColumn('attendances', 'created_at')) {
                Log::warning('attendances table missing created_at column');
            }
        } catch (\Exception $e) {
            Log::warning('Could not check attendances table structure: ' . $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // API: SAVE ATTENDANCE HISTORY - FIXED VERSION
    // ──────────────────────────────────────────────────────────────────────────

    public function saveAttendanceHistory(Request $request): JsonResponse
    {
        if (!$this->isAuthenticated()) {
            return $this->unauthenticatedResponse();
        }

        $validated = $request->validate([
            'course'          => 'required|string|max:50',
            'cutoff_start'    => 'required|date_format:Y-m-d',
            'attendance_data' => 'required|array',
            'attendance_data.*' => 'array',
            'attendance_data.*.id' => [
                'required',
                'regex:/^(?:(?:FT|PT|ST|UT)[-:])?[1-9][0-9]*$/i',
            ],
            'attendance_data.*.type' => 'nullable|string|max:50',
            'attendance_data.*.employee_type' => 'nullable|string|max:50',
            'attendance_data.*.employee_name' => 'nullable|string|max:255',
            'attendance_data.*.name' => 'nullable|string|max:255',
            'attendance_data.*.email' => 'nullable|email|max:255',
            'attendance_data.*.designation' => 'nullable|string|max:255',
            'attendance_data.*.attendance' => 'required|array',
            'attendance_data.*.attendance.*' => [function (string $attribute, $value, $fail) {
                if (!is_array($value)) {
                    return;
                }

                $hasPunches = collect(['am_in', 'am_out', 'pm_in', 'pm_out'])
                    ->contains(fn ($key) => trim((string) ($value[$key] ?? '')) !== '');
                $hasRemarks = trim((string) ($value['remarks'] ?? '')) !== '';
                $hasStatus = trim((string) ($value['status'] ?? '')) !== '';

                if ($hasRemarks && !$hasStatus && !$hasPunches) {
                    $fail('Choose a status when adding remarks without time entries.');
                }
            }],
            'attendance_data.*.attendance.*.am_in' => 'nullable|date_format:H:i',
            'attendance_data.*.attendance.*.am_out' => 'nullable|date_format:H:i',
            'attendance_data.*.attendance.*.pm_in' => 'nullable|date_format:H:i',
            'attendance_data.*.attendance.*.pm_out' => 'nullable|date_format:H:i',
            'attendance_data.*.attendance.*.status' => [
                'nullable',
                'string',
                'in:' . implode(',', self::ATTENDANCE_STATUSES),
            ],
            'attendance_data.*.attendance.*.remarks' => 'nullable|string|max:1000',
        ]);

        try {
            $course = strtoupper(trim($validated['course']));
            if (!$this->authorizeCourseAccess($course)) {
                return $this->unauthorizedCourseResponse();
            }
            if (!Schema::hasTable('attendance_histories')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance history storage is not available.',
                ], 503);
            }

            $cutoffDate = Carbon::createFromFormat('Y-m-d', $validated['cutoff_start']);
            [$cutoffStartDate, $cutoffEndDate] = $this->cutoffRange($cutoffDate);
            $userId        = $this->getUserId();
            $saved         = 0;
            $skipped       = 0;
            $deleted       = 0;

            foreach ($validated['attendance_data'] as $employee) {
                $empType = $employee['type'] ?? $employee['employee_type'] ?? null;
                $identity = $this->employeeIdentity($employee['id'] ?? null, $empType);

                if (!$identity) {
                    Log::warning('Invalid employee ID skipped in history', [
                        'original_id' => $employee['id'] ?? null,
                        'course' => $course,
                    ]);
                    $skipped++;
                    continue;
                }

                $empName = $employee['name'] ?? $employee['employee_name'] ?? null;

                if (!$empName) {
                    Log::warning('Missing employee name in history', [
                        'employee_id' => $identity['typed_id'],
                        'course' => $course,
                    ]);
                    $skipped++;
                    continue;
                }

                $employeeDays = $employee['attendance'] ?? [];

                foreach ($employeeDays as $date => $dayData) {
                    $attendanceDate = $this->dateWithinCutoff(
                        (string) $date,
                        $cutoffStartDate,
                        $cutoffEndDate
                    );
                    if (!$attendanceDate) {
                        $skipped++;
                        continue;
                    }
                    $date = $attendanceDate->toDateString();

                    $punches = $this->dayPunches($dayData);
                    if ($punches === null) {
                        $skipped++;
                        continue;
                    }
                    $statusProvided = is_array($dayData) && array_key_exists('status', $dayData);
                    $remarksProvided = is_array($dayData) && array_key_exists('remarks', $dayData);
                    $status = $statusProvided
                        ? strtolower(trim((string) ($dayData['status'] ?? '')))
                        : null;
                    $remarks = $remarksProvided
                        ? trim((string) ($dayData['remarks'] ?? ''))
                        : null;
                    $metrics = Dtr::metrics(
                        $punches['am_in'],
                        $punches['am_out'],
                        $punches['pm_in'],
                        $punches['pm_out'],
                        $status
                    );

                    if (!$metrics['has_entry'] && !$status && !$remarks) {
                        $deleted += $this->deleteHistoryDay(
                            $identity['id'],
                            $course,
                            $identity['type'],
                            $date
                        );
                        continue;
                    }

                    try {
                        $existing = $this->historyIdentityQuery(
                            $identity['id'],
                            $course,
                            $identity['type']
                        )->whereDate('attendance_date', $date)->orderByDesc('id')->first();

                        $this->upsertHistoryDay(
                            $identity['id'],
                            $course,
                            $identity['type'],
                            $date,
                            [
                                'employee_name' => $empName,
                                'email'         => $employee['email'] ?? null,
                                'employee_type' => $identity['type'],
                                'designation'   => $employee['designation'] ?? null,
                                'department'    => $course,
                                'is_present'    => $metrics['present'],
                                'hours_worked'  => $metrics['total_hours'],
                                'time_in'       => $punches['am_in'],
                                'time_out'      => $punches['pm_out'],
                                'status'        => $statusProvided
                                    ? ($status ?: ($metrics['has_entry'] ? 'present' : 'absent'))
                                    : ($existing?->status ?? ($metrics['has_entry'] ? 'present' : 'absent')),
                                'remarks'       => $remarksProvided
                                    ? ($remarks ?: null)
                                    : ($existing?->remarks ?? null),
                                'location'      => null,
                                'user_id'       => $userId,
                                'updated_at'    => now(),
                            ]
                        );
                        $saved++;
                    } catch (\Exception $e) {
                        Log::warning("Failed to save history for {$empName}: " . $e->getMessage());
                        $skipped++;
                    }
                }
            }

            Log::info('Attendance history saved', [
                'course' => $course,
                'saved' => $saved,
                'skipped' => $skipped,
                'deleted' => $deleted,
                'user_id' => $userId,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Attendance history saved ($saved records).",
                'saved'   => $saved,
                'skipped' => $skipped,
                'deleted' => $deleted,
            ]);

        } catch (\Exception $e) {
            Log::error('saveAttendanceHistory failed: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to save attendance history: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // API: BULK DELETE
    // ──────────────────────────────────────────────────────────────────────────

    public function bulkDeleteAttendance(Request $request): JsonResponse
    {
        if (!$this->isAuthenticated()) {
            return $this->unauthenticatedResponse();
        }

        $validated = $request->validate([
            'course'           => 'required|string|max:50',
            'cutoff_start'     => 'required|date_format:Y-m-d',
            'employee_ids'     => 'required_without:employees|array|max:500',
            'employee_ids.*'   => [
                'required',
                'regex:/^(?:(?:FT|PT|ST|UT)[-:])?[1-9][0-9]*$/i',
            ],
            'employees'        => 'required_without:employee_ids|array|max:500',
            'employees.*.id'   => 'required|integer|min:1',
            'employees.*.type' => 'required|string|max:50',
        ]);

        try {
            $course = strtoupper(trim($validated['course']));
            if (!$this->authorizeCourseAccess($course)) {
                return $this->unauthorizedCourseResponse();
            }

            $structuredIdentities = collect($validated['employees'] ?? [])
                ->map(fn ($employee) => $this->employeeIdentity(
                    $employee['id'] ?? null,
                    $employee['type'] ?? null
                ));
            $typedIdentities = collect($validated['employee_ids'] ?? [])
                ->map(fn ($employeeId) => $this->employeeIdentity($employeeId));
            $identities = $structuredIdentities
                ->merge($typedIdentities)
                ->filter()
                ->unique('typed_id')
                ->values();

            if ($identities->isEmpty()) {
                return response()->json(['error' => 'No valid employee IDs provided.'], 400);
            }

            $cutoffDate = Carbon::createFromFormat('Y-m-d', $validated['cutoff_start']);
            [$cutoffStart, $cutoffEnd] = $this->cutoffRange($cutoffDate);
            $deleted = 0;
            $historyDeleted = 0;

            foreach ($identities as $identity) {
                $deleted += $this->attendanceIdentityQuery(
                    $identity['id'],
                    $course,
                    $identity['type']
                )->whereBetween('date', [
                    $cutoffStart->toDateString(),
                    $cutoffEnd->toDateString(),
                ])->delete();

                if (Schema::hasTable('attendance_histories')) {
                    $historyDeleted += $this->historyIdentityQuery(
                        $identity['id'],
                        $course,
                        $identity['type']
                    )->whereBetween('attendance_date', [
                        $cutoffStart->toDateString(),
                        $cutoffEnd->toDateString(),
                    ])->delete();
                }
            }

            Log::info('Attendance records deleted', [
                'course' => $course,
                'deleted_count' => $deleted,
                'history_deleted_count' => $historyDeleted,
                'cutoff_start' => $cutoffStart->toDateString(),
                'cutoff_end' => $cutoffEnd->toDateString(),
                'user_id' => $this->getUserId(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted $deleted attendance record(s).",
                'deleted' => $deleted,
                'history_deleted' => $historyDeleted,
            ]);

        } catch (\Exception $e) {
            Log::error('bulkDeleteAttendance failed: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to delete records.'], 500);
        }
    }
}
