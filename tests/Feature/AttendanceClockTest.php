<?php

use App\Models\AttendanceSession;
use App\Models\Employee;
use App\Models\FulltimeTimesheet;
use App\Models\PayslipHistory;
use App\Models\User;
use App\Support\AttendancePayroll;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->employee = Employee::create(['name' => 'Clock Employee', 'email' => 'clock@example.com', 'position' => 'Staff']);
    $this->user = User::factory()->create(['email' => 'clock@example.com', 'role' => 'employee']);
    $this->travelTo(Carbon::parse('2026-09-25 08:00:00'));
});

function clockSession($test, array $overrides = []): AttendanceSession
{
    return AttendanceSession::create(array_merge([
        'employee_id' => $test->employee->id,
        'user_id' => $test->user->id,
        'work_date' => '2026-09-25',
        'clocked_in_at' => '2026-09-25 08:00:00',
        'clocked_out_at' => '2026-09-25 12:00:00',
        'worked_seconds' => 14400,
        'status' => 'complete',
    ], $overrides));
}

function clockPayrollRow($test): FulltimeTimesheet
{
    return FulltimeTimesheet::create([
        'employee_id' => $test->employee->id, 'employee_name' => $test->employee->name,
        'email' => $test->employee->email, 'designation' => 'Instructor', 'department' => 'BSIT',
        'date' => '2026-09-16', 'month' => 9, 'year' => 2026, 'period' => '16-end',
        'total_hour' => 100, 'rate_per_hour' => 200, 'total_honorarium' => 20000,
        'deduction' => 50,
    ]);
}

it('records server time and the signed-in employee, ignoring forged values', function () {
    $this->actingAs($this->user)->post(route('employee.attendance.punch'), [
        'action' => 'in', 'last_session_id' => 0, 'employee_id' => 999,
        'clocked_in_at' => '2020-01-01 01:00:00',
    ])->assertRedirect(route('employee.dashboard', ['tab' => 'attendance']));
    $session = AttendanceSession::sole();
    expect($session->employee_id)->toBe($this->employee->id)
        ->and($session->clocked_in_at->format('Y-m-d H:i:s'))->toBe('2026-09-25 08:00:00');
    $this->travelTo(Carbon::parse('2026-09-25 12:30:00'));
    $this->post(route('employee.attendance.punch'), ['action' => 'out', 'last_session_id' => $session->id])->assertSessionHasNoErrors();
    expect($session->fresh()->worked_seconds)->toBe(16200);
});

it('rejects stale duplicate punches and time out without time in', function () {
    $this->actingAs($this->user)->post(route('employee.attendance.punch'), ['action' => 'out', 'last_session_id' => 0])->assertSessionHasErrors('attendance');
    $this->post(route('employee.attendance.punch'), ['action' => 'in', 'last_session_id' => 0])->assertSessionHasNoErrors();
    $this->post(route('employee.attendance.punch'), ['action' => 'in', 'last_session_id' => 0])->assertSessionHasErrors('attendance');
    expect(AttendanceSession::count())->toBe(1);
});

it('requires a linked employee and employee role', function () {
    $unlinked = User::factory()->create(['role' => 'employee']);
    $this->actingAs($unlinked)->post(route('employee.attendance.punch'), ['action' => 'in', 'last_session_id' => 0])->assertSessionHasErrors('attendance');
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->post(route('employee.attendance.punch'), ['action' => 'in', 'last_session_id' => 0]);
    expect(AttendanceSession::count())->toBe(0);
});

it('supports overnight duty and assigns it to its start date', function () {
    $this->travelTo(Carbon::parse('2026-09-30 22:00:00'));
    $this->actingAs($this->user)->post(route('employee.attendance.punch'), ['action' => 'in', 'last_session_id' => 0]);
    $this->travelTo(Carbon::parse('2026-10-01 06:00:00'));
    $session = AttendanceSession::sole();
    $this->post(route('employee.attendance.punch'), ['action' => 'out', 'last_session_id' => $session->id])->assertSessionHasNoErrors();
    expect($session->fresh()->worked_seconds)->toBe(28800)
        ->and($session->fresh()->work_date->toDateString())->toBe('2026-09-30');
});

