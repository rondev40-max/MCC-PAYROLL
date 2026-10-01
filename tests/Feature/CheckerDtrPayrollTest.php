<?php

use App\Mail\PayslipMail;
use App\Models\Employee;
use App\Models\FulltimeTimesheet;
use App\Models\Holiday;
use App\Models\ParttimeTimesheet;
use App\Models\PayslipHistory;
use App\Models\Setting;
use App\Models\StaffTimesheet;
use App\Models\User;
use App\Support\CheckerDtrPayroll;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

// Cutoff under test: Sep 16-30 2026, which has 11 weekdays.
const DTR_START = '2026-09-16';
const DTR_END = '2026-09-30';

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-10-01 09:00:00'));
    $this->admin = User::factory()->create(['role' => 'admin']);
    Setting::set(CheckerDtrPayroll::SETTING, '1', 'payroll');
});

function dtrDay(int $employeeId, string $date, array $overrides = [], string $type = 'Fulltime', string $course = 'BSIT'): void
{
    $row = array_merge([
        'am_in_time' => '08:00', 'am_out_time' => '12:00', 'pm_in_time' => '13:00', 'pm_out_time' => '17:00',
        'status' => 'present',
    ], $overrides);

    DB::table('attendances')->insert(array_merge($row, [
        'employee_id' => $employeeId,
        'date' => $date,
        'time_in' => $row['am_in_time'],
        'time_out' => $row['pm_out_time'],
        'course' => $course,
        'employee_name' => 'Register Person',
        'employee_type' => $type,
        'created_at' => now(),
        'updated_at' => now(),
    ]));
}

/** Official hours on every weekday of the cutoff, except the dates given. */
function dtrCutoff(int $employeeId, array $exceptions = [], string $type = 'Fulltime', string $course = 'BSIT'): void
{
    foreach (CarbonPeriod::create(DTR_START, DTR_END) as $date) {
        $key = $date->toDateString();
        if ($date->isWeekend() || array_key_exists($key, $exceptions)) {
            if (is_array($exceptions[$key] ?? null)) {
                dtrDay($employeeId, $key, $exceptions[$key], $type, $course);
            }
            continue;
        }
        dtrDay($employeeId, $key, [], $type, $course);
    }
}

function dtrFulltimeRow(array $overrides = []): FulltimeTimesheet
{
    $employee = Employee::create(['name' => 'Juan Dela Cruz', 'email' => 'juan@example.com', 'position' => 'Full-time Instructor']);

    return FulltimeTimesheet::create(array_merge([
        'employee_id' => $employee->id, 'employee_name' => 'Juan Dela Cruz', 'email' => 'juan@example.com',
        'designation' => 'Instructor', 'department' => 'BSIT',
        'date' => DTR_START, 'month' => 9, 'year' => 2026, 'period' => '16-end',
        'total_hour' => 100, 'rate_per_hour' => 200, 'total_honorarium' => 20000, 'deduction' => 50,
    ], $overrides));
}

function sendCutoff($test, string $start = DTR_START, string $end = DTR_END)
{
    return $test->actingAs($test->admin)->post(route('admin.send.payslips'), ['start_date' => $start, 'end_date' => $end]);
}

