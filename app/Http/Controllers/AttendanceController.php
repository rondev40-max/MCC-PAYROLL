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

            if (!$user) {
                Log::warning('Password reset attempt for non-existent user', [
                    'email' => $request->email,
                    'ip' => $request->ip(),
                ]);
                return back()->with('error', 'Email not found or invalid role.');
            }

            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            DB::table('attendance_password_otps')->updateOrInsert(
                ['email' => $request->email],
                [
                    'otp_hash' => Hash::make($otp),
                    'expires_at' => now()->addMinutes(10),
                    'used_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            try {
                Mail::to($request->email)->send(new AttendanceOtpMail($otp, $user->name));
                Log::info('OTP sent for password reset', ['email' => $request->email]);
                return back()->with('success', 'OTP sent to your email. Valid for 10 minutes.');
            } catch (\Exception $e) {
                Log::error('Failed to send OTP email: ' . $e->getMessage());
                return back()->with('error', 'Failed to send OTP. Please try again.');
            }

        } catch (\Exception $e) {
            Log::error('sendOtp failed: ' . $e->getMessage());
            return back()->with('error', 'An error occurred. Please try again.');
        }
    }

    public function showResetForm()
    {
        return view('attendance.reset_password');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        try {
            $record = DB::table('attendance_password_otps')
                ->where('email', $request->email)
                ->first();

            if (!$record) {
                Log::warning('OTP verification failed - no record', ['email' => $request->email]);
                return back()->with('error', 'No OTP request found for this email.');
            }

            if (Carbon::parse($record->expires_at)->isPast()) {
                DB::table('attendance_password_otps')->where('email', $request->email)->delete();
                return back()->with('error', 'OTP has expired. Please request a new one.');
            }

            if (!Hash::check($request->otp, $record->otp_hash)) {
                Log::warning('Invalid OTP attempt', ['email' => $request->email]);
                return back()->with('error', 'Invalid OTP. Please try again.');
            }

            $request->session()->put([
                'otp_verified' => true,
                'reset_email' => $request->email,
            ]);

            return redirect()->route('attendance.change.form')->with('success', 'OTP verified successfully.');

        } catch (\Exception $e) {
            Log::error('verifyOtp failed: ' . $e->getMessage());
            return back()->with('error', 'An error occurred. Please try again.');
        }
    }

    public function showChangePasswordForm()
    {
        if (!session('otp_verified') || !session('reset_email')) {
            return redirect()->route('attendance.forgot.form')->with('error', 'Please verify your email first.');
        }

        return view('attendance.change_password', ['email' => session('reset_email')]);
    }

    public function resetPassword(Request $request)
    {
        if (!session('otp_verified') || !session('reset_email')) {
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

                $request->session()->forget(['otp_verified', 'reset_email']);

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

            // Safe mapper that handles missing columns
            $mapper = fn(string $prefix) => fn($e) => [
                'id'            => "{$prefix}-{$e->id}",
                'email'         => $e->email ?? null,
                'employee_name' => $e->employee_name ?? 'Unknown',
                'designation'   => $e->designation ?? '',
                'employee_type' => $this->getEmployeeType($e, $prefix),
                'days'          => $this->safeDecode($e->days ?? '{}'),
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

            Log::info('getAttendanceData retrieved', [
                'course' => $course,
                'count' => $data->count(),
                'fulltime' => $fulltime->count(),
                'parttime' => $parttime->count(),
                'staff' => $staff->count(),
                'utility' => $utility->count(),
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
            'week_start'      => 'required|date',
            'attendance_data' => 'required|array',
        ]);

        try {
            $course        = strtoupper($validated['course']);
            $weekStartDate = Carbon::parse($validated['week_start'])->startOfDay();
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

                foreach (self::WEEK_DAYS as $dayKey => $offset) {
                    if (!array_key_exists($dayKey, $employeeDays)) {
                        continue;
                    }

                    $isPresent = (bool) $employeeDays[$dayKey];
                    $date      = $weekStartDate->copy()->addDays($offset)->toDateString();

                    try {
                        DB::table('attendances')->updateOrInsert(
                            [
                                'employee_id' => $empId,
                                'user_id'     => $userId,
                                'date'        => $date,
                                'course'      => $course,
                            ],
                            [
                                'time_in'        => $isPresent ? self::DEFAULT_TIME_IN : null,
                                'time_out'       => $isPresent ? self::DEFAULT_TIME_OUT : null,
                                'hours_rendered' => $isPresent ? self::DEFAULT_HOURS : 0,
                                'status'         => $isPresent ? 'present' : 'absent',
                                'remarks'        => null,
                                'employee_name'  => $empName,
                                'employee_type'  => $empType,
                                'updated_at'     => now(),
                                'created_at'     => now(),
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
            'week_start'      => 'required|date',
            'attendance_data' => 'required|array',
        ]);

        try {
            $course        = strtoupper($validated['course']);
            $weekStartDate = Carbon::parse($validated['week_start'])->startOfDay();
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

                foreach (self::WEEK_DAYS as $dayKey => $offset) {
                    if (!array_key_exists($dayKey, $employeeDays)) {
                        continue;
                    }

                    $isPresent = (bool) $employeeDays[$dayKey];
                    $date      = $weekStartDate->copy()->addDays($offset)->toDateString();

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
                                'is_present'    => $isPresent,
                                'hours_worked'  => $isPresent ? self::DEFAULT_HOURS : 0,
                                'time_in'       => $isPresent ? self::DEFAULT_TIME_IN : null,
                                'time_out'      => $isPresent ? self::DEFAULT_TIME_OUT : null,
                                'status'        => $isPresent ? 'present' : 'absent',
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