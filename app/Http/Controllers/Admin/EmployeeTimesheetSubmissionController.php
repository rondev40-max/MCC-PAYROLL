<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeTimesheet;
use Illuminate\Http\Request;

class EmployeeTimesheetSubmissionController extends Controller
{
    public function index()
    {
        $submissions = EmployeeTimesheet::query()
            ->where('status', 'Submitted')
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.employee-timesheet-submissions', [
            'submissions' => $submissions,
        ]);
    }

    public function print()
    {
        $submissions = EmployeeTimesheet::query()
            ->where('status', 'Submitted')
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.employee-timesheet-submissions-print', [
            'submissions' => $submissions,
        ]);
    }

    public function approve(EmployeeTimesheet $submission)
    {
        $submission->status = 'Approved';
        $submission->save();

        return back()->with('success', 'Timesheet submission approved.');
    }

    public function reject(EmployeeTimesheet $submission)
    {
        $submission->status = 'Rejected';
        $submission->save();

        return back()->with('success', 'Timesheet submission rejected.');
    }
}

