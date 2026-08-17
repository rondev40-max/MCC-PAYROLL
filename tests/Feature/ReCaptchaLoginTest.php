<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->checker = User::create([
        'name' => 'BSIT Checker',
        'email' => 'bsit@mcclawis.edu.ph',
        'password' => Hash::make('password123'),
        'role' => 'attendance_checker',
        'course' => 'bsit',
    ]);
});

function attendanceLogin(array $overrides = []): array
{
    return array_merge([
        'email' => 'bsit@mcclawis.edu.ph',
        'password' => 'password123',
        'user_type' => 'attendance',
    ], $overrides);
}

/*
 * The dangerous half of this feature is not the bot blocking — it is locking
 * every employee out of payroll. These first two tests pin that down.
 */

test('sign-in works normally when reCAPTCHA is not configured', function () {
    config(['services.recaptcha.site_key' => null, 'services.recaptcha.secret_key' => null]);
    Http::fake(); // Nothing should reach Google at all.

    $response = $this->post('/attendance/attendlog', attendanceLogin());

    $response->assertRedirect(route('attendance.dashboard'));
    expect(session('user_role'))->toBe('attendance_checker');
    Http::assertNothingSent();
});

test('a half-configured deployment does not start rejecting sign-ins', function () {
    // Site key present, secret missing — a very easy deploy mistake.
    config(['services.recaptcha.site_key' => 'site', 'services.recaptcha.secret_key' => null]);
    Http::fake();

    $response = $this->post('/attendance/attendlog', attendanceLogin());

    $response->assertRedirect(route('attendance.dashboard'));
    Http::assertNothingSent();
});

test('a valid token is accepted once reCAPTCHA is configured', function () {
    config(['services.recaptcha.site_key' => 'site', 'services.recaptcha.secret_key' => 'secret']);

    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true, 'score' => 0.9, 'action' => 'login',
        ]),
    ]);

    $response = $this->post('/attendance/attendlog', attendanceLogin([
        'g-recaptcha-response' => 'valid-token',
    ]));

    $response->assertRedirect(route('attendance.dashboard'));
    expect(session('user_role'))->toBe('attendance_checker');
});

test('a missing token is rejected when reCAPTCHA is configured', function () {
    config(['services.recaptcha.site_key' => 'site', 'services.recaptcha.secret_key' => 'secret']);
    Http::fake();

    $response = $this->post('/attendance/attendlog', attendanceLogin());

    $response->assertSessionHasErrors('g-recaptcha-response');
    expect(session('user_role'))->toBeNull();
});

test('a token Google rejects is refused', function () {
    config(['services.recaptcha.site_key' => 'site', 'services.recaptcha.secret_key' => 'secret']);

    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => false, 'error-codes' => ['invalid-input-response'],
        ]),
    ]);

    $response = $this->post('/attendance/attendlog', attendanceLogin([
        'g-recaptcha-response' => 'forged',
    ]));

    $response->assertSessionHasErrors('g-recaptcha-response');
    expect(session('user_role'))->toBeNull();
});

test('a low score is treated as automated', function () {
    config([
        'services.recaptcha.site_key' => 'site',
        'services.recaptcha.secret_key' => 'secret',
        'services.recaptcha.min_score' => 0.5,
    ]);

    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true, 'score' => 0.1, 'action' => 'login',
        ]),
    ]);

    $response = $this->post('/attendance/attendlog', attendanceLogin([
        'g-recaptcha-response' => 'bot-token',
    ]));

    $response->assertSessionHasErrors('g-recaptcha-response');
});

test('a token minted for a different action is refused', function () {
    config(['services.recaptcha.site_key' => 'site', 'services.recaptcha.secret_key' => 'secret']);

    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true, 'score' => 0.9, 'action' => 'contact_form',
        ]),
    ]);

    $response = $this->post('/attendance/attendlog', attendanceLogin([
        'g-recaptcha-response' => 'replayed-token',
    ]));

    $response->assertSessionHasErrors('g-recaptcha-response');
});

test('a Google outage does not lock everyone out by default', function () {
    config(['services.recaptcha.site_key' => 'site', 'services.recaptcha.secret_key' => 'secret']);

    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => Http::response('gateway timeout', 504),
    ]);

    $response = $this->post('/attendance/attendlog', attendanceLogin([
        'g-recaptcha-response' => 'any-token',
    ]));

    $response->assertRedirect(route('attendance.dashboard'));
});

test('fail_open false makes an outage reject instead', function () {
    config([
        'services.recaptcha.site_key' => 'site',
        'services.recaptcha.secret_key' => 'secret',
        'services.recaptcha.fail_open' => false,
    ]);

    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => Http::response('gateway timeout', 504),
    ]);

    $response = $this->post('/attendance/attendlog', attendanceLogin([
        'g-recaptcha-response' => 'any-token',
    ]));

    $response->assertSessionHasErrors('g-recaptcha-response');
});
