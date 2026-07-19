<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test users
    $this->adminUser = User::create([
        'name' => 'Admin User',
        'email' => 'admin@test.com',
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

test('admin can login with correct credentials', function () {
    $response = $this->post('/admin/login', [
        'email' => 'admin@test.com',
        'password' => 'password123',
        'user_type' => 'admin'
    ]);

    $response->assertRedirect('/dashboard');
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
        'email' => 'admin@test.com',
        'password' => 'password123',
        'user_type' => 'attendance'
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('attendance checker cannot login with wrong role', function () {
    $response = $this->post('/admin/login', [
        'email' => 'attendance@test.com',
        'password' => 'password123',
        'user_type' => 'admin'
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('login form displays role selection', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('Select Role');
    $response->assertSee('Admin');
    $response->assertSee('Attendance Checker');
});