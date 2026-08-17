<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use App\Mail\OtpMail;
use App\Support\RoleHome;
use Carbon\Carbon;

class OtpVerificationController extends Controller
{
    /**
     * Display the OTP verification form.
     */
    public function showVerificationForm()
    {
        if (!Schema::hasColumns('users', ['otp_code', 'otp_expires_at', 'otp_attempts', 'otp_locked_until'])) {
            return redirect()->route('index')->with('warning', 'OTP verification is unavailable. Please log in again.');
        }
 
        // If there's no 2FA session, show a helpful waiting/paste-code page instead
        // of immediately redirecting. The form will allow entering email + code
        // as a fallback when the temporary 2FA session has been lost.
        $sessionMissing = !session('2fa:user:id');
        return view('auth.verify-otp', ['sessionMissing' => $sessionMissing]);
    }

    /**
     * Handle the OTP verification process.
     */
    public function verify(Request $request)
    {
        // Validation: 6 digits, required, numeric. If session is missing, require an email fallback.
        $rules = [
            'otp' => ['required', 'numeric', 'digits:6'],
        ];

        $userId = session('2fa:user:id');
        $sessionMissing = !$userId;
        if ($sessionMissing) {
            // When the temporary 2FA session is gone, allow the user to provide their
            // email along with the OTP so they can paste a code received earlier.
            $rules['email'] = ['required', 'email'];
        }

        $request->validate($rules);

        // Resolve the user either from the 2FA session or from the provided email.
        if ($userId) {
            $user = User::find($userId);
        } else {
            $user = User::where('email', $request->input('email'))->first();
        }
        if (!$user) {
            if ($sessionMissing) {
                return back()->withErrors(['email' => 'No account found for that email. Please check and try again, or go back to the login page.']);
            }
            session()->forget('2fa:user:id');
            return redirect()->route('index');
        }


        // Hard stop regardless of what code is entered, once too many wrong
        // guesses have been made against the current OTP.
        if ($user->otp_locked_until && Carbon::now()->isBefore($user->otp_locked_until)) {
            $minutesLeft = max(1, (int) ceil(Carbon::now()->diffInSeconds($user->otp_locked_until) / 60));
            return back()->withErrors([
                'otp' => "Too many incorrect attempts. Please try again in about {$minutesLeft} minute(s), or use \"Resend code\" for a fresh one.",
            ]);
        }

        // **CORE VERIFICATION LOGIC**
        // 1. Check if the provided OTP matches the stored OTP and is not expired.
        // hash_equals() avoids leaking timing information that could help an
        // attacker narrow down the correct digits one at a time.
        $otpMatches = $user->otp_code !== null
            && hash_equals((string) $user->otp_code, (string) $request->otp);

        if ($otpMatches && Carbon::now()->isBefore($user->otp_expires_at)) {
            // SUCCESS: OTP is valid and not expired

            // --- START: Logic for updating last login, IP, and session ID ---

            // NOTE: Removed session regeneration to prevent 419 Page Expired right after login
            // (regenerating session here can invalidate CSRF/session state for the next request/redirect)

            $user->update([
                'last_login_at' => now(),           // Update Last Login Time
                'last_login_ip' => $request->ip(),   // Update IP Address
                'session_id' => $request->session()->getId(), // Update Session ID
            ]);

            // Clear OTP fields and attempt/lock state in the database
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->otp_attempts = 0;
            $user->otp_locked_until = null;
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
            
            // Role-based landing page. This used to be an if-chain that covered
            // super_admin, admin and attendance_checker and then fell through to
            // redirect('/') — which meant an employee with a CORRECT code was
            // logged in and dumped back on the public landing page. See RoleHome.
            return redirect()
                ->route(RoleHome::routeFor($user->role))
                ->with('success', RoleHome::messageFor($user->role));
        }

        // Failure: OTP is incorrect or expired. Track the attempt and lock out
        // after too many consecutive wrong guesses against this code.
        $user->otp_attempts = ($user->otp_attempts ?? 0) + 1;

        $maxAttempts = 5;

        if ($user->otp_attempts >= $maxAttempts) {
            $user->otp_locked_until = Carbon::now()->addMinutes(15);
            $user->otp_attempts = 0;
            $user->save();

            return back()->withErrors([
                'otp' => 'Too many incorrect attempts. Please try again in 15 minutes, or use "Resend code" for a fresh one.',
            ]);
        }

        $user->save();

        $remaining = $maxAttempts - $user->otp_attempts;
        return back()->withErrors(['otp' => "The verification code is invalid or has expired. {$remaining} attempt(s) remaining before a temporary lock."]);
    }
    
    /**
     * Handle the resend OTP request.
     */
    public function resendOtp(Request $request)
    {
        if (!Schema::hasColumns('users', ['otp_code', 'otp_expires_at', 'otp_attempts', 'otp_locked_until'])) {
            return redirect()->route('index')->with('warning', 'OTP verification is unavailable. Please log in again.');
        }
 
        $userId = session('2fa:user:id');
 
        // Check if user is being tracked in session
        if (!$userId) {
            return redirect()->route('index')->with('error', 'Session expired. Please try to log in again.');
        }

        $user = User::find($userId);

        if (!$user) {
            session()->forget('2fa:user:id');
            return redirect()->route('index')->with('error', 'User not found.');
        }

        // 1. Generate NEW OTP
        $otp = random_int(100000, 999999);

        // 2. Store the NEW OTP, NEW Expiration Time (5 minutes from now), and
        //    reset attempt/lock state — a fresh code deserves a fresh budget.
        $user->otp_code = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(5);
        $user->otp_attempts = 0;
        $user->otp_locked_until = null;
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