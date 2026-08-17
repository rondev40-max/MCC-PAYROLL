<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\Attendance;
use App\Models\PayslipHistory;
use App\Models\EmployeeTimesheet;
use App\Models\LeaveRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Support\PasswordHash;
use App\Support\PayslipGate;
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

    /** Tabs the single-page portal knows how to open. */
    private const PORTAL_TABS = ['overview', 'attendance', 'timesheets', 'payslips', 'announcements', 'profile'];

    public function portalDashboard(Request $request)
    {
        $user        = Auth::user();
        $employeeId  = $this->resolveEmployeeId($user);
        $attendances = $this->getAttendances($employeeId);
        $stats       = $this->buildStats($attendances);
        $announcements = Announcement::orderByDesc('created_at')->take(5)->get();
        $readAnnouncementIds = AnnouncementRead::where('employee_id', $employeeId)
            ->pluck('announcement_id')->all();
        $payslips    = PayslipHistory::where('email', $user->email)
            ->orderByDesc('sent_at')->take(3)->get();
        $employee    = Employee::where('email', $user->email)
            ->orWhere('id', $employeeId)
            ->first();

        // The Timesheets tab has always rendered `$timesheets ?? []`, but this
        // action never passed it — so the tab read "No timesheets submitted yet"
        // no matter how many the employee had filed.
        $timesheets = EmployeeTimesheet::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhere('email', $user->email);
        })->orderByDesc('date')->take(50)->get();

        // Which tab to open on load. Whitelisted so a crafted ?tab= cannot be
        // reflected into the page.
        $requestedTab = (string) $request->query('tab', 'overview');
        $activeTab    = in_array($requestedTab, self::PORTAL_TABS, true) ? $requestedTab : 'overview';

        $payslipUnlocked    = PayslipGate::unlocked($request);
        $payslipUnlockedFor = PayslipGate::unlockedFor($request);
        $maskedEmail        = PayslipGate::maskEmail($user->email);

        return view('employee.dashboard-v2', compact(
            'user', 'employee', 'stats', 'announcements', 'readAnnouncementIds',
            'payslips', 'attendances', 'timesheets', 'activeTab',
            'payslipUnlocked', 'payslipUnlockedFor', 'maskedEmail'
        ));
    }

    /**
     * The portal is a single tabbed page (employee.dashboard-v2), so the
     * standalone per-section pages these routes used to render never existed —
     * every one of them raised "View [employee.payslips] not found" and 500'd.
     * They redirect into the matching dashboard tab instead, which keeps old
     * links and bookmarks working.
     */
    public function portalPayslips(Request $request)
    {
        return redirect()->route('employee.dashboard', ['tab' => 'payslips']);
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

    /** @see portalPayslips() for why this redirects rather than renders. */
    public function portalAttendance(Request $request)
    {
        return redirect()->route('employee.dashboard', ['tab' => 'attendance']);
    }

    /** @see portalPayslips() for why this redirects rather than renders. */
    public function portalTimesheets(Request $request)
    {
        return redirect()->route('employee.dashboard', ['tab' => 'timesheets']);
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

    /** @see portalPayslips() for why this redirects rather than renders. */
    public function portalAnnouncements(Request $request)
    {
        return redirect()->route('employee.dashboard', ['tab' => 'announcements']);
    }

    /**
     * Mark a single announcement as read for the current employee.
     * Called from the portal via fetch(); returns the employee's
     * updated unread count so the sidebar badge can update live.
     */
    public function markAnnouncementRead(Request $request, Announcement $announcement)
    {
        $user       = Auth::user();
        $employeeId = $this->resolveEmployeeId($user);

        AnnouncementRead::firstOrCreate([
            'announcement_id' => $announcement->id,
            'employee_id'     => $employeeId,
        ], [
            'read_at' => now(),
        ]);

        $unread = Announcement::orderByDesc('created_at')->take(5)->get()
            ->whereNotIn('id', AnnouncementRead::where('employee_id', $employeeId)->pluck('announcement_id'))
            ->count();

        return response()->json(['success' => true, 'unread' => $unread]);
    }

    /**
     * Mark every announcement currently visible to the employee as read.
     */
    public function markAllAnnouncementsRead(Request $request)
    {
        $user       = Auth::user();
        $employeeId = $this->resolveEmployeeId($user);

        $ids = Announcement::pluck('id');
        $existing = AnnouncementRead::where('employee_id', $employeeId)
            ->whereIn('announcement_id', $ids)->pluck('announcement_id');

        $rows = $ids->diff($existing)->map(fn ($id) => [
            'announcement_id' => $id,
            'employee_id'     => $employeeId,
            'read_at'         => now(),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        if ($rows->isNotEmpty()) {
            AnnouncementRead::insert($rows->all());
        }

        return response()->json(['success' => true, 'unread' => 0]);
    }

    /** @see portalPayslips() for why this redirects rather than renders. */
    public function portalProfile(Request $request)
    {
        return redirect()->route('employee.dashboard', ['tab' => 'profile']);
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

        $emailChanged = strcasecmp($data['email'], (string) $user->email) !== 0;

        // Payslips are released against whatever address is on the account, so
        // changing that address is a security-relevant act, not a profile edit:
        // an attacker on a borrowed session could otherwise point the payslip
        // code at a mailbox they own. Require the password, exactly as a
        // password change does.
        if ($emailChanged) {
            if (empty($data['current_password']) || !PasswordHash::check($data['current_password'], $user->password)) {
                return back()->withErrors([
                    'current_password' => 'Enter your current password to change the email address on your account.',
                ]);
            }
        }

        $user->name  = $data['name'];
        $user->email = $data['email'];

        if (!empty($data['new_password'])) {
            if (empty($data['current_password']) || !PasswordHash::check($data['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $user->password = Hash::make($data['new_password']);
        }

        $user->save();

        // Re-seal payslips whenever the destination address moves, so the new
        // address has to prove itself before anything is released to it.
        if ($emailChanged) {
            PayslipGate::clear($request);

            return back()->with('success', 'Profile updated. Verify your new email address again to open payslips.');
        }

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