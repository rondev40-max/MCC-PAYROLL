<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\Employee;
use App\Models\PayslipHistory;
use App\Support\AttendancePayroll;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendancePayrollController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['start_date' => 'nullable|date_format:Y-m-d', 'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date']);
        $start = $data['start_date'] ?? now()->day(now()->day <= 15 ? 1 : 16)->toDateString();
        $end = $data['end_date'] ?? (Carbon::parse($start)->day <= 15 ? Carbon::parse($start)->day(15) : Carbon::parse($start)->endOfMonth())->toDateString();
        $employees = Employee::orderBy('name')->paginate(25)->withQueryString();
        $summaries = $employees->mapWithKeys(fn ($employee) => [$employee->id => AttendancePayroll::summary($employee, $start, $end)]);
        $previews = $employees->mapWithKeys(fn ($employee) => [$employee->id => AttendancePayroll::preview($employee, $start, $end)]);

        return view('admin.attendance-payroll', compact('employees', 'summaries', 'previews', 'start', 'end'));
    }

    public function enable(Request $request, Employee $employee)
    {
        $data = $request->validate(['effective_from' => 'required|date_format:Y-m-d']);
        $date = Carbon::parse($data['effective_from']);
        if (! in_array($date->day, [1, 16], true)) {
            throw ValidationException::withMessages(['effective_from' => 'Choose the first day of a cutoff (1st or 16th).']);
        }
        DB::transaction(function () use ($employee, $data, $date) {
            $employee = Employee::whereKey($employee->id)->lockForUpdate()->firstOrFail();
            if ($employee->attendance_payroll_from) {
                throw ValidationException::withMessages(['effective_from' => 'Attendance payroll is already enabled.']);
            }
            // Activation is allowed only before payroll has been issued for this date or later.
            if (PayslipHistory::withTrashed()->whereRaw('LOWER(TRIM(email)) = ?', [strtolower(trim($employee->email))])->where('sent_at', '>=', $date)->exists()) {
                throw ValidationException::withMessages(['effective_from' => 'Payroll has already been issued. Choose a later cutoff.']);
            }
            $employee->forceFill(['attendance_payroll_from' => $data['effective_from']])->save();
        });

        return back()->with('success', 'Attendance payroll enabled. Keep the payroll timesheet linked to this master-list employee and its rate up to date.');
    }

    public function review(Request $request, AttendanceSession $session)
    {
        $data = $request->validate([
            'decision' => 'required|in:complete,void',
            'hours' => 'required_if:decision,complete|nullable|numeric|min:0|max:18',
            'note' => 'required|string|min:5|max:1000',
        ]);
        DB::transaction(function () use ($session, $data, $request) {
            Employee::whereKey($session->employee_id)->lockForUpdate()->firstOrFail();
            $session = AttendanceSession::whereKey($session->id)->lockForUpdate()->firstOrFail();
            if (! in_array($session->status, ['open', 'needs_review'], true)) {
                throw ValidationException::withMessages(['attendance' => 'This session has already been reviewed.']);
            }
            $seconds = $data['decision'] === 'complete' ? (int) round((float) $data['hours'] * 3600) : 0;
            $elapsed = $session->clocked_in_at->diffInSeconds($session->clocked_out_at ?? now());
            if ($seconds > $elapsed) {
                throw ValidationException::withMessages(['hours' => 'Approved hours cannot exceed the elapsed session time.']);
            }
            // Preserve original punches; the reviewer, reason and approved duration are separate.
            $session->update([
                'status' => $data['decision'],
                'worked_seconds' => $seconds,
                'reviewed_by' => $request->user()->id,
                'review_note' => $data['note'],
            ]);
        });

        return back()->with('success', 'Attendance reviewed. Payroll hours updated.');
    }
}
