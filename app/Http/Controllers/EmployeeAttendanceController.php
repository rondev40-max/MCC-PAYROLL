<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSession;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeAttendanceController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'action' => 'required|in:in,out',
            // A stale browser cannot close a newer session or create a duplicate.
            'last_session_id' => 'required|integer|min:0',
        ]);
        $employee = Employee::forAccount($request->user());
        if (! $employee) {
            throw ValidationException::withMessages(['attendance' => 'Ask payroll to link your account to the employee master list first.']);
        }

        DB::transaction(function () use ($employee, $request, $data) {
            Employee::whereKey($employee->id)->lockForUpdate()->firstOrFail();
            $latest = AttendanceSession::where('employee_id', $employee->id)->latest('id')->first();
            if ((int) $data['last_session_id'] !== (int) ($latest?->id ?? 0)) {
                throw ValidationException::withMessages(['attendance' => 'Attendance has changed. Refresh the page before punching again.']);
            }
            $open = AttendanceSession::where('employee_id', $employee->id)->where('status', 'open')->first();
            $now = now();
            if ($data['action'] === 'in') {
                if ($open) {
                    throw ValidationException::withMessages(['attendance' => 'You are already clocked in.']);
                }
                AttendanceSession::create([
                    'employee_id' => $employee->id,
                    'user_id' => $request->user()->id,
                    'work_date' => $now->toDateString(),
                    'clocked_in_at' => $now,
                ]);
            } else {
                if (! $open || $now->lessThanOrEqualTo($open->clocked_in_at)) {
                    throw ValidationException::withMessages(['attendance' => 'There is no valid open time-in to close.']);
                }
                $seconds = (int) $open->clocked_in_at->diffInSeconds($now);
                $open->update([
                    'clocked_out_at' => $now,
                    'worked_seconds' => $seconds,
                    'status' => $seconds > config('attendance.max_session_hours') * 3600 ? 'needs_review' : 'complete',
                ]);
            }
        }, 3);

        return redirect()->route('employee.dashboard', ['tab' => 'attendance'])
            ->with('success', $data['action'] === 'in' ? 'Time in recorded.' : 'Time out recorded. Duty hours updated.');
    }
}
