<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\PayslipHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class MobilePortalController extends Controller
{
    /**
     * Shared with the web portal via Employee::forAccount(), so the mobile app
     * and the browser resolve the same person. Both used to compare emails
     * strictly and fall back to matching employees.id against users.id.
     */
    protected function resolveEmployeeId($user)
    {
        return Employee::forAccount($user)?->id ?? ($user->employee_id ?? $user->id);
    }

    protected function buildStats($attendances)
    {
        $stats = [
            'present_days' => 0,
            'absent_days' => 0,
            'late_days' => 0,
            'total_hours' => '0h',
            'today_time_in' => '—',
            'today_time_out' => '—',
            'today_hours' => '0h',
        ];

        if ($attendances->isNotEmpty()) {
            $totalHours = $attendances->sum(fn ($a) => $a->hours_rendered ?? 0);
            $stats['total_hours'] = $totalHours > 0 ? round($totalHours, 1) . 'h' : '0h';
            $stats['present_days'] = $attendances->where('status', 'present')->count();
            $stats['absent_days'] = $attendances->where('status', 'absent')->count();
            $stats['late_days'] = $attendances->where('status', 'late')->count();

            $today = Carbon::today();
            $todayRecord = $attendances->first(fn ($a) => Carbon::parse($a->date)->isSameDay($today));

            if ($todayRecord) {
                $stats['today_time_in'] = $todayRecord->time_in ? Carbon::parse($todayRecord->time_in)->format('h:i A') : '—';
                $stats['today_time_out'] = $todayRecord->time_out ? Carbon::parse($todayRecord->time_out)->format('h:i A') : '—';
                $stats['today_hours'] = round($todayRecord->hours_rendered ?? 0, 1) . 'h';
            }
        }

        return $stats;
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $employeeId = $this->resolveEmployeeId($user);
        $attendances = Attendance::where('employee_id', $employeeId)->orderByDesc('date')->take(10)->get();
        $stats = $this->buildStats($attendances);
        $announcements = Announcement::orderByDesc('created_at')->take(5)->get();
        $payslips = PayslipHistory::where('email', $user->email)->orderByDesc('sent_at')->take(3)->get();
        $employee = Employee::forAccount($user);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'employee' => $employee,
            'stats' => $stats,
            'announcements' => $announcements,
            'payslips' => $payslips,
            'attendances' => $attendances,
        ]);
    }

    public function attendance(Request $request)
    {
        $user = $request->user();
        $employeeId = $this->resolveEmployeeId($user);
        $attendances = Attendance::where('employee_id', $employeeId)->orderByDesc('date')->get();
        $stats = $this->buildStats($attendances);

        return response()->json([
            'attendances' => $attendances,
            'stats' => $stats,
        ]);
    }

    public function payslips(Request $request)
    {
        $user = $request->user();
        $payslips = PayslipHistory::where('email', $user->email)->orderByDesc('sent_at')->get();

        return response()->json(['payslips' => $payslips]);
    }

    public function announcements(Request $request)
    {
        $announcements = Announcement::orderByDesc('created_at')->get();

        return response()->json(['announcements' => $announcements]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        $employee = Employee::forAccount($user);
        $employeeId = $this->resolveEmployeeId($user);
        $attendances = Attendance::where('employee_id', $employeeId)->orderByDesc('date')->get();
        $stats = $this->buildStats($attendances);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'employee' => $employee,
            'stats' => $stats,
        ]);
    }
}
