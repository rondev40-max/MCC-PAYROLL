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
        $allowed = false;
        if ($credentials['user_type'] === 'admin' && ($user->role === 'admin' || $user->role === 'super_admin')) {
            $allowed = true;
        } elseif ($credentials['user_type'] === 'attendance' && $user->role === 'attendance_checker') {
            $allowed = true;
        } elseif ($credentials['user_type'] === 'employee' && $user->role === 'employee') {
            $allowed = true;
        }


        if (!$allowed) {
            // Gumamit ng session('error') para gumana ang SweetAlert sa Blade
            return back()->with('error', 'Access denied. Incorrect user type selected for your role.')->withInput();
        }


        // =========================================================
        // 4. LOGIN (OTP DISABLED)
        // =========================================================

        // Handle attendance_checker role separately (doesn't use Laravel Auth)
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

        // For admin and employee users, use Laravel Auth
        Auth::login($user);

        // Set session data for other roles
        $isAdmin = ($user->role === 'super_admin' || $user->role === 'admin');
        session([
            'user_role' => $user->role,
            'is_admin' => $isAdmin,
        ]);

        // Regenerate session for security
        $request->session()->regenerate();

        if ($user->role === 'super_admin' || $user->role === 'admin') {
            return redirect()->route('admin.dashboard')->with('success', 'Admin login successful!');
        }

        if ($user->role === 'employee') {
            return redirect()->route('employee.dashboard')->with('success', 'Employee login successful!');
        }

        return redirect()->intended('/')->with('success', 'Login successful!');

    }
}