it('excludes unpaid gaps, caps daily units and separates cutoffs', function () {
    clockSession($this);
    clockSession($this, ['clocked_in_at' => '2026-09-25 13:00:00', 'clocked_out_at' => '2026-09-25 18:00:00', 'worked_seconds' => 18000]);
    clockSession($this, ['work_date' => '2026-09-15']);
    $summary = AttendancePayroll::summary($this->employee, '2026-09-16', '2026-09-30');
    expect($summary['hours'])->toBe(9.0)->and($summary['days'])->toBe(1.0);
});

it('flags long sessions and requires admin review with a reason', function () {
    $session = clockSession($this, ['clocked_out_at' => null, 'worked_seconds' => 0, 'status' => 'open']);
    $this->travelTo(Carbon::parse('2026-09-26 08:00:00'));
    $this->actingAs($this->user)->post(route('employee.attendance.punch'), ['action' => 'out', 'last_session_id' => $session->id]);
    expect($session->fresh()->status)->toBe('needs_review');
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->post(route('admin.attendance-payroll.review', $session), ['decision' => 'complete', 'hours' => 8])->assertSessionHasErrors('note');
    $this->post(route('admin.attendance-payroll.review', $session), ['decision' => 'complete', 'hours' => 8, 'note' => 'Verified shift with supervisor.'])->assertSessionHasNoErrors();
    expect($session->fresh()->worked_seconds)->toBe(28800)
        ->and($session->fresh()->reviewed_by)->toBe($admin->id)
        ->and($session->fresh()->clocked_out_at->format('Y-m-d H:i:s'))->toBe('2026-09-26 08:00:00');
});

it('allows an admin to resolve a missed time out without inventing a timestamp', function () {
    $session = clockSession($this, ['clocked_out_at' => null, 'worked_seconds' => 0, 'status' => 'open']);
    $this->travelTo(Carbon::parse('2026-09-25 17:00:00'));
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->post(route('admin.attendance-payroll.review', $session), ['decision' => 'complete', 'hours' => 8, 'note' => 'Confirmed missed punch.'])->assertSessionHasNoErrors();
    expect($session->fresh()->clocked_out_at)->toBeNull()->and($session->fresh()->status)->toBe('complete');
    $this->actingAs($this->user)->post(route('employee.attendance.punch'), ['action' => 'in', 'last_session_id' => $session->id])->assertSessionHasNoErrors();
});

it('protects admin activation and requires a cutoff start', function () {
    $url = route('admin.attendance-payroll.enable', $this->employee);
    $this->actingAs($this->user)->post($url, ['effective_from' => '2026-09-16']);
    expect($this->employee->fresh()->attendance_payroll_from)->toBeNull();
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->post($url, ['effective_from' => '2026-09-25'])->assertSessionHasErrors('effective_from');
    $this->post($url, ['effective_from' => '2026-09-16'])->assertSessionHasNoErrors();
    expect($this->employee->fresh()->attendance_payroll_from)->toBe('2026-09-16');
});

it('uses attendance in actual payslip generation and preserves deductions', function () {
    Mail::fake();
    $this->employee->forceFill(['attendance_payroll_from' => '2026-09-16'])->save();
    clockPayrollRow($this);
    clockSession($this);
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->post(route('admin.send.payslips'), ['start_date' => '2026-09-16', 'end_date' => '2026-09-30'])->assertSessionHasNoErrors();
    $payslip = PayslipHistory::sole();
    expect((float) $payslip->total_hours_or_days)->toBe(4.0)
        ->and((float) $payslip->gross_pay)->toBe(800.0)
        ->and((float) $payslip->net_pay)->toBe(750.0);
    Mail::assertSent(App\Mail\PayslipMail::class, function ($mail) {
        $html = $mail->render();
        expect($html)->toContain('800.00')->toContain('750.00')->not->toContain('20,000.00');

        return true;
    });
    expect($payslip->attendance_snapshot['hours_by_date']['2026-09-25'])->toBe(4);
    AttendanceSession::sole()->update(['worked_seconds' => 7200]);
    expect($payslip->fresh()->attendance_snapshot['hours_by_date']['2026-09-25'])->toBe(4)
        ->and((float) $payslip->fresh()->gross_pay)->toBe(800.0);
});

