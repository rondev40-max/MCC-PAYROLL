<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test users
    // The admin portal only accepts @mcc.edu.ph / @mcclawis.edu.ph addresses
    // (LoginController rejects anything else before checking the password), so
    // an admin fixture on @test.com can never sign in.
    $this->adminUser = User::create([
        'name' => 'Admin User',
        'email' => 'admin@mcclawis.edu.ph',
        'password' => Hash::make('password123'),
        'role' => 'admin'
    ]);

    $this->attendanceUser = User::create([
        'name' => 'Attendance Checker',
        'email' => 'attendance@test.com',
        'password' => Hash::make('password123'),
        'role' => 'attendance_checker'
    ]);
});

// The admin portal posts to /portal/management-login, not /admin/login — the
// old URL 404'd, which is why this file's two admin tests failed.
//
// Admin sign-in is also two steps: correct credentials do NOT log you in, they
// issue an OTP challenge. Auth::login() and the role session keys only happen
// once the code is verified, so the two halves are asserted separately below.
test('correct admin credentials issue an OTP challenge rather than a session', function () {
    $response = $this->post('/portal/management-login', [
        'email' => 'admin@mcclawis.edu.ph',
        'password' => 'password123',
        'user_type' => 'admin'
    ]);

    $response->assertRedirect(route('admin.login.form'));
    $response->assertSessionHas('show_otp_modal', true);
    $response->assertSessionHas('2fa:user:id', $this->adminUser->id);

    // Not authenticated yet — that is the whole point of the second factor.
    expect(session('user_role'))->toBeNull();
    expect(auth()->check())->toBeFalse();

    $this->adminUser->refresh();
    expect($this->adminUser->otp_code)->not->toBeNull();
});

test('verifying the OTP logs the admin in and lands on the admin dashboard', function () {
    $this->post('/portal/management-login', [
        'email' => 'admin@mcclawis.edu.ph',
        'password' => 'password123',
        'user_type' => 'admin'
    ]);

    $response = $this->post('/otp/verify', [
        'otp' => $this->adminUser->fresh()->otp_code,
    ]);

    $response->assertRedirect(route('admin.dashboard'));
    $response->assertSessionHas('success');
    expect(session('user_role'))->toBe('admin');
    expect(session('is_admin'))->toBe(true);
});

test('attendance checker can login with correct credentials', function () {
    $response = $this->post('/attendance/attendlog', [
        'email' => 'attendance@test.com',
        'password' => 'password123',
        'user_type' => 'attendance'
    ]);

    $response->assertRedirect('/attendance/dashboard');
    $response->assertSessionHas('success');
    expect(session('user_role'))->toBe('attendance_checker');
    expect(session('is_attendance'))->toBe(true);
});

test('admin cannot login with wrong role', function () {
    $response = $this->post('/attendance/attendlog', [
        'email' => 'admin@mcclawis.edu.ph',
        'password' => 'password123',
        'user_type' => 'attendance'
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('attendance checker cannot login with wrong role', function () {
    $response = $this->post('/portal/management-login', [
        'email' => 'attendance@test.com',
        'password' => 'password123',
        'user_type' => 'admin'
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('portal landing page displays main portals', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('Madridejos Community College');
    $response->assertSee('Employee Portal');
    $response->assertSee('Attendance Log');
});