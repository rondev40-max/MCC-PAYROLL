<?php

namespace App\Http\Controllers;

use App\Mail\UserVerificationEmail;
use App\Models\Employee;
use App\Models\User;
use App\Rules\ReCaptcha;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    private const VERIFICATION_LIFETIME_MINUTES = 60;

    /**
     * A password seen in known data leaks more than this many times is rejected.
     * The default (0) rejects a password found even once, which turns away most
     * passwords people actually pick ("Maria2024!" style) and makes registration
     * feel broken. 100 still blocks the genuinely common ones (Password123!,
     * Welcome@2024, ...) while accepting a password that is merely uncommon.
     */
    private const MAX_BREACH_COUNT = 100;

    /**
     * Public registration is deliberately limited to a person already present
     * in the employee roster. Privileged and attendance-checker accounts are
     * provisioned by an administrator, never chosen by a public form.
     */
    public function store(Request $request)
    {
        if ($request->filled('company_url')) {
            Log::warning('Registration honeypot triggered', ['ip' => $request->ip()]);

            return $this->registrationResponse();
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'password' => ['required', 'string', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised(self::MAX_BREACH_COUNT)],
            'terms' => ['accepted'],
        ];

        if (ReCaptcha::isConfigured()) {
            $rules['g-recaptcha-response'] = [
                'required',
                new ReCaptcha(expectedAction: 'register', remoteIp: $request->ip(), failOpen: false),
            ];
        }

        $data = $request->validate($rules, [
            'terms.accepted' => 'You must agree to the Terms and Conditions to create an account.',
            'g-recaptcha-response.required' => 'Please complete the verification check and try again.',
        ]);

        $email = strtolower(trim($data['email']));
        $employee = Employee::whereRaw('LOWER(TRIM(email)) = ?', [$email])->first();

        // An email outside the roster and an email that already has an account
        // get the same message, so the form does not say which of the two it was.
        if (!$employee) {
            Log::notice('Registration attempted with an email outside the employee roster', ['ip' => $request->ip()]);

            return $this->notCreatedResponse();
        }

        $existingUser = User::whereRaw('LOWER(TRIM(email)) = ?', [$email])->first();
        if ($existingUser) {
            // Only the legacy link flow needs a new link. In the normal flow the
            // person simply signs in and confirms the emailed one-time code.
            if (!$this->otpReady() && $existingUser->role === 'employee' && !$existingUser->email_verified_at) {
                $this->sendVerificationLink($existingUser);
            }

            return $this->notCreatedResponse();
        }

        try {
            $user = User::create([
                // The roster, not arbitrary form input, establishes the account's identity.
                'name' => $employee->name,
                'email' => $email,
                'password' => Hash::make($data['password']),
                'role' => 'employee',
                'employee_id' => $employee->id,
                // Stays 'pending' until the first successful sign-in. That sign-in
                // requires the one-time code emailed to this address, which is what
                // proves the person owns the inbox (see OtpVerificationController).
                'status' => 'pending',
            ]);
        } catch (QueryException $e) {
            // A concurrent request can win the database unique constraint.
            Log::notice('Concurrent registration request rejected', ['ip' => $request->ip()]);

            return $this->notCreatedResponse();
        }

        // Without the OTP columns the sign-in has no second factor, so fall back to
        // the emailed verification link rather than activating on a password alone.
        if (!$this->otpReady()) {
            $this->sendVerificationLink($user);

            return $this->registrationResponse();
        }

        return redirect()->route('employee.login.form')
            ->withInput(['email' => $email])
            ->with('success', 'Account created! Sign in with your email and password. We will email you a 6-digit code to finish signing in, and then you will be taken to your employee portal.');
    }

    /** Send a fresh, single-use verification secret and retain only its hash. */
    public function sendVerificationLink(User $user): void
    {
        $token = Str::random(64);

        $fields = ['verification_token' => hash('sha256', $token)];

        // Tolerate a database that has not run the expiry migration yet, instead of
        // failing registration with a 500 after the account row was already created.
        if (Schema::hasColumn('users', 'verification_expires_at')) {
            $fields['verification_expires_at'] = now()->addMinutes(self::VERIFICATION_LIFETIME_MINUTES);
        }

        $user->forceFill($fields)->save();

        try {
            Mail::to($user->email)->send(new UserVerificationEmail($user, $token));
        } catch (\Throwable $e) {
            // Do not disclose delivery details to callers. The user can request
            // another link, while administrators retain enough detail to fix mail.
            Log::error('Registration verification email could not be sent', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /** Public resend endpoint with a deliberately non-enumerating response. */
    public function resend(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'string', 'email:rfc', 'max:255']]);
        $email = strtolower(trim($data['email']));
        $user = User::whereRaw('LOWER(TRIM(email)) = ?', [$email])->first();

        if ($user && $user->role === 'employee' && !$user->email_verified_at) {
            $this->sendVerificationLink($user);
        }

        return $this->registrationResponse();
    }

    private function otpReady(): bool
    {
        return Schema::hasColumns('users', ['otp_code', 'otp_expires_at', 'otp_attempts', 'otp_locked_until']);
    }

    /**
     * Shown when no new account was made (email not on the roster, or already
     * registered). Both cases share one message so it does not say which it was.
     */
    private function notCreatedResponse()
    {
        // Send the person back to the form they just filled in (minus passwords) so
        // they can correct the email straight away instead of landing on the login page.
        return redirect()->route('register.form')
            ->withInput(request()->only('name', 'email'))
            ->with(
                'error',
                'We could not create a new account with that email. Use the exact email address the HR office has on file for you. If you already registered, sign in instead, or contact HR.'
            );
    }

    private function registrationResponse()
    {
        return redirect()->route('employee.login.form')->with(
            'success',
            'If the email address is eligible, a verification link has been sent. Verify your account before signing in.'
        );
    }
}