it('blocks the whole payroll batch before sending when a punch is unresolved', function () {
    Mail::fake();
    $this->employee->forceFill(['attendance_payroll_from' => '2026-09-16'])->save();
    clockPayrollRow($this);
    clockSession($this, ['clocked_out_at' => null, 'worked_seconds' => 0, 'status' => 'open']);
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->post(route('admin.send.payslips'), ['start_date' => '2026-09-16', 'end_date' => '2026-09-30'])->assertSessionHasErrors('attendance');
    Mail::assertNothingSent();
    expect(PayslipHistory::count())->toBe(0);
});

it('does not fall back to scheduled hours when enabled but no punches exist', function () {
    $this->employee->forceFill(['attendance_payroll_from' => '2026-09-16'])->save();
    $payload = ['timesheet' => clockPayrollRow($this), 'rate' => 200, 'type' => 'Fulltime', 'totalHonorarium' => 20000];
    $result = AttendancePayroll::apply($payload, Carbon::parse('2026-09-16'), Carbon::parse('2026-09-30'));
    expect($result['totalHonorarium'])->toBe(0.0)->and($result['totalDaysOrHours'])->toBe('0 hours');
});

it('renders employee punches and admin summaries without exposing other employees', function () {
    clockSession($this);
    $this->actingAs($this->user)->get(route('employee.dashboard', ['tab' => 'attendance']))->assertOk()->assertSee('Time in / Time out')->assertSee('4.00 h');
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->get(route('admin.attendance-payroll.index'))->assertOk()->assertSee('4.00 duty hours');
});

it('calculates partial daily pay through payslip generation for every daily category', function ($model, $type) {
    Mail::fake();
    $this->employee->forceFill(['attendance_payroll_from' => '2026-09-16'])->save();
    $row = new $model;
    $row->forceFill([
        'employee_id' => $this->employee->id, 'employee_name' => $this->employee->name,
        'email' => $this->employee->email, 'designation' => 'Staff', 'department' => 'BSIT',
        'month' => 9, 'year' => 2026, 'period' => '16-end',
        'total_days' => 10, 'rate_per_day' => 1000, 'total_honorarium' => 10000, 'deduction' => 50,
    ])->save();
    clockSession($this, ['clocked_out_at' => '2026-09-25 12:30:00', 'worked_seconds' => 16200]);
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->post(route('admin.send.payslips'), ['start_date' => '2026-09-16', 'end_date' => '2026-09-30'])->assertSessionHasNoErrors();
    $payslip = PayslipHistory::sole();
    expect($payslip->employee_type)->toBe($type)->and($payslip->rate_unit)->toBe('day')
        ->and((float) $payslip->total_hours_or_days)->toBe(0.5625)
        ->and((float) $payslip->gross_pay)->toBe(562.5)
        ->and((float) $payslip->net_pay)->toBe(512.5);
})->with([
    [App\Models\StaffTimesheet::class, 'Staff'],
    [App\Models\UtilityTimesheet::class, 'Utility'],
    [App\Models\WatchmanTimesheet::class, 'Watchman'],
    [App\Models\AdminPersonnelTimesheet::class, 'Admin Personnel'],
]);

it('matches an unlinked payroll row by normalized master-list email', function () {
    $this->employee->forceFill(['attendance_payroll_from' => '2026-09-16'])->save();
    clockSession($this);
    $row = clockPayrollRow($this);
    $row->forceFill(['employee_id' => null, 'email' => 'CLOCK@EXAMPLE.COM '])->save();
    $result = AttendancePayroll::apply(['timesheet' => $row, 'rate' => 200, 'type' => 'Fulltime'], Carbon::parse('2026-09-16'), Carbon::parse('2026-09-30'));
    expect($result['totalHonorarium'])->toBe(800.0);
    expect(AttendancePayroll::preview($this->employee, '2026-09-16', '2026-09-30')[0]['gross'])->toBe(800.0);
});

