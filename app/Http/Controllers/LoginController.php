<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

class LoginController extends Controller
{
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'user_type' => ['required', 'in:admin,attendance,employee'],

        ]);
 
        if ($credentials['user_type'] === 'admin') {
            $allowedDomains = ['mcc.edu.ph', 'mcclawis.edu.ph'];
            $domainPattern = '/@(' . implode('|', array_map('preg_quote', $allowedDomains)) . ')$/i';
            if (!preg_match($domainPattern, $credentials['email'])) {
                return back()->with('error', 'Admin login requires an @mcc.edu.ph or @mcclawis.edu.ph email address.')->withInput();
            }
        }
 
        $user = User::where('email', $credentials['email'])->first();

        // 1. Initial Validation: User not found
        if (!$user) {
            // Gumamit ng session('error') para gumana ang SweetAlert sa Blade
            return back()->with('error', 'User not yet registered.')->withInput();
        }

        // 2. Initial Validation: Wrong Password
        if (!Hash::check($credentials['password'], $user->password)) {
            // Gumamit ng session('error') para gumana ang SweetAlert sa Blade
            return back()->with('error', 'Password is wrong. Please try again.')->withInput();
        }

        // 3. Role Check (Verify if the user is trying to log into the correct type)
        $userRole = strtolower(trim($user->role ?? ''));
        $allowed = false;
        if ($credentials['user_type'] === 'admin' && in_array($userRole, ['admin', 'super_admin', 'superadmin', 'administrator'], true)) {
            $allowed = true;
        } elseif ($credentials['user_type'] === 'attendance' && $userRole === 'attendance_checker') {
            $allowed = true;
        } elseif ($credentials['user_type'] === 'employee' && $userRole === 'employee') {
            $allowed = true;
        }
 
 
        if (!$allowed) {
            // Gumamit ng session('error') para gumana ang SweetAlert sa Blade
            return back()->with('error', "Access denied. Your account role is '{$user->role}'. Please use the correct portal for your account type or contact your system administrator.")->withInput();
        }


        // =========================================================
        // 4. LOGIN
        // =========================================================

        // Attendance checker uses its own lightweight session-based login
        // (see CheckAttendanceRole middleware) rather than Laravel Auth, and
        // is intentionally not gated by OTP here — it's a separate, lower-
        // privilege flow already covered by its own throttled routes.
        if ($user->role === 'attendance_checker') {
            // Set session data for attendance
            $request->session()->put([
                'user_id'       => $user->id,
                'user_name'     => $user->name,
                'user_role'     => 'attendance_checker',
                'user_course'   => $user->course ?? null,
                'is_attendance' => true,
            ]);

            Log::info('Attendance user logged in', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_course' => $user->course,
            ]);

            return redirect()->route('attendance.dashboard')->with('success', 'Attendance login successful!');
        }

        // Admin and employee accounts require a second factor before Auth::login()
        // is called. Generate a fresh OTP, reset any prior attempt/lock state so
        // this new code starts clean, and hand off to OtpVerificationController.
        $otp = random_int(100000, 999999);

        $user->otp_code = $otp;
        $user->otp_expires_at = now()->addMinutes(5);
        $user->otp_attempts = 0;
        $user->otp_locked_until = null;
        $user->save();

        try {
            Mail::to($user->email)->send(new OtpMail($otp));
        } catch (\Exception $e) {
            Log::error("OTP email failed to send for user ID: {$user->id}. Error: " . $e->getMessage());
            return back()->with('error', 'Could not send your verification code. Please try again in a moment.')->withInput();
        }

        $request->session()->put('2fa:user:id', $user->id);
 
        return redirect()->route('otp.verify.form')
            ->with('info', 'Enter the OTP code sent to your email to verify your login.');
    }
}