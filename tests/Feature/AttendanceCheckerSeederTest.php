<?php

use App\Models\User;
use Database\Seeders\AttendanceCheckerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

const CHECKER_PASSWORD = 'bsit@12345';

test('the seeder creates the four checkers with a hashed password', function () {
    $this->seed(AttendanceCheckerSeeder::class);

    $checkers = User::where('role', 'attendance_checker')->get();
    expect($checkers)->toHaveCount(4);

    foreach ($checkers as $checker) {
        // Never stored in readable form.
        expect($checker->password)->not->toBe(CHECKER_PASSWORD);
        expect(Hash::isHashed($checker->password))->toBeTrue();

        // Hashed exactly once — a double hash would fail this.
        expect(Hash::check(CHECKER_PASSWORD, $checker->password))->toBeTrue();
    }
});

test('running it twice does not duplicate accounts', function () {
    $this->seed(AttendanceCheckerSeeder::class);
    $this->seed(AttendanceCheckerSeeder::class);
    $this->seed(AttendanceCheckerSeeder::class);

    expect(User::where('role', 'attendance_checker')->count())->toBe(4);
    expect(User::where('email', 'bsit@gmail.com')->count())->toBe(1);
});

test('an existing checker has its password reset rather than being duplicated', function () {
    $existing = User::create([
        'name' => 'BSIT Attendance Checker',
        'email' => 'bsit@gmail.com',
        'password' => Hash::make('some-old-password'),
        'role' => 'attendance_checker',
        'course' => 'bsit',
    ]);

    $this->seed(AttendanceCheckerSeeder::class);

    $existing->refresh();

    expect(User::where('email', 'bsit@gmail.com')->count())->toBe(1)
        ->and($existing->id)->toBe($existing->id)
        ->and(Hash::check(CHECKER_PASSWORD, $existing->password))->toBeTrue()
        ->and(Hash::check('some-old-password', $existing->password))->toBeFalse();
});

test('checkers keep the attendance_checker role and never become admins', function () {
    $this->seed(AttendanceCheckerSeeder::class);

    foreach (['bsit', 'bsba', 'bshm', 'bsed'] as $course) {
        $user = User::where('email', "{$course}@gmail.com")->first();

        expect($user->role)->toBe('attendance_checker')
            ->and($user->course)->toBe($course);
    }

    expect(User::whereIn('role', ['admin', 'super_admin'])->count())->toBe(0);
});

test('other accounts are left completely untouched', function () {
    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@mcclawis.edu.ph',
        'password' => Hash::make('admin-secret'),
        'role' => 'admin',
    ]);
    $employee = User::create([
        'name' => 'Employee',
        'email' => 'employee@example.com',
        'password' => Hash::make('employee-secret'),
        'role' => 'employee',
    ]);

    $this->seed(AttendanceCheckerSeeder::class);

    $admin->refresh();
    $employee->refresh();

    expect($admin->role)->toBe('admin')
        ->and(Hash::check('admin-secret', $admin->password))->toBeTrue()
        ->and($employee->role)->toBe('employee')
        ->and(Hash::check('employee-secret', $employee->password))->toBeTrue();
});

test('a seeded checker can actually sign in through the real login route', function () {
    $this->seed(AttendanceCheckerSeeder::class);

    $response = $this->post('/attendance/attendlog', [
        'email' => 'bsit@gmail.com',
        'password' => CHECKER_PASSWORD,
        'user_type' => 'attendance',
    ]);

    $response->assertRedirect(route('attendance.dashboard'));
    expect(session('user_role'))->toBe('attendance_checker')
        ->and(session('user_course'))->toBe('bsit');
});