it('preserves manual payroll before activation', function () {
    $this->employee->forceFill(['attendance_payroll_from' => '2026-10-01'])->save();
    clockSession($this);
    $payload = ['timesheet' => clockPayrollRow($this), 'rate' => 200, 'type' => 'Fulltime', 'totalHonorarium' => 20000];
    expect(AttendancePayroll::apply($payload, Carbon::parse('2026-09-16'), Carbon::parse('2026-09-30')))->toBe($payload);
});

it('does not display another employees punches or allow their review', function () {
    $session = clockSession($this, ['status' => 'needs_review', 'review_note' => 'Private supervisor note']);
    $other = Employee::create(['name' => 'Other Employee', 'email' => 'other@example.com', 'position' => 'Staff']);
    $user = User::factory()->create(['email' => $other->email, 'role' => 'employee']);
    $this->actingAs($user)->get(route('employee.dashboard', ['tab' => 'attendance']))->assertOk()->assertDontSee('Private supervisor note');
    $this->post(route('admin.attendance-payroll.review', $session), ['decision' => 'void', 'note' => 'Unauthorized change']);
    expect($session->fresh()->status)->toBe('needs_review');
});

it('auto-closes forgotten open sessions via artisan command', function () {
    $openSession = AttendanceSession::create([
        'employee_id'   => $this->employee->id,
        'user_id'       => $this->user->id,
        'work_date'     => now()->subDays(2)->toDateString(),
        'clocked_in_at' => now()->subDays(2)->setTime(8, 0),
        'status'        => 'open',
    ]);

    $this->artisan('attendance:auto-close')
        ->expectsOutputToContain('Successfully auto-closed 1 forgotten open attendance session(s).')
        ->assertSuccessful();

    $fresh = $openSession->fresh();
    expect($fresh->status)->toBe('needs_review')
        ->and($fresh->auto_closed)->toBeTrue()
        ->and($fresh->worked_seconds)->toBe(8 * 3600)
        ->and($fresh->clocked_out_at)->not->toBeNull();
});

it('auto-heals previous shift forgotten open session when clocking in today', function () {
    $oldSession = AttendanceSession::create([
        'employee_id'   => $this->employee->id,
        'user_id'       => $this->user->id,
        'work_date'     => now()->subDay()->toDateString(),
        'clocked_in_at' => now()->subDay()->setTime(8, 0),
        'status'        => 'open',
    ]);

    $response = $this->actingAs($this->user)->post(route('employee.attendance.punch'), [
        'action'          => 'in',
        'last_session_id' => $oldSession->id,
    ]);

    $response->assertRedirect(route('employee.dashboard', ['tab' => 'attendance']));

    $oldFresh = $oldSession->fresh();
    expect($oldFresh->status)->toBe('needs_review')
        ->and($oldFresh->auto_closed)->toBeTrue();

    $newSession = AttendanceSession::where('employee_id', $this->employee->id)->latest('id')->first();
    expect($newSession->id)->not->toBe($oldSession->id)
        ->and($newSession->status)->toBe('open')
        ->and($newSession->work_date->toDateString())->toBe(now()->toDateString());
});

it('records client IP and user agent on punch-in', function () {
    $this->actingAs($this->user)
        ->withServerVariables(['REMOTE_ADDR' => '192.168.10.50', 'HTTP_USER_AGENT' => 'CampusBrowser/1.0'])
        ->post(route('employee.attendance.punch'), [
            'action'          => 'in',
            'last_session_id' => 0,
        ]);

    $session = AttendanceSession::where('employee_id', $this->employee->id)->latest('id')->first();
    expect($session->ip_address)->toBe('192.168.10.50');
});

