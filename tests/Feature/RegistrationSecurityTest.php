<?php

use App\Mail\OtpMail;
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
    ]))
        ->assertRedirect(route('employee.login.form'))
        ->assertSessionHas('success');

    $user = User::where('email', 'maria.santos@example.com')->firstOrFail();
    expect($user->name)->toBe('Maria Santos')
        ->and($user->role)->toBe('employee')
        ->and($user->employee_id)->toBe($employee->id)
        ->and($user->status)->toBe('pending')
        // Inbox ownership is proven by the emailed sign-in code, not by a link.
        ->and($user->email_verified_at)->toBeNull();

    Mail::assertNotSent(UserVerificationEmail::class);
});

it('does not create an account for an email outside the employee roster', function () {
    $this->post(route('register.store'), registrationPayload())
        ->assertRedirect(route('register.form'))
        ->assertSessionHas('error');

    expect(User::where('email', 'maria.santos@example.com')->exists())->toBeFalse();
});

it('still activates an employee from a valid unexpired legacy verification link', function () {
    $employee = Employee::create([
        'name' => 'Maria Santos',
        'email' => 'maria.santos@example.com',
        'position' => 'Staff',
    ]);

    $user = User::create([
        'name' => $employee->name,
        'email' => $employee->email,
        'password' => bcrypt('SafePassword123!'),
        'role' => 'employee',
        'employee_id' => $employee->id,
        'status' => 'pending',
    ]);
    $user->forceFill([
        'verification_token' => hash('sha256', 'legacy-token'),
        'verification_expires_at' => now()->addHour(),
    ])->save();

    $this->get(route('user.verify', 'legacy-token'))
        ->assertRedirect(route('employee.login.form'));

    $user->refresh();
    expect($user->email_verified_at)->not->toBeNull()
        ->and($user->status)->toBe('active')
        ->and($user->verification_token)->toBeNull()
        ->and($user->verification_expires_at)->toBeNull();
});

function pendingEmployee(string $status = 'pending'): User
{
    $employee = Employee::create([
        'name' => 'Maria Santos',
        'email' => 'maria.santos@example.com',
        'position' => 'Staff',
    ]);

    return User::create([
        'name' => $employee->name,
        'email' => $employee->email,
        'password' => bcrypt('SafePassword123!'),
        'role' => 'employee',
        'employee_id' => $employee->id,
        'status' => $status,
    ]);
}

it('sends a freshly registered employee on to the emailed sign-in code', function () {
    pendingEmployee();

    // Mixed case and stray spaces must still find the account.
    $this->post(route('employee.login'), [
        'email' => ' Maria.Santos@Example.com ',
        'password' => 'SafePassword123!',
        'user_type' => 'employee',
    ])->assertRedirect(route('otp.verify.form'));

    Mail::assertSent(OtpMail::class);
});

it('activates the account and opens the employee portal once the code is entered', function () {
    $user = pendingEmployee();
    $user->forceFill([
        'otp_code' => '123456',
        'otp_expires_at' => now()->addMinutes(5),
        'otp_attempts' => 0,
    ])->save();

    $this->withSession(['2fa:user:id' => $user->id])
        ->post(route('otp.verify'), ['otp' => '123456'])
        ->assertRedirect(route('employee.dashboard'));

    $user->refresh();
    expect($user->status)->toBe('active')
        ->and($user->email_verified_at)->not->toBeNull();
    $this->assertAuthenticatedAs($user);
});

it('does not let a suspended employee begin the login flow', function () {
    pendingEmployee('suspended');

    $this->post(route('employee.login'), [
        'email' => 'maria.santos@example.com',
        'password' => 'SafePassword123!',
        'user_type' => 'employee',
    ])->assertSessionHas('error', 'This account is not active. Please contact the administrator.');

    Mail::assertNotSent(OtpMail::class);
});

it('does not issue a mobile api token to an unverified employee', function () {
    pendingEmployee();

    $this->postJson('/api/mobile/login', [
        'email' => 'maria.santos@example.com',
        'password' => 'SafePassword123!',
    ])->assertStatus(403)->assertJsonMissingPath('token');
});