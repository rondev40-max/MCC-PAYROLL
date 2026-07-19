<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail; 
use Carbon\Carbon;

class OtpVerificationController extends Controller
{
    /**
     * Display the OTP verification form.
     */
    public function showVerificationForm()
    {
        // Ensure there is a user ID in the session before showing the form
        if (!session('2fa:user:id')) {
            return redirect()->route('login');
        }
        
        return view('auth.verify-otp');
    }

    /**
     * Handle the OTP verification process.
     */
    public function verify(Request $request)
    {
        // Validation: 6 digits, required, numeric
        $request->validate([
            'otp' => ['required', 'numeric', 'digits:6'],
        ]);

        $userId = session('2fa:user:id');
        
        // Check if user is being tracked in session
        if (!$userId) {
            return back()->withErrors(['otp' => 'Session expired. Please log in again.']);
        }

        $user = User::find($userId);

        if (!$user) {
            session()->forget('2fa:user:id');
            return redirect()->route('login');
        }
        
        // **CORE VERIFICATION LOGIC**
        // 1. Check if the provided OTP matches the stored OTP and is not expired.
        if ((string)$user->otp_code === $request->otp && Carbon::now()->isBefore($user->otp_expires_at)) {
            // SUCCESS: OTP is valid and not expired

            // --- START: Logic for updating last login, IP, and session ID ---

            // NOTE: Removed session regeneration to prevent 419 Page Expired right after login
            // (regenerating session here can invalidate CSRF/session state for the next request/redirect)

            $user->update([
                'last_login_at' => now(),           // Update Last Login Time
                'last_login_ip' => $request->ip(),   // Update IP Address
                'session_id' => $request->session()->getId(), // Update Session ID
            ]);

            // Clear OTP fields in the database
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->save();

            // Clear the 2FA tracker session
            session()->forget('2fa:user:id'); 

            // Log in the user
            Auth::login($user); 
            
            // Set session data needed by your custom middleware/routes
            $isAdmin = ($user->role === 'super_admin' || $user->role === 'admin');
            
            session([
                'user_role' => $user->role,
                'is_admin' => $isAdmin,
                // user_id and user_name can usually be accessed via Auth::user()
            ]);
            
            // Conditional redirect based on role
            if ($user->role === 'super_admin') {
                return redirect()->route('admin.user-management')->with('success', 'Super Admin login successful!');
            } 
            
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard')->with('success', 'Admin login successful!');
            }
            
            if ($user->role === 'attendance_checker') {
                // Use intended() for a more flexible redirect after login
                return redirect()->intended('/attendance/dashboard')->with('success', 'Attendance checker login successful!');
            }

            // Fallback redirect
            return redirect('/')->with('success', 'Login successful!');

        }
        
        // Failure: OTP is incorrect or expired
        return back()->withErrors(['otp' => 'The verification code is invalid or has expired. Please check your email or log in again.']);
    }
    
    /**
     * Handle the resend OTP request.
     */
    public function resendOtp(Request $request)
    {
        $userId = session('2fa:user:id');

        // Check if user is being tracked in session
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Session expired. Please try to log in again.');
        }

        $user = User::find($userId);

        if (!$user) {
            session()->forget('2fa:user:id');
            return redirect()->route('login')->with('error', 'User not found.');
        }

        // 1. Generate NEW OTP
        $otp = rand(100000, 999999);
        
        // 2. Store the NEW OTP and NEW Expiration Time (5 minutes from now)
        $user->otp_code = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(5);
        $user->save();
        
        try {
            // 3. Send the NEW OTP via Email
            Mail::to($user->email)->send(new OtpMail($otp));
        } catch (\Exception $e) {
            // Log the error for debugging without exposing sensitive info in production
            \Log::error("OTP Email failed for user ID: {$user->id}. Error: " . $e->getMessage());
            return back()->with('error', 'Failed to send new verification code. Check your mail settings.');
        }

        // 4. I-redirect pabalik sa verification form na may success message
        return back()->with('message', 'A new verification code has been sent to your email.');
    }
}