it('pays a full-time instructor for the DTR, deducting late and undertime', function () {
    Mail::fake();
    $row = dtrFulltimeRow();
    dtrCutoff($row->employee_id, [
        '2026-09-22' => ['am_in_time' => '08:30'],                                   // 30 min late: 450
        '2026-09-23' => ['status' => 'absent', 'am_in_time' => null, 'am_out_time' => null, 'pm_in_time' => null, 'pm_out_time' => null],
        '2026-09-24' => ['status' => 'half_day', 'pm_in_time' => null, 'pm_out_time' => null], // morning only: 240
        '2026-09-25' => ['status' => 'official_business', 'am_in_time' => null, 'am_out_time' => null, 'pm_in_time' => null, 'pm_out_time' => null], // 480
        '2026-09-28' => ['status' => 'leave', 'am_in_time' => null, 'am_out_time' => null, 'pm_in_time' => null, 'pm_out_time' => null],
        '2026-09-29' => ['pm_out_time' => '16:00'],                                  // left an hour early: 420
    ]);

    sendCutoff($this)->assertSessionHasNoErrors()->assertSessionHas('success');

    // 5 full days (2400) + 450 + 240 + 480 + 420 = 3990 min = 66.5 h x 200
    $payslip = PayslipHistory::sole();
    expect((float) $payslip->total_hours_or_days)->toBe(66.5)
        ->and((float) $payslip->gross_pay)->toBe(13300.0)
        ->and((float) $payslip->net_pay)->toBe(13250.0)
        ->and($payslip->attendance_snapshot['source'])->toBe('checker_dtr')
        ->and($payslip->attendance_snapshot['hours_by_date']['2026-09-22'])->toBe(7.5)
        ->and($payslip->attendance_snapshot['hours_by_date']['2026-09-23'])->toBe(0);

    Mail::assertSent(PayslipMail::class, function ($mail) {
        expect($mail->render())->toContain('13,300.00')->not->toContain('20,000.00');

        return true;
    });

    // The stored timesheet keeps the administrator's schedule untouched.
    expect((float) $row->fresh()->total_hour)->toBe(100.0);
});

it('pays staff by the day, with half days counted as half', function () {
    Mail::fake();
    $staff = StaffTimesheet::create([
        'employee_name' => 'Staff Person', 'email' => 'staff@example.com', 'designation' => 'Clerk',
        'department' => 'BSIT', 'month' => 9, 'year' => 2026, 'period' => '16-end',
        'total_days' => 11, 'rate_per_day' => 500, 'total_honorarium' => 5500,
    ]);
    // No master-list link: the register identifies this person by the timesheet row id.
    dtrCutoff($staff->id, [
        '2026-09-24' => ['status' => 'half_day', 'am_in_time' => null, 'am_out_time' => null],
    ], 'Staff');

    sendCutoff($this)->assertSessionHasNoErrors();

    $payslip = PayslipHistory::sole();
    expect((float) $payslip->total_hours_or_days)->toBe(10.5)
        ->and((float) $payslip->gross_pay)->toBe(5250.0);
});

it('does not require holidays or weekends, but pays anyone recorded working on them', function () {
    Mail::fake();
    Holiday::create(['date' => '2026-09-21', 'name' => 'Test Holiday']);
    $row = dtrFulltimeRow();
    dtrCutoff($row->employee_id, ['2026-09-21' => null]); // holiday left blank
    dtrDay($row->employee_id, '2026-09-26');              // a Saturday that was worked

    sendCutoff($this)->assertSessionHasNoErrors();

    // 10 weekdays + 1 Saturday, all 8 h
    expect((float) PayslipHistory::sole()->total_hours_or_days)->toBe(88.0);
});

it('refuses the whole batch and names every unfinished day before sending anything', function () {
    Mail::fake();
    $row = dtrFulltimeRow();
    dtrCutoff($row->employee_id, [
        '2026-09-23' => null,
        '2026-09-24' => null,
        '2026-09-25' => ['am_in_time' => null, 'am_out_time' => null, 'pm_in_time' => null, 'pm_out_time' => null], // "present" with no times
    ]);

    sendCutoff($this)->assertSessionHasErrors('attendance');

    $message = session('error');
    expect($message)->toContain('Juan Dela Cruz (BSIT)')
        ->toContain('no entry for Sep 23, Sep 24')
        ->toContain('unfinished time entries on Sep 25');
    Mail::assertNothingSent();
    expect(PayslipHistory::count())->toBe(0);
});

