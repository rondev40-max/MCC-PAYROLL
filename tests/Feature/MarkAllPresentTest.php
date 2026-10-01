<?php

use App\Models\FulltimeTimesheet;
use App\Models\Holiday;
use App\Models\ParttimeTimesheet;
use App\Models\StaffTimesheet;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    // A Tuesday, mid-morning.
    $this->travelTo(Carbon::parse('2026-09-22 10:00:00'));

    $this->fulltime = FulltimeTimesheet::create([
        'employee_name' => 'Ana Fulltime', 'email' => 'ana@example.com', 'designation' => 'Instructor',
        'department' => 'BSIT', 'date' => '2026-09-16', 'period' => '16-end', 'month' => 9, 'year' => 2026,
    ]);
    $this->staff = StaffTimesheet::create([
        'employee_name' => 'Ben Staff', 'email' => 'ben@example.com', 'designation' => 'Clerk',
        'department' => 'BSIT', 'month' => 9, 'year' => 2026, 'period' => '16-end',
    ]);
    $this->parttime = ParttimeTimesheet::create([
        'employee_name' => 'Cara Parttime', 'email' => 'cara@example.com', 'designation' => 'Instructor',
        'department' => 'BSIT', 'month' => 9, 'year' => 2026, 'period' => '16-end',
    ]);
    $this->otherDepartment = FulltimeTimesheet::create([
        'employee_name' => 'Dan Business', 'email' => 'dan@example.com', 'designation' => 'Instructor',
        'department' => 'BSBA', 'date' => '2026-09-16', 'period' => '16-end', 'month' => 9, 'year' => 2026,
    ]);

    // Ben was already entered as late; marking the day must not undo that.
    DB::table('attendances')->insert([
        'employee_id' => $this->staff->id, 'date' => '2026-09-22', 'course' => 'BSIT', 'employee_type' => 'Staff',
        'employee_name' => 'Ben Staff', 'status' => 'late',
        'am_in_time' => '08:20', 'am_out_time' => '12:00', 'pm_in_time' => '13:00', 'pm_out_time' => '17:00',
        'time_in' => '08:20', 'time_out' => '17:00', 'created_at' => now(), 'updated_at' => now(),
    ]);
});

function checkerSession(string $course = 'BSIT'): array
{
    return [
        'user_id' => 91, 'user_name' => 'Checker', 'user_role' => 'attendance_checker',
        'user_course' => $course, 'is_attendance' => true,
    ];
}

function markDay($test, string $date, string $course = 'BSIT', ?array $session = null)
{
    return $test->withSession($session ?? checkerSession())
        ->postJson('/attendance/api/mark-present', ['course' => $course, 'date' => $date]);
}

it('marks everyone without an entry present at the official hours', function () {
    markDay($this, '2026-09-22')
        ->assertOk()
        ->assertJson(['success' => true, 'marked' => 1, 'kept' => 1, 'part_time' => 1]);

    $ana = DB::table('attendances')->where('employee_id', $this->fulltime->id)->where('employee_type', 'Fulltime')->sole();
    expect(substr($ana->am_in_time, 0, 5))->toBe('08:00')
        ->and(substr($ana->am_out_time, 0, 5))->toBe('12:00')
        ->and(substr($ana->pm_in_time, 0, 5))->toBe('13:00')
        ->and(substr($ana->pm_out_time, 0, 5))->toBe('17:00')
        ->and($ana->status)->toBe('present')
        ->and((int) $ana->undertime_minutes)->toBe(0)
        ->and((float) $ana->total_hours)->toBe(8.0);

    // Ben's late entry is untouched.
    $ben = DB::table('attendances')->where('employee_id', $this->staff->id)->where('employee_type', 'Staff')->sole();
    expect($ben->status)->toBe('late')->and(substr($ben->am_in_time, 0, 5))->toBe('08:20');

    // Part-time instructors and other departments are left alone.
    expect(DB::table('attendances')->where('employee_type', 'Parttime')->count())->toBe(0)
        ->and(DB::table('attendances')->where('course', 'BSBA')->count())->toBe(0);

    // The history table the rest of the portal reads is kept in step.
    expect(DB::table('attendance_histories')->where('employee_id', $this->fulltime->id)->value('status'))->toBe('present');
});

it('is safe to press twice', function () {
    markDay($this, '2026-09-22')->assertJson(['marked' => 1]);
    markDay($this, '2026-09-22')->assertJson(['marked' => 0, 'kept' => 2]);

    expect(DB::table('attendances')->where('date', '2026-09-22')->count())->toBe(2);
});

it('refuses days that have not happened, Sundays and holidays', function () {
    Holiday::create(['date' => '2026-09-21', 'name' => 'Test Holiday']);

    markDay($this, '2026-09-23')->assertStatus(422)->assertJson(['success' => false]);
    markDay($this, '2026-09-20')->assertStatus(422)->assertJsonFragment(['message' => 'Sunday is not a working day. Enter anyone who worked one by one.']);
    markDay($this, '2026-09-21')->assertStatus(422);
    expect(markDay($this, '2026-09-21')->json('message'))->toContain('Test Holiday');

    expect(DB::table('attendances')->count())->toBe(1);
});

it('only works on the checker\'s own department and signed-in session', function () {
    markDay($this, '2026-09-22', 'BSBA')->assertStatus(403);
    $this->flushSession();
    $this->postJson('/attendance/api/mark-present', ['course' => 'BSIT', 'date' => '2026-09-22'])->assertStatus(401);

    expect(DB::table('attendances')->count())->toBe(1);
});

it('feeds the register that payroll reads', function () {
    markDay($this, '2026-09-22');

    $summary = App\Support\CheckerDtrPayroll::summarize(
        $this->fulltime->fresh(), 'Fulltime', Carbon::parse('2026-09-22'), Carbon::parse('2026-09-22'), []
    );

    expect($summary['problems'])->toBe([])->and($summary['paid_minutes'])->toBe(480);
});
