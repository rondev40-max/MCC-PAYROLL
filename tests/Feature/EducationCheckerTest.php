<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/**
 * The Education attendance checker is seeded with course 'bsed', but every
 * timesheet stores its department as 'EDUCATION' — the column is an enum of
 * ('BSIT','BSBA','BSHM','EDUCATION') and has no BSED member at all.
 *
 * So that checker signs in successfully, is authorised for BSED, and then gets
 * an empty register forever while every other department works.
 */
test('the education checker can see education personnel', function () {
    DB::table('fulltime_timesheets')->insert([
        'employee_name' => 'Education Instructor',
        'designation' => 'Instructor',
        'department' => 'EDUCATION',
        'email' => 'edu@mcc.edu.ph',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $session = [
        'user_id' => 94,
        'user_name' => 'BSED Attendance Checker',
        'user_role' => 'attendance_checker',
        'user_course' => 'bsed',
        'is_attendance' => true,
    ];

    $response = $this->withSession($session)
        ->get('/attendance/api/attendance-data/bsed?cutoff_start=2026-08-01');

    $response->assertOk();

    expect($response->json())
        ->not->toBeEmpty('the BSED checker should see the EDUCATION roster');
});

test('a BSIT checker is unaffected', function () {
    DB::table('fulltime_timesheets')->insert([
        'employee_name' => 'IT Instructor',
        'designation' => 'Instructor',
        'department' => 'BSIT',
        'email' => 'it@mcc.edu.ph',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $response = $this->withSession([
        'user_id' => 91, 'user_name' => 'C', 'user_role' => 'attendance_checker',
        'user_course' => 'bsit', 'is_attendance' => true,
    ])->get('/attendance/api/attendance-data/bsit?cutoff_start=2026-08-01');

    $response->assertOk();
    expect($response->json())->not->toBeEmpty();
});

test('aliasing does not let one department read another', function () {
    // BSED and EDUCATION are the same department; BSIT is not.
    $bsed = ['user_id' => 94, 'user_name' => 'C', 'user_role' => 'attendance_checker',
             'user_course' => 'bsed', 'is_attendance' => true];

    $this->withSession($bsed)
        ->get('/attendance/api/attendance-data/education?cutoff_start=2026-08-01')
        ->assertOk();

    $this->withSession($bsed)
        ->get('/attendance/api/attendance-data/bsit?cutoff_start=2026-08-01')
        ->assertStatus(403);

    $this->withSession($bsed)
        ->get('/attendance/api/attendance-data/bsba?cutoff_start=2026-08-01')
        ->assertStatus(403);
});

test('an unknown department is refused rather than matching everything', function () {
    $this->withSession([
        'user_id' => 99, 'user_name' => 'C', 'user_role' => 'attendance_checker',
        'user_course' => 'nonsense', 'is_attendance' => true,
    ])->get('/attendance/api/attendance-data/bsit?cutoff_start=2026-08-01')
      ->assertStatus(403);
});
