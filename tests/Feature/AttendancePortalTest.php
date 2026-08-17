<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('attendance_histories');
    Schema::dropIfExists('attendances');

    Schema::create('attendances', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('employee_id');
        $table->unsignedBigInteger('user_id')->nullable();
        $table->date('date');
        $table->time('time_in')->nullable();
        $table->time('time_out')->nullable();
        $table->time('am_in_time')->nullable();
        $table->time('am_out_time')->nullable();
        $table->time('pm_in_time')->nullable();
        $table->time('pm_out_time')->nullable();
        $table->decimal('hours_rendered', 5, 2)->default(0);
        $table->unsignedInteger('lateness_minutes')->default(0);
        $table->unsignedInteger('undertime_minutes')->default(0);
        $table->unsignedInteger('overtime_minutes')->default(0);
        $table->decimal('total_hours', 5, 2)->default(0);
        $table->string('status')->default('present');
        $table->text('remarks')->nullable();
        $table->string('course', 50);
        $table->string('employee_name')->nullable();
        $table->string('employee_type', 50)->nullable();
        $table->timestamps();
    });

    Schema::create('attendance_histories', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('employee_id')->nullable();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('employee_name');
        $table->string('email')->nullable();
        $table->string('employee_type', 50);
        $table->string('designation')->nullable();
        $table->string('department', 100);
        $table->date('attendance_date');
        $table->boolean('is_present')->default(false);
        $table->decimal('hours_worked', 5, 2)->default(0);
        $table->time('time_in')->nullable();
        $table->time('time_out')->nullable();
        $table->string('status', 50)->default('present');
        $table->text('remarks')->nullable();
        $table->string('location')->nullable();
        $table->softDeletes();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('attendance_histories');
    Schema::dropIfExists('attendances');
});

function attendancePortalSession(array $overrides = []): array
{
    return array_merge([
        'user_id' => 91,
        'user_name' => 'BSIT Attendance Checker',
        'user_role' => 'attendance_checker',
        'user_course' => 'BSIT',
        'is_attendance' => true,
    ], $overrides);
}

function insertPortalAttendance(array $overrides = []): void
{
    DB::table('attendances')->insert(array_merge([
        'employee_id' => 12,
        'user_id' => 91,
        'date' => '2026-08-03',
        'time_in' => '08:00',
        'time_out' => '17:00',
        'am_in_time' => '08:00',
        'am_out_time' => '12:00',
        'pm_in_time' => '13:00',
        'pm_out_time' => '17:00',
        'hours_rendered' => 8,
        'lateness_minutes' => 0,
        'undertime_minutes' => 0,
        'overtime_minutes' => 0,
        'total_hours' => 8,
        'status' => 'present',
        'remarks' => null,
        'course' => 'BSIT',
        'employee_name' => 'Alice Example',
        'employee_type' => 'Fulltime',
        'created_at' => now(),
        'updated_at' => now(),
    ], $overrides));
}

function attendanceSavePayload(array $attendance, array $overrides = []): array
{
    return array_merge([
        'course' => 'BSIT',
        'cutoff_start' => '2026-08-01',
        'attendance_data' => [[
            'id' => 'FT-12',
            'employee_name' => 'Alice Example',
            'name' => 'Alice Example',
            'email' => 'alice@example.test',
            'designation' => 'Instructor I',
            'employee_type' => 'Fulltime',
            'type' => 'Fulltime',
            'attendance' => $attendance,
        ]],
    ], $overrides);
}

test('authenticated checker can render the DTR index editor and print form', function () {
    insertPortalAttendance();

    $index = $this
        ->withSession(attendancePortalSession())
        ->get('/attendance/dtr?course=BSIT&month=2026-08');

    $index
        ->assertOk()
        ->assertViewIs('attendance.dtr-index')
        ->assertSee('Daily Time Records')
        ->assertSee('Alice Example');

    $show = $this
        ->withSession(attendancePortalSession())
        ->get('/attendance/dtr/BSIT/12?month=2026-08&type=Fulltime');

    $show
        ->assertOk()
        ->assertViewIs('attendance.dtr')
        ->assertSee('Civil Service Form No. 48')
        ->assertSee('Alice Example');

    $print = $this
        ->withSession(attendancePortalSession())
        ->get('/attendance/dtr/BSIT/12/print?month=2026-08&type=Fulltime');

    $print
        ->assertOk()
        ->assertViewIs('attendance.dtr-print')
        ->assertSee('Civil Service Form No. 48')
        ->assertSee('Alice Example');
});

