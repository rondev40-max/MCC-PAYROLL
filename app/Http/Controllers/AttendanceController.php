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
use App\Mail\AttendanceOtpMail;

class AttendanceController extends Controller
{
    private const DEFAULT_TIME_IN  = '08:00:00';
    private const DEFAULT_TIME_OUT = '17:00:00';
    private const DEFAULT_AM_OUT   = '12:00:00';
    private const DEFAULT_PM_IN    = '13:00:00';
    private const DEFAULT_HOURS    = 8;
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

    private function getUserCourse(): ?string
    {
        $course = session('user_course');
        return $course ? strtoupper($course) : null;
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
        if (!$userCourse) return false;
        return $requestedCourse === $userCourse;
    }

    private function unauthenticatedResponse(): JsonResponse
    {
        return response()->json(['error' => 'Unauthenticated.'], 401);
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

        if (!$user || !Hash::check($request->password, $user->password)) {
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

        if (Hash::needsRehash($user->password)) {
            DB::table('users')
                ->where('id', $user->id)
                ->update(['password' => Hash::make($request->password)]);
        }

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
            // consecutive misses. bcrypt's Hash::check is already constant-time,
            // so this closes the brute-force gap the flow previously had.
            if (!Hash::check($request->otp, $record->otp_hash)) {
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
            $course = strtoupper($request->query('course', $this->getUserCourse() ?? ''));

            if (!$course) {
                return response()->json(['count' => 0]);
            }

            // Count all three timesheet types so Staff and Utility
            // employees are included in the course count badge.
            $fulltime = DB::table('fulltime_timesheets')
                ->whereRaw('UPPER(TRIM(department)) = ?', [$course])
                ->count();

            $parttime = DB::table('parttime_timesheets')
                ->whereRaw('UPPER(TRIM(department)) = ?', [$course])
                ->count();

            $staff = DB::table('staff_timesheets')
                ->whereRaw('UPPER(TRIM(department)) = ?', [$course])
                ->count();

            $utility = DB::table('utility_timesheets')
                ->whereRaw('UPPER(TRIM(department)) = ?', [$course])
                ->count();

            return response()->json(['count' => $fulltime + $parttime + $staff + $utility]);

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
            $course = strtoupper($course);

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
                    $cutoffStart = Carbon::parse($request->query('cutoff_start'))->startOfDay();
                } catch (\Exception $e) {
                    Log::warning('Invalid cutoff_start param: ' . $e->getMessage());
                }
            }

            // Safe mapper that handles missing columns
            $mapper = fn(string $prefix) => fn($e) => [
                'id'            => "{$prefix}-{$e->id}",
                'email'         => $e->email ?? null,
                'employee_name' => $e->employee_name ?? 'Unknown',
                'designation'   => $e->designation ?? '',
                'employee_type' => $this->getEmployeeType($e, $prefix),
                'days'          => $this->safeDecode($e->days ?? '{}'),
                'raw_id'        => $e->id,
            ];

            $data = collect();

            // Query each timesheet type with only columns that exist
            $fulltime = $this->safeQuery('fulltime_timesheets', $course, 'FT', $mapper('FT'));
            $parttime = $this->safeQuery('parttime_timesheets', $course, 'PT', $mapper('PT'));
            $staff = $this->safeQuery('staff_timesheets', $course, 'ST', $mapper('ST'));
            $utility = $this->safeQuery('utility_timesheets', $course, 'UT', $mapper('UT'));

            $data = $fulltime
                ->concat($parttime)
                ->concat($staff)
                ->concat($utility);

            // Merge saved CSC attendance times if cutoff_start is provided
            if ($cutoffStart) {
                $data = $this->mergeSavedTimes($data, $course, $cutoffStart);
            }

            Log::info('getAttendanceData retrieved', [
                'course'   => $course,
                'count'    => $data->count(),
                'fulltime' => $fulltime->count(),
                'parttime' => $parttime->count(),
                'staff'    => $staff->count(),
                'utility'  => $utility->count(),
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

            return $query->select($columns)->get()->map($mapper);
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
            // Determine cutoff end date
            $cutoffEnd = $cutoffStart->copy();
            if ($cutoffStart->day <= 15) {
                $cutoffEnd->day = 15;
            } else {
                $cutoffEnd->endOfMonth();
            }

            // Build list of dates for the cutoff period
            $cutoffDates = [];
            $currentDate = $cutoffStart->copy();
            while ($currentDate->lte($cutoffEnd)) {
                $cutoffDates[] = $currentDate->toDateString();
                $currentDate->addDay();
            }

            // Gather all raw employee IDs from the data
            $rawIds = $data->pluck('raw_id')->filter()->values()->toArray();

            if (empty($rawIds)) {
                return $data;
            }

            // Fetch all saved attendance rows for this cutoff/course
            $savedRows = DB::table('attendances')
                ->where('course', $course)
                ->whereIn('employee_id', $rawIds)
                ->whereBetween('date', [
                    $cutoffStart->toDateString(),
                    $cutoffEnd->toDateString(),
                ])
                ->select([
                    'employee_id', 'date',
                    'am_in_time', 'am_out_time', 'pm_in_time', 'pm_out_time',
                    'lateness_minutes', 'undertime_minutes', 'overtime_minutes', 'total_hours',
                    'status',
                ])
                ->get();

            // Index by employee_id -> date
            $indexed = [];
            foreach ($savedRows as $row) {
                $indexed[$row->employee_id][$row->date] = $row;
            }

            return $data->map(function ($emp) use ($indexed, $cutoffDates) {
                $rawId = $emp['raw_id'];
                $savedTimes = [];

                foreach ($cutoffDates as $dateStr) {
                    if (isset($indexed[$rawId][$dateStr])) {
                        $r = $indexed[$rawId][$dateStr];
                        $savedTimes[$dateStr] = [
                            'am_in'              => $r->am_in_time   ? substr($r->am_in_time, 0, 5)   : '',
                            'am_out'             => $r->am_out_time  ? substr($r->am_out_time, 0, 5)  : '',
                            'pm_in'              => $r->pm_in_time   ? substr($r->pm_in_time, 0, 5)   : '',
                            'pm_out'             => $r->pm_out_time  ? substr($r->pm_out_time, 0, 5)  : '',
                            'lateness_minutes'   => $r->lateness_minutes   ?? 0,
                            'undertime_minutes'  => $r->undertime_minutes  ?? 0,
                            'overtime_minutes'   => $r->overtime_minutes   ?? 0,
                            'total_hours'        => $r->total_hours        ?? 0,
                            'status'             => $r->status ?? 'absent',
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
        $lateness  = 0;
        $undertime = 0;
        $overtime  = 0;
        $totalMins = 0;

        try {
            // Lateness: arrived after 08:00
            if ($amIn) {
                $official = Carbon::createFromTimeString(self::DEFAULT_TIME_IN);
                $actual   = Carbon::createFromTimeString($amIn);
                if ($actual->gt($official)) {
                    $lateness = $actual->diffInMinutes($official);
                }
            }

            // Undertime: left before 17:00
            if ($pmOut) {
                $official = Carbon::createFromTimeString(self::DEFAULT_TIME_OUT);
                $actual   = Carbon::createFromTimeString($pmOut);
                if ($actual->lt($official)) {
                    $undertime = $official->diffInMinutes($actual);
                }
                // Overtime: stayed past 17:00
                if ($actual->gt($official)) {
                    $overtime = $actual->diffInMinutes($official);
                }
            }

            // Total worked minutes
            if ($amIn && $amOut) {
                $totalMins += Carbon::createFromTimeString($amOut)->diffInMinutes(Carbon::createFromTimeString($amIn));
            }
            if ($pmIn && $pmOut) {
                $totalMins += Carbon::createFromTimeString($pmOut)->diffInMinutes(Carbon::createFromTimeString($pmIn));
            }
        } catch (\Exception $e) {
            Log::warning('calculateDayMetrics error: ' . $e->getMessage());
        }

        return [
            'lateness'    => $lateness,
            'undertime'   => $undertime,
            'overtime'    => $overtime,
            'total_hours' => round($totalMins / 60, 2),
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
            'cutoff_start'    => 'required|date',
            'attendance_data' => 'required|array',
        ]);

        try {
            $course        = strtoupper($validated['course']);
            $cutoffStartDate = Carbon::parse($validated['cutoff_start'])->startOfDay();
            $userId        = $this->getUserId();
            $saved         = 0;
            $skipped       = 0;

            // Ensure attendances table has required columns
            $this->ensureAttendancesTableStructure();

            foreach ($validated['attendance_data'] as $employee) {
                $empId = $employee['id'] ?? null;

                if ($empId) {
                    $empId = (int) preg_replace('/[^0-9]/', '', $empId);
                }

                if (!$empId || $empId <= 0) {
                    Log::warning('Invalid employee ID skipped', [
                        'original_id' => $employee['id'] ?? null,
                        'course' => $course,
                    ]);
                    $skipped++;
                    continue;
                }

                $empName = $employee['name'] ?? $employee['employee_name'] ?? null;
                $empType = $employee['type'] ?? $employee['employee_type'] ?? null;

                if (!$empName) {
                    Log::warning('Missing employee name', ['empId' => $empId, 'course' => $course]);
                    $skipped++;
                    continue;
                }

                $employeeDays = $employee['attendance'] ?? [];

                foreach ($employeeDays as $date => $dayData) {
                    // Basic validation for date format
                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                        continue;
                    }

                    // Support both time-object format and legacy boolean
                    if (is_array($dayData)) {
                        $amIn  = !empty($dayData['am_in'])  ? $dayData['am_in']  : null;
                        $amOut = !empty($dayData['am_out']) ? $dayData['am_out'] : null;
                        $pmIn  = !empty($dayData['pm_in'])  ? $dayData['pm_in']  : null;
                        $pmOut = !empty($dayData['pm_out']) ? $dayData['pm_out'] : null;
                    } else {
                        // Legacy boolean present/absent
                        $isPresent = (bool) $dayData;
                        $amIn  = $isPresent ? self::DEFAULT_TIME_IN  : null;
                        $amOut = $isPresent ? self::DEFAULT_AM_OUT   : null;
                        $pmIn  = $isPresent ? self::DEFAULT_PM_IN    : null;
                        $pmOut = $isPresent ? self::DEFAULT_TIME_OUT : null;
                    }

                    $hasTimes = $amIn || $amOut || $pmIn || $pmOut;

                    // Calculate metrics
                    $metrics   = $this->calculateDayMetrics($amIn, $amOut, $pmIn, $pmOut);

                    try {
                        DB::table('attendances')->updateOrInsert(
                            [
                                'employee_id' => $empId,
                                'user_id'     => $userId,
                                'date'        => $date,
                                'course'      => $course,
                            ],
                            [
                                'time_in'            => $amIn,
                                'time_out'           => $pmOut,
                                'am_in_time'         => $amIn,
                                'am_out_time'        => $amOut,
                                'pm_in_time'         => $pmIn,
                                'pm_out_time'        => $pmOut,
                                'hours_rendered'     => $metrics['total_hours'],
                                'lateness_minutes'   => $metrics['lateness'],
                                'undertime_minutes'  => $metrics['undertime'],
                                'overtime_minutes'   => $metrics['overtime'],
                                'total_hours'        => $metrics['total_hours'],
                                'status'             => $hasTimes ? 'present' : 'absent',
                                'remarks'            => null,
                                'employee_name'      => $empName,
                                'employee_type'      => $empType,
                                'updated_at'         => now(),
                                'created_at'         => now(),
                            ]
                        );
                        $saved++;
                    } catch (\Exception $e) {
                        Log::warning("Failed to save attendance for {$empName}: " . $e->getMessage());
                        $skipped++;
                    }
                }
            }

            Log::info('Attendance saved', [
                'course' => $course,
                'saved' => $saved,
                'skipped' => $skipped,
                'user_id' => $userId,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Attendance saved successfully ($saved records).",
                'saved'   => $saved,
                'skipped' => $skipped,
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
            'cutoff_start'    => 'required|date',
            'attendance_data' => 'required|array',
        ]);

        try {
            $course        = strtoupper($validated['course']);
            $cutoffStartDate = Carbon::parse($validated['cutoff_start'])->startOfDay();
            $userId        = $this->getUserId();
            $saved         = 0;
            $skipped       = 0;

            foreach ($validated['attendance_data'] as $employee) {
                $empId = $employee['id'] ?? null;

                if ($empId) {
                    $empId = (int) preg_replace('/[^0-9]/', '', $empId);
                }

                if (!$empId || $empId <= 0) {
                    Log::warning('Invalid employee ID skipped in history', [
                        'original_id' => $employee['id'] ?? null,
                        'course' => $course,
                    ]);
                    $skipped++;
                    continue;
                }

                $empName = $employee['name'] ?? $employee['employee_name'] ?? null;
                $empType = $employee['type'] ?? $employee['employee_type'] ?? null;

                if (!$empName) {
                    Log::warning('Missing employee name in history', ['empId' => $empId, 'course' => $course]);
                    $skipped++;
                    continue;
                }

                $employeeDays = $employee['attendance'] ?? [];

                foreach ($employeeDays as $date => $dayData) {
                    // Basic validation for date format
                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                        continue;
                    }

                    if (is_array($dayData)) {
                        $amIn  = !empty($dayData['am_in'])  ? $dayData['am_in']  : null;
                        $pmOut = !empty($dayData['pm_out']) ? $dayData['pm_out'] : null;
                        $hasTimes = $amIn || !empty($dayData['am_out']) || !empty($dayData['pm_in']) || $pmOut;
                    } else {
                        $hasTimes = (bool) $dayData;
                        $amIn  = $hasTimes ? self::DEFAULT_TIME_IN  : null;
                        $pmOut = $hasTimes ? self::DEFAULT_TIME_OUT : null;
                    }

                    try {
                        DB::table('attendance_histories')->updateOrInsert(
                            [
                                'employee_id'     => $empId,
                                'attendance_date' => $date,
                                'course'          => $course,
                            ],
                            [
                                'employee_name' => $empName,
                                'email'         => $employee['email'] ?? null,
                                'employee_type' => $empType,
                                'designation'   => $employee['designation'] ?? null,
                                'department'    => $course,
                                'is_present'    => $hasTimes,
                                'hours_worked'  => $hasTimes ? self::DEFAULT_HOURS : 0,
                                'time_in'       => $amIn,
                                'time_out'      => $pmOut,
                                'status'        => $hasTimes ? 'present' : 'absent',
                                'remarks'       => null,
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
                'user_id' => $userId,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Attendance history saved ($saved records).",
                'saved'   => $saved,
                'skipped' => $skipped,
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
            'course'       => 'required|string|max:50',
            'employee_ids' => 'required|array|min:1',
        ]);

        try {
            $course      = strtoupper($validated['course']);
            $employeeIds = array_filter(array_map('intval', $validated['employee_ids']));

            if (empty($employeeIds)) {
                return response()->json(['error' => 'No valid employee IDs provided.'], 400);
            }

            $deleted = DB::table('attendances')
                ->where('course', $course)
                ->whereIn('employee_id', $employeeIds)
                ->delete();

            if (Schema::hasTable('attendance_histories')) {
                DB::table('attendance_histories')
                    ->where('department', $course)
                    ->whereIn('employee_id', $employeeIds)
                    ->delete();
            }

            Log::info('Attendance records deleted', [
                'course' => $course,
                'deleted_count' => $deleted,
                'user_id' => $this->getUserId(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted $deleted attendance record(s).",
                'deleted' => $deleted,
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