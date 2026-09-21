<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('unauthenticated guests cannot access department attendance views', function (string $department) {
    $response = $this->get("/{$department}");
    $response->assertRedirect('/');
})->with(['bsit', 'bsba', 'bshm', 'education']);

test('an administrator can access department attendance views', function () {
    $admin = User::create([
        'name'     => 'Admin Test',
        'email'    => 'admin@mcclawis.edu.ph',
        'password' => Hash::make('password123'),
        'role'     => 'admin',
    ]);

    $response = $this->actingAs($admin)->get('/bsit');
    $response->assertOk();
    $response->assertViewIs('bsit.index');
});

test('signing in as an attendance checker regenerates the session', function () {
    $user = User::create([
        'name'     => 'BSIT Checker',
        'email'    => 'bsit_test@mcclawis.edu.ph',
        'password' => Hash::make('secret123'),
        'role'     => 'attendance_checker',
        'course'   => 'bsit',
    ]);

    $initialSessionId = session()->getId();

    $response = $this->post('/attendance/attendlog', [
        'email'     => 'bsit_test@mcclawis.edu.ph',
        'password'  => 'secret123',
        'user_type' => 'attendance',
    ]);

    $response->assertRedirect(route('attendance.dashboard'));
    expect(session()->getId())->not->toBe($initialSessionId);
    expect(session('user_role'))->toBe('attendance_checker');
    expect(session('is_attendance'))->toBeTrue();
});