test('attendance APIs deny access to a different course', function () {
    $session = attendancePortalSession();
    $crossCourseSave = attendanceSavePayload([
        '2026-08-03' => [
            'am_in' => '08:00',
            'am_out' => '12:00',
            'pm_in' => '13:00',
            'pm_out' => '17:00',
        ],
    ], ['course' => 'BSBA']);

    $this->withSession($session)
        ->getJson('/attendance/api/course-counts?course=BSBA')
        ->assertForbidden();

    $this->withSession($session)
        ->getJson('/attendance/api/attendance-data/BSBA?cutoff_start=2026-08-01')
        ->assertForbidden();

    $this->withSession($session)
        ->postJson('/attendance/api/save-attendance', $crossCourseSave)
        ->assertForbidden();

    $this->withSession($session)
        ->postJson('/attendance/api/bulk-delete-attendance', [
            'course' => 'BSBA',
            'cutoff_start' => '2026-08-01',
            'employee_ids' => ['FT-12'],
        ])
        ->assertForbidden();

    $this->assertDatabaseCount('attendances', 0);
});

test('expired attendance session receives JSON 401 from API routes', function () {
    $this->getJson('/attendance/api/course-counts?course=BSIT')
        ->assertUnauthorized()
        ->assertExactJson(['error' => 'Unauthenticated.']);

    $this->get('/attendance/dashboard')
        ->assertRedirect('/attendance/attendlog');
});

test('saving four punches persists metrics and clearing the day deletes it', function () {
    $session = attendancePortalSession();

    $this->withSession($session)
        ->postJson('/attendance/api/save-attendance', attendanceSavePayload([
            '2026-08-03' => [
                'am_in' => '08:15',
                'am_out' => '12:00',
                'pm_in' => '13:00',
                'pm_out' => '17:00',
            ],
        ]))
        ->assertOk()
        ->assertJson([
            'success' => true,
            'saved' => 1,
        ]);

    $this->assertDatabaseHas('attendances', [
        'employee_id' => 12,
        'user_id' => 91,
        'date' => '2026-08-03',
        'course' => 'BSIT',
        'employee_type' => 'Fulltime',
        'am_in_time' => '08:15',
        'am_out_time' => '12:00',
        'pm_in_time' => '13:00',
        'pm_out_time' => '17:00',
        'hours_rendered' => 7.75,
        'lateness_minutes' => 15,
        'status' => 'present',
    ]);
    $this->assertDatabaseHas('attendance_histories', [
        'employee_id' => 12,
        'user_id' => 91,
        'attendance_date' => '2026-08-03',
        'department' => 'BSIT',
        'employee_type' => 'Fulltime',
        'email' => 'alice@example.test',
        'designation' => 'Instructor I',
        'is_present' => true,
        'status' => 'present',
    ]);

    $this->withSession($session)
        ->postJson('/attendance/api/save-attendance', attendanceSavePayload([
            '2026-08-03' => [
                'am_in' => '',
                'am_out' => '',
                'pm_in' => '',
                'pm_out' => '',
            ],
        ]))
        ->assertOk()
        ->assertJson(['success' => true]);

    $this->assertDatabaseMissing('attendances', [
        'employee_id' => 12,
        'date' => '2026-08-03',
        'course' => 'BSIT',
        'employee_type' => 'Fulltime',
    ]);
    $this->assertDatabaseMissing('attendance_histories', [
        'employee_id' => 12,
        'attendance_date' => '2026-08-03',
        'department' => 'BSIT',
        'employee_type' => 'Fulltime',
    ]);
});

