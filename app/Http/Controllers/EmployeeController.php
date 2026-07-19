<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\PayslipHistory;
use App\Models\EmployeeTimesheet;
use App\Models\LeaveRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeController extends Controller
{
    // -----------------------------------------------------------------------
    // HELPERS (shared by mobile + portal)
    // -----------------------------------------------------------------------

    protected function resolveEmployeeId($user)
    {
        $employee = Employee::where(function ($q) use ($user) {
            $q->where('email', $user->email)
              ->orWhere('id', $user->employee_id ?? 0);
        })->first();

        return $employee->id ?? ($user->employee_id ?? $user->id);
    }

    protected function getAttendances($employeeId)
    {
        $attendances = Attendance::where('employee_id', $employeeId)
            ->orderByDesc('date')
            ->get();

        if ($attendances->isEmpty()) {
            $rows = DB::table('attendances')
                ->where('employee_id', $employeeId)
                ->orderByDesc('date')
                ->get();
            return $rows;
        }

        return $attendances;
    }

    protected function buildStats($attendances)
    {
        $stats = [
            'present_days'  => 0,
            'absent_days'   => 0,
            'late_days'     => 0,
            'total_hours'   => '0h',
            'today_time_in' => '—',
            'today_time_out'=> '—',
            'today_hours'   => '0h',
        ];

        if ($attendances->isNotEmpty()) {
            $totalHours = $attendances->sum(fn ($a) => $a->hours_rendered ?? 0);
            $stats['total_hours']  = $totalHours > 0 ? round($totalHours, 1) . 'h' : '0h';
            $stats['present_days'] = $attendances->where('status', 'present')->count();
            $stats['absent_days']  = $attendances->where('status', 'absent')->count();
            $stats['late_days']    = $attendances->where('status', 'late')->count();

            $today = Carbon::today();
            $todayRecord = $attendances->first(fn ($a) => Carbon::parse($a->date)->isSameDay($today));

            if ($todayRecord) {
                $stats['today_time_in']  = $todayRecord->time_in  ? Carbon::parse($todayRecord->time_in)->format('h:i A')  : '—';
                $stats['today_time_out'] = $todayRecord->time_out ? Carbon::parse($todayRecord->time_out)->format('h:i A') : '—';
                $stats['today_hours']    = round($todayRecord->hours_rendered ?? 0, 1) . 'h';
            }
        }

        return $stats;
    }

    // -----------------------------------------------------------------------
    // EMPLOYEE PORTAL ROUTES (desktop/web portal)
    // -----------------------------------------------------------------------

    public function portalDashboard(Request $request)
    {
        $user        = Auth::user();
        $employeeId  = $this->resolveEmployeeId($user);
        $attendances = $this->getAttendances($employeeId);
        $stats       = $this->buildStats($attendances);
        $announcements = Announcement::orderByDesc('created_at')->take(5)->get();
        $payslips    = PayslipHistory::where('email', $user->email)
            ->orderByDesc('sent_at')->take(3)->get();
        $employee    = Employee::where('email', $user->email)->first();

        return view('employee.dashboard-v2', compact(
            'user', 'employee', 'stats', 'announcements', 'payslips', 'attendances'
        ));
    }

    public function portalPayslips(Request $request)
    {
        $user     = Auth::user();
        $payslips = PayslipHistory::where('email', $user->email)
            ->orderByDesc('sent_at')->get();

        return view('employee.payslips', compact('user', 'payslips'));
    }

    public function portalPayslipJson(Request $request, PayslipHistory $payslip)
    {
        $user = Auth::user();
        if ($payslip->email !== $user->email) abort(403);

        return response()->json(['payslip' => $payslip]);
    }

    public function portalPayslipDownload(Request $request, PayslipHistory $payslip)
    {
        $user = Auth::user();
        if ($payslip->email !== $user->email) abort(403);

        $pdf = Pdf::loadView('employee.payslip-pdf', ['payslip' => $payslip, 'user' => $user]);
        return $pdf->download('payslip-' . $payslip->id . '.pdf');
    }

    public function portalPayslipView(Request $request, PayslipHistory $payslip)
    {
        $user = Auth::user();
        if ($payslip->email !== $user->email) abort(403);

        return view('employee.payslip-pdf', compact('payslip', 'user'));
    }

    public function portalAttendance(Request $request)
    {
        $user        = Auth::user();
        $employeeId  = $this->resolveEmployeeId($user);
        $attendances = $this->getAttendances($employeeId);
        $stats       = $this->buildStats($attendances);

        return view('employee.attendance', compact('user', 'attendances', 'stats'));
    }

    public function portalTimesheets(Request $request)
    {
        $user       = Auth::user();
        $timesheets = EmployeeTimesheet::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhere('email', $user->email);
        })->orderByDesc('date')->paginate(20);

        return view('employee.timesheets', compact('user', 'timesheets'));
    }

    public function portalStoreTimesheet(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'date'      => 'required|date|date_format:Y-m-d|before_or_equal:today',
            'time_in'   => 'nullable|date_format:H:i',
            'time_out'  => 'nullable|date_format:H:i',
            'work_type' => ['required', Rule::in(['Regular','Overtime','Meeting','Fieldwork','WFH'])],
            'task'      => 'nullable|string|max:1000',
            'remarks'   => 'nullable|string|max:1000',
        ]);

        $employeeId = $this->resolveEmployeeId($user);
        $hours = 0;

        if (!empty($data['time_in']) && !empty($data['time_out'])) {
            $ti = Carbon::createFromFormat('H:i', $data['time_in']);
            $to = Carbon::createFromFormat('H:i', $data['time_out']);
            $hours = max(0, round($ti->floatDiffInHours($to), 2));
        }

        EmployeeTimesheet::create([
            'user_id'       => $user->id,
            'employee_id'   => $employeeId,
            'employee_name' => $user->name,
            'email'         => $user->email,
            'date'          => $data['date'],
            'time_in'       => $data['time_in']  ?? null,
            'time_out'      => $data['time_out'] ?? null,
            'work_type'     => $data['work_type'],
            'task'          => $data['task']     ?? null,
            'remarks'       => $data['remarks']  ?? null,
            'hours'         => $hours,
            'status'        => 'Submitted',
        ]);

        return back()->with('success', 'Timesheet submitted!');
    }

    public function portalAnnouncements(Request $request)
    {
        $user          = Auth::user();
        $announcements = Announcement::orderByDesc('created_at')->get();

        return view('employee.announcements', compact('user', 'announcements'));
    }

    public function portalProfile(Request $request)
    {
        $user     = Auth::user();
        $employee = Employee::where('email', $user->email)->first();
        $employeeId  = $this->resolveEmployeeId($user);
        $attendances = $this->getAttendances($employeeId);
        $stats       = $this->buildStats($attendances);

        return view('employee.profile', compact('user', 'employee', 'stats'));
    }

    public function portalUpdateProfile(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'current_password' => 'nullable|string',
            'new_password'     => 'nullable|string|min:8|confirmed',
        ]);

        $user->name  = $data['name'];
        $user->email = $data['email'];

        if (!empty($data['new_password'])) {
            if (empty($data['current_password']) || !Hash::check($data['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $user->password = Hash::make($data['new_password']);
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully!');
    }

    // Payslip download for employee portal (named route: employee.payslip.download)
    public function portalPayslipDownloadById(Request $request, $id)
    {
        $user    = Auth::user();
        $payslip = PayslipHistory::where('id', $id)->where('email', $user->email)->firstOrFail();
        $pdf     = Pdf::loadView('employee.payslip-pdf', ['payslip' => $payslip, 'user' => $user]);

        return $pdf->download('payslip-' . $payslip->id . '.pdf');
    }
}