it('never counts another employee type sharing the same id', function () {
    $row = dtrFulltimeRow();
    dtrCutoff($row->employee_id, [], 'Staff'); // a staff register under the same number

    sendCutoff($this)->assertSessionHasErrors('attendance');
    expect(PayslipHistory::count())->toBe(0);
});

it('needs one whole cutoff', function () {
    $row = dtrFulltimeRow();
    dtrCutoff($row->employee_id);

    sendCutoff($this, '2026-09-16', '2026-09-25')->assertSessionHasErrors('attendance');
    expect(session('error'))->toContain('whole cut-off');
});

it('leaves payroll exactly as before when the setting is off', function () {
    Mail::fake();
    Setting::set(CheckerDtrPayroll::SETTING, '0', 'payroll');
    dtrFulltimeRow();
    // Nothing in the register at all: the old behaviour must not care.

    sendCutoff($this)->assertSessionHasNoErrors();

    $payslip = PayslipHistory::sole();
    expect((float) $payslip->total_hours_or_days)->toBe(100.0)
        ->and((float) $payslip->gross_pay)->toBe(20000.0)
        ->and($payslip->attendance_snapshot)->toBeNull();
});

it('keeps part-time instructors on their timesheet teaching load', function () {
    Mail::fake();
    ParttimeTimesheet::create([
        'employee_name' => 'Part Timer', 'email' => 'pt@example.com', 'designation' => 'Instructor',
        'department' => 'BSIT', 'month' => 9, 'year' => 2026, 'period' => '16-end',
        'total_hour' => 12, 'rate_per_hour' => 150, 'total_honorarium' => 1800,
    ]);

    sendCutoff($this)->assertSessionHasNoErrors();

    expect((float) PayslipHistory::sole()->total_hours_or_days)->toBe(12.0);
});

it('leaves employees on web Time In / Time Out payroll to that system', function () {
    expect(CheckerDtrPayroll::covers(['type' => 'Fulltime', 'timesheet' => new FulltimeTimesheet(), 'attendance_snapshot' => ['hours_by_date' => []]]))->toBeFalse()
        ->and(CheckerDtrPayroll::covers(['type' => 'Fulltime', 'timesheet' => new FulltimeTimesheet()]))->toBeTrue()
        ->and(CheckerDtrPayroll::covers(['type' => 'Part-time', 'timesheet' => new ParttimeTimesheet()]))->toBeFalse()
        ->and(CheckerDtrPayroll::covers(['type' => 'Watchman', 'timesheet' => new FulltimeTimesheet()]))->toBeFalse();
});

it('reads legacy rows that only carry time_in and time_out the way the DTR does', function () {
    $day = CheckerDtrPayroll::day((object) [
        'status' => 'present', 'time_in' => '08:10:00', 'time_out' => '17:00:00',
        'am_in_time' => null, 'am_out_time' => null, 'pm_in_time' => null, 'pm_out_time' => null,
        'hours_rendered' => 8,
    ]);

    expect($day['incomplete'])->toBeFalse()->and($day['paid_minutes'])->toBe(470);
});

it('lets the administrator switch it on and off in System Settings', function () {
    Setting::set(CheckerDtrPayroll::SETTING, '0', 'payroll');

    $this->actingAs($this->admin)->get(route('admin.settings.index'))
        ->assertOk()
        ->assertSee("Pay from the attendance checker's DTR", false)
        ->assertSee('name="settings[' . CheckerDtrPayroll::SETTING . ']"', false);

    $this->actingAs($this->admin)->post(route('admin.settings.update'), [
        'settings' => [CheckerDtrPayroll::SETTING => '1', 'currency_symbol' => '₱'],
    ])->assertRedirect(route('admin.settings.index'));
    expect(CheckerDtrPayroll::enabled())->toBeTrue();

    // An unticked checkbox is simply absent from the form.
    $this->post(route('admin.settings.update'), ['settings' => ['currency_symbol' => '₱']]);
    expect(CheckerDtrPayroll::enabled())->toBeFalse();
});
