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
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    private const VERIFICATION_LIFETIME_MINUTES = 60;

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
            'password' => ['required', 'string', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised()],
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

        // Use one response for an existing, ineligible, or newly created account
        // so the public endpoint does not reveal whether a given email is on the
        // employee roster or already has an account.
        if (!$employee) {
            Log::notice('Registration attempted with an email outside the employee roster', ['ip' => $request->ip()]);

            return $this->registrationResponse();
        }

        $existingUser = User::whereRaw('LOWER(TRIM(email)) = ?', [$email])->first();
        if ($existingUser) {
            if ($existingUser->role === 'employee' && !$existingUser->email_verified_at) {
                $this->sendVerificationLink($existingUser);
            }

            return $this->registrationResponse();
        }

        try {
            $user = User::create([
                // The roster, not arbitrary form input, establishes the account's identity.
                'name' => $employee->name,
                'email' => $email,
                'password' => Hash::make($data['password']),
                'role' => 'employee',
                'employee_id' => $employee->id,
                'status' => 'pending',
            ]);
        } catch (QueryException $e) {
            // A concurrent request can win the database unique constraint. Give
            // the same response rather than leaking account existence.
            Log::notice('Concurrent registration request rejected', ['ip' => $request->ip()]);

            return $this->registrationResponse();
        }

        $this->sendVerificationLink($user);

        return $this->registrationResponse();
    }

    /** Send a fresh, single-use verification secret and retain only its hash. */
    public function sendVerificationLink(User $user): void
    {
        $token = Str::random(64);

        $user->forceFill([
            'verification_token' => hash('sha256', $token),
            'verification_expires_at' => now()->addMinutes(self::VERIFICATION_LIFETIME_MINUTES),
        ])->save();

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

    private function registrationResponse()
    {
        return redirect()->route('employee.login.form')->with(
            'success',
            'If the email address is eligible, a verification link has been sent. Verify your account before signing in.'
        );
    }
}
