<?php

use App\Mail\UserVerificationEmail;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('services.recaptcha.site_key', null);
    config()->set('services.recaptcha.secret_key', null);
    Http::fake(['https://api.pwnedpasswords.com/*' => Http::response('', 200)]);
    Mail::fake();
});

function registrationPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Maria Santos',
        'email' => 'maria.santos@example.com',
        'password' => 'SafePassword123!',
        'password_confirmation' => 'SafePassword123!',
        'terms' => '1',
    ], $overrides);
}

it('only creates a pending employee account for an email in the roster', function () {
    $employee = Employee::create([
        'name' => 'Maria Santos',
        'email' => 'Maria.Santos@Example.com ',
        'position' => 'Staff',
    ]);

    $this->post(route('register.store'), registrationPayload([
        // A forged public form value must never grant an attendance role.
        'role' => 'attendance_checker',
        'course' => 'bsit',
    ]))->assertRedirect(route('employee.login.form'));

    $user = User::where('email', 'maria.santos@example.com')->firstOrFail();
    expect($user->name)->toBe('Maria Santos')
        ->and($user->role)->toBe('employee')
        ->and($user->employee_id)->toBe($employee->id)
        ->and($user->status)->toBe('pending')
        ->and($user->email_verified_at)->toBeNull()
        ->and($user->verification_token)->toMatch('/^[a-f0-9]{64}$/')
        ->and($user->verification_expires_at)->not->toBeNull();
});

it('does not create an account for an email outside the employee roster', function () {
    $this->post(route('register.store'), registrationPayload())
        ->assertRedirect(route('employee.login.form'));

    expect(User::where('email', 'maria.santos@example.com')->exists())->toBeFalse();
});

it('activates an employee only when a valid unexpired verification link is used', function () {
    Employee::create([
        'name' => 'Maria Santos',
        'email' => 'maria.santos@example.com',
        'position' => 'Staff',
    ]);

    $this->post(route('register.store'), registrationPayload());

    $token = null;
    Mail::assertSent(UserVerificationEmail::class, function (UserVerificationEmail $mail) use (&$token) {
        $token = $mail->token;

        return true;
    });

    $this->get(route('user.verify', $token))
        ->assertRedirect(route('employee.login.form'));

    $user = User::where('email', 'maria.santos@example.com')->firstOrFail();
    expect($user->email_verified_at)->not->toBeNull()
        ->and($user->status)->toBe('active')
        ->and($user->verification_token)->toBeNull()
        ->and($user->verification_expires_at)->toBeNull();
});

it('does not let a pending employee begin the login flow', function () {
    $employee = Employee::create([
        'name' => 'Maria Santos',
        'email' => 'maria.santos@example.com',
        'position' => 'Staff',
    ]);

    User::create([
        'name' => $employee->name,
        'email' => $employee->email,
        'password' => bcrypt('SafePassword123!'),
        'role' => 'employee',
        'employee_id' => $employee->id,
        'status' => 'pending',
    ]);

    $this->post(route('employee.login'), [
        'email' => $employee->email,
        'password' => 'SafePassword123!',
        'user_type' => 'employee',
    ])->assertSessionHas('error', 'Please verify your email address before signing in.');
});