test('monthly DTR edits sync history and note-only rows require a status', function () {
    $session = attendancePortalSession();

    $this->withSession($session)
        ->post('/attendance/dtr/BSIT/12', [
            'month' => '2026-08',
            'employee_name' => 'Alice Example',
            'employee_type' => 'Fulltime',
            'days' => [
                3 => [
                    'am_in' => '09:00',
                    'am_out' => '12:00',
                    'pm_in' => '13:00',
                    'pm_out' => '18:00',
                    'status' => 'present',
                    'remarks' => 'Late arrival approved',
                ],
            ],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('attendances', [
        'employee_id' => 12,
        'date' => '2026-08-03',
        'undertime_minutes' => 60,
        'overtime_minutes' => 60,
        'remarks' => 'Late arrival approved',
    ]);
    $this->assertDatabaseHas('attendance_histories', [
        'employee_id' => 12,
        'attendance_date' => '2026-08-03',
        'hours_worked' => 8,
        'remarks' => 'Late arrival approved',
    ]);

    $this->withSession($session)
        ->post('/attendance/dtr/BSIT/12', [
            'month' => '2026-08',
            'employee_name' => 'Alice Example',
            'employee_type' => 'Fulltime',
            'days' => [3 => ['status' => '', 'remarks' => 'Needs classification']],
        ])
        ->assertSessionHasErrors('days.3');
});

test('bulk delete removes only the selected identity inside the selected cutoff', function () {
    insertPortalAttendance(['date' => '2026-08-01']);
    insertPortalAttendance(['date' => '2026-08-15']);
    insertPortalAttendance(['date' => '2026-08-16']);
    insertPortalAttendance(['date' => '2026-07-31']);
    insertPortalAttendance([
        'date' => '2026-08-03',
        'employee_name' => 'Pat Parttime',
        'employee_type' => 'Parttime',
    ]);
    insertPortalAttendance([
        'date' => '2026-08-03',
        'course' => 'BSBA',
    ]);

    $this->withSession(attendancePortalSession())
        ->postJson('/attendance/api/bulk-delete-attendance', [
            'course' => 'BSIT',
            'cutoff_start' => '2026-08-01',
            'employee_ids' => ['FT-12'],
        ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'deleted' => 2,
        ]);

    $this->assertDatabaseMissing('attendances', [
        'employee_id' => 12,
        'employee_type' => 'Fulltime',
        'course' => 'BSIT',
        'date' => '2026-08-01',
    ]);
    $this->assertDatabaseMissing('attendances', [
        'employee_id' => 12,
        'employee_type' => 'Fulltime',
        'course' => 'BSIT',
        'date' => '2026-08-15',
    ]);
    $this->assertDatabaseHas('attendances', [
        'employee_id' => 12,
        'employee_type' => 'Fulltime',
        'course' => 'BSIT',
        'date' => '2026-08-16',
    ]);
    $this->assertDatabaseHas('attendances', [
        'employee_id' => 12,
        'employee_type' => 'Fulltime',
        'course' => 'BSIT',
        'date' => '2026-07-31',
    ]);
    $this->assertDatabaseHas('attendances', [
        'employee_id' => 12,
        'employee_type' => 'Parttime',
        'course' => 'BSIT',
        'date' => '2026-08-03',
    ]);
    $this->assertDatabaseHas('attendances', [
        'employee_id' => 12,
        'course' => 'BSBA',
        'date' => '2026-08-03',
    ]);
});

/*
 * The attendance register itself had no rendering test, which is how a Blade
 * ParseError in it reached production: the compiled view failed before emitting
 * a single byte, so every checker got a 500 straight after signing in.
 *
 * Compilation errors only surface when a view is actually rendered, so these
 * assertions are deliberately about the page loading at all.
 */
test('the attendance register renders for a signed-in checker', function () {
    $response = $this
        ->withSession(attendancePortalSession())
        ->get(route('attendance.dashboard'));

    $response
        ->assertOk()
        ->assertViewIs('attendance.dashboard')
        ->assertSee('Attendance Register');
});

test('the register hands its route config to the browser intact', function () {
    $html = $this
        ->withSession(attendancePortalSession())
        ->get(route('attendance.dashboard'))
        ->getContent();

    // Blade previously truncated this payload mid-array. Every key has to
    // survive, or the dashboard JS silently loses an endpoint.
    expect($html)->toContain('window.attendancePortal');

    foreach (['attendanceData', 'saveAttendance', 'saveHistory', 'bulkDelete', 'login', 'dtrBase'] as $key) {
        expect($html)->toContain($key);
    }
});

/*
 * The register load failed in production with nothing but "could not be
 * loaded". Two causes were found and are pinned here.
 */

test('attendance API responses are never cacheable', function () {
    $response = $this
        ->withSession(attendancePortalSession())
        ->get('/attendance/api/attendance-data/bsit?cutoff_start=2026-08-01');

    $response->assertOk();

    // This host has LiteSpeed cache in front of it. An unmarked GET carrying
    // one department's roster is fair game for a shared cache to replay to a
    // different checker.
    $cacheControl = $response->headers->get('Cache-Control');
    expect($cacheControl)->toContain('no-store');
    expect($response->headers->get('X-LiteSpeed-Cache-Control'))->toBe('no-cache');
});

test('department matching tolerates stray whitespace and casing', function () {
    insertPortalAttendance();

    // users.course is free text, so the session value is not guaranteed clean.
    foreach ([' bsit ', 'BSIT', 'bSiT'] as $stored) {
        $response = $this
            ->withSession(attendancePortalSession(['user_course' => $stored]))
            ->get('/attendance/api/attendance-data/bsit?cutoff_start=2026-08-01');

        expect($response->status())
            ->toBe(200, "session course [{$stored}] should authorise BSIT");
    }
});

test('a genuinely different department is still refused', function () {
    $response = $this
        ->withSession(attendancePortalSession(['user_course' => 'BSBA']))
        ->get('/attendance/api/attendance-data/bsit?cutoff_start=2026-08-01');

    $response->assertStatus(403);
});
