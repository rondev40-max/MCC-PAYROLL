<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FulltimeTimesheetController;
use App\Http\Controllers\ParttimeTimesheetController;
use App\Http\Controllers\StaffTimesheetController;
use App\Http\Controllers\UtilityTimesheetController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OtpVerificationController;
use App\Http\Controllers\BsitController; // Course Attendance
use App\Http\Controllers\BsbaController; // Course Attendance
use App\Http\Controllers\BshmController; // Course Attendance
use App\Http\Controllers\EducationController; // Course Attendance
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\SalaryController;

// --- PUBLIC & AUTHENTICATION ROUTES ---

// Main Landing Page (Login Form)
Route::get('/', function () {
    return view('index');
})->name('index'); 

// Handle Login Submission
Route::post('/', [LoginController::class, 'authenticate'])->name('login.submit');


// OTP Verification Routes
Route::get('/otp/verify', [OtpVerificationController::class, 'showVerificationForm'])->name('otp.verify.form');
Route::post('/otp/verify', [OtpVerificationController::class, 'verify'])->name('otp.verify');
Route::get('/otp/resend', [OtpVerificationController::class, 'resendOtp'])->name('otp.resend');

// Registration Routes
Route::get('/register', function () {
    return view('auth.register');
})->name('register.form');
Route::post('/register', [RegisterController::class, 'store'])->middleware(app()->environment('production') ? 'throttle:5,1' : []);

// Email Verification Route
Route::get('/email/verify/{token}', [App\Http\Controllers\VerificationController::class, 'verify'])->name('user.verify');
Route::post('/email/resend', [App\Http\Controllers\VerificationController::class, 'resend'])->name('verification.resend');

// Admin Login Routes (Separate View) — Obscured path for security
Route::get('/portal/management-login', function () {
    return view('admin.login');
})->name('admin.login.form');
Route::post('/portal/management-login', [LoginController::class, 'authenticate'])->name('admin.login')->middleware(app()->environment('production') ? 'throttle:15,1' : []);

// Employee Login Routes (Separate View)
Route::get('/employee/login', function () {
    return view('employee.login');
})->name('employee.login.form');
Route::post('/employee/login', [LoginController::class, 'authenticate'])->name('employee.login')->middleware(app()->environment('production') ? 'throttle:15,1' : []);

// Attendance Login Routes (Separate View)
Route::get('/attendance/attendlog', function () {
    return view('attendance.attendlog');
})->name('attendance.attendlog.form');
Route::post('/attendance/attendlog', [LoginController::class, 'authenticate'])->name('attendance.attendlog')->middleware(app()->environment('production') ? 'throttle:3,1' : []);

// --- AUTHENTICATED USER ROUTES (GENERIC) ---

Route::middleware(['auth'])->group(function () {

    // LOGOUT FIX
    Route::post('/logout', function (Request $request) {
        $user = Auth::user();
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/')->with('logout_success', 'You have been logged out successfully.');
    })->name('logout');

    // ✅ EVALUATION FORM - ALL Authenticated Users
    // NOTE: employee evaluation lives under /employee, not under the generic authenticated group.
    // Keeping /evaluation route could break admin/employee navigation consistency.
    // Route::get('/evaluation', [\App\Http\Controllers\EvaluationController::class, 'showEmployeeForm'])->name('evaluation.form');
    Route::post('/evaluation', [\App\Http\Controllers\EvaluationController::class, 'storeEvaluation'])->name('evaluation.store');



    // GENERIC DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/api/attendance-summary', [DashboardController::class, 'attendanceSummary']);
Route::get('/api/payroll-monthly',    [DashboardController::class, 'payrollMonthly']);

    // Employee Management
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    
    // Master List
    Route::get('/master-list', [DashboardController::class, 'masterList'])->name('master.list');
    Route::get('/master-list/add', [DashboardController::class, 'masterListAddForm'])->name('master.list.add');
    Route::post('/master-list/add', [DashboardController::class, 'masterListAddStore'])->name('master.list.add.store');
    Route::get('/master-list/{id}/edit', [DashboardController::class, 'editMasterList'])->name('master.list.edit');
    Route::put('/master-list/{id}', [DashboardController::class, 'updateMasterList'])->name('master.list.update');
    Route::post('/master-list/delete-selected', [DashboardController::class, 'deleteSelected'])->name('master.list.delete');
    Route::post('/master-list/print-data', [DashboardController::class, 'getPrintData'])->name('master.list.print');
    
    // Timesheet API/Resources
    Route::get('/search', [DashboardController::class, 'search'])->name('search');
    Route::get('/api/employee-stats', [DashboardController::class, 'getEmployeeStats'])->name('employee.stats');
    Route::get('/api/employee-stats/detailed', [DashboardController::class, 'getDetailedEmployeeStats'])->name('employee.stats.detailed');
    Route::get('/api/instructors-by-rate', [DashboardController::class, 'getInstructorsByRate'])->name('instructors.by.rate');
    
    // Full-Time Timesheet Resource
  // 1. Resource Route
Route::resource('fulltime', FulltimeTimesheetController::class)->except(['show']);
// 2. Custom Routes
Route::get('/fulltime/print', [FulltimeTimesheetController::class, 'printAll'])->name('fulltime.print');
Route::post('/fulltime/{id}/update-field', [FulltimeTimesheetController::class, 'updateField'])->name('fulltime.update.field');

    // Part-Time Timesheet Resource
    Route::resource('parttime', ParttimeTimesheetController::class);
    Route::get('/parttime/print/all', [ParttimeTimesheetController::class, 'printAll'])->name('parttime.print');
    Route::post('/parttime/{id}/update-day', [ParttimeTimesheetController::class, 'updateDay'])->name('parttime.update.day');
    Route::post('/parttime/{id}/update-field', [ParttimeTimesheetController::class, 'updateField'])->name('parttime.update.field');
    Route::post('/parttime/update-hours', [ParttimeTimesheetController::class, 'updateHours'])->name('parttime.update.hours');
    
    // Staff Timesheet Resource
    Route::resource('staff', StaffTimesheetController::class);
    Route::get('/staff/print/all', [StaffTimesheetController::class, 'printAll'])->name('staff.print');
    Route::post('/staff/{id}/update-day', [StaffTimesheetController::class, 'updateDay'])->name('staff.update.day');
    Route::post('/staff/{id}/update-field', [StaffTimesheetController::class, 'updateField'])->name('staff.update.field');

    // Utility Timesheet Resource
    Route::resource('utility', \App\Http\Controllers\UtilityTimesheetController::class)->except(['show']);
    Route::get('/utility/print', [\App\Http\Controllers\UtilityTimesheetController::class, 'printAll'])->name('utility.print');
    Route::post('/utility/{id}/update-field', [\App\Http\Controllers\UtilityTimesheetController::class, 'updateField'])->name('utility.update.field');

    // Department Routes
    Route::resource('departments', DepartmentController::class);
    Route::get('/timesheets', [FulltimeTimesheetController::class, 'index'])->name('timesheets.index');

    // New Routes for Holiday Management
    Route::resource('holidays', App\Http\Controllers\HolidayController::class);

});
// CSRF token refresh endpoint (prevents 419 on reCAPTCHA async submit)
Route::get('/csrf-refresh', function () {
    return response()->json(['token' => csrf_token()]);
});


// --- ADMIN ROUTES (Protected by auth.admin Middleware) ---

Route::middleware(['auth.admin'])->prefix('admin')->name('admin.')->group(function () {

Route::get('/evaluation/results', [\App\Http\Controllers\Admin\EvaluationController::class, 'evaluationResults'])
        ->name('evaluation.results');
    
    // Admin dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Employee timesheet submissions (Submitted by employee portal)
    Route::get('/employee-timesheets/submissions', [\App\Http\Controllers\Admin\EmployeeTimesheetSubmissionController::class, 'index'])
        ->name('employee.timesheets.submissions');

    Route::get('/employee-timesheets/submissions/print', [\App\Http\Controllers\Admin\EmployeeTimesheetSubmissionController::class, 'print'])
        ->name('employee-timesheets.submissions.print');

    Route::post('/employee-timesheets/submissions/{submission}/approve', [\App\Http\Controllers\Admin\EmployeeTimesheetSubmissionController::class, 'approve'])
        ->name('employee-timesheets.submissions.approve');

    Route::post('/employee-timesheets/submissions/{submission}/reject', [\App\Http\Controllers\Admin\EmployeeTimesheetSubmissionController::class, 'reject'])
        ->name('employee-timesheets.submissions.reject');


    // Activity Log
    Route::get('/activity-log', [AdminController::class, 'activityLog'])->name('activity-log'); 
    
    // User Management 
    Route::get('/user-management', [UserController::class, 'index'])->name('user-management');
    Route::delete('/user-management/{user}', [UserController::class, 'destroy'])->name('user.delete');

    // Super Admin Only
    Route::middleware('auth.superadmin')->group(function () {
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
    });

    // Payslip Management
    Route::post('/send-payslips', [AdminController::class, 'sendPayslips'])->name('send.payslips');
    Route::get('/history', [AdminController::class, 'history'])->name('history');
    Route::get('/history/trash', [AdminController::class, 'trash'])->name('history.trash');
    

    // ⭐️ FIX: DAPAT MAUNA ANG SPECIFIC DELETE ROUTES ⭐️
    // 1. Mass Soft Delete (Delete Selected)
    Route::delete('/history/mass-delete', [AdminController::class, 'massSoftDelete'])->name('history.mass.delete');
    // 2. Soft Delete All (Para sa history.blade)
    Route::delete('/history/delete-all', [AdminController::class, 'historySoftDeleteAll'])->name('history.delete.all');
    // 3. Force Delete All (Para sa trash.blade)
    Route::delete('/history/force-delete-all', [AdminController::class, 'historyForceDeleteAll'])->name('history.force.delete.all');

    // ⭐️ WILDCARD DELETE ROUTES (DAPAT HULI) ⭐️
    Route::delete('/history/{id}', [AdminController::class, 'historySoftDelete'])->name('history.delete'); 
    
    Route::patch('/history/{id}/restore', [AdminController::class, 'historyRestore'])->name('history.restore');
    Route::delete('/history/{id}/force', [AdminController::class, 'historyForceDelete'])->name('history.force');
    
    // Salary Adjustment route
    Route::get('/salary-adjustment', [SalaryController::class, 'adjustmentForm'])
        ->name('salary.adjustment');

    // Tax & Government Deductions Routes
    Route::get('/deductions', [\App\Http\Controllers\Admin\DeductionController::class, 'index'])
        ->name('deductions.index');
    Route::post('/deductions/update-settings', [\App\Http\Controllers\Admin\DeductionController::class, 'updateSettings'])
        ->name('deductions.update-settings');
    Route::post('/deductions/apply', [\App\Http\Controllers\Admin\DeductionController::class, 'applyDeductions'])
        ->name('deductions.apply');
    Route::get('/deductions/summary', [\App\Http\Controllers\Admin\DeductionController::class, 'summary'])
        ->name('deductions.summary');

    // ✅ Evaluation Results - ADMIN ONLY
    Route::get('/evaluation/results', [\App\Http\Controllers\Admin\EvaluationController::class, 'evaluationResults'])->name('evaluation.results');

    Route::get('/payroll-history', [AdminController::class, 'payrollHistory'])->name('payroll.history');
    Route::get('/payroll-history/export', [AdminController::class, 'exportPayrollHistory'])->name('payroll.export');
});


// --- EMPLOYEE PORTAL ROUTES (Protected by auth.employee Middleware) ---

Route::middleware(['auth', 'role:employee', 'log.employee.portal'])->prefix('employee')->name('employee.')->group(function () {

    Route::get('/dashboard', [EmployeeController::class, 'portalDashboard'])->name('dashboard');

    Route::get('/evaluation', [\App\Http\Controllers\EvaluationController::class, 'showEmployeeForm'])
        ->name('evaluation.form');   // full name: employee.evaluation.form
 
    Route::post('/evaluation', [\App\Http\Controllers\EvaluationController::class, 'storeEvaluation'])
        ->name('evaluation.store'); 

    // Payslips (used by employee/dashboard.blade.php via fetch & download)
    Route::get('/payslips', [EmployeeController::class, 'portalPayslips'])->name('payslips');
    Route::get('/payslips/{payslip}', [EmployeeController::class, 'portalPayslipJson'])->name('payslip.json');
    Route::get('/payslips/{payslip}/download', [EmployeeController::class, 'portalPayslipDownload'])->name('payslip.download');
    Route::get('/payslips/{payslip}/view', [EmployeeController::class, 'portalPayslipView'])->name('payslip.view');

    // Attendance
    Route::get('/attendance', [EmployeeController::class, 'portalAttendance'])->name('attendance');

    // Timesheets
    Route::get('/timesheets', [EmployeeController::class, 'portalTimesheets'])->name('timesheets');
    Route::post('/timesheets', [EmployeeController::class, 'portalStoreTimesheet'])->name('timesheets.store');

    // Announcements
    Route::get('/announcements', [EmployeeController::class, 'portalAnnouncements'])->name('announcements');

    // Profile
    Route::get('/profile', [EmployeeController::class, 'portalProfile'])->name('profile');
    Route::post('/profile', [EmployeeController::class, 'portalUpdateProfile'])->name('profile.update');
});


// --- ATTENDANCE ROUTES (Protected by auth.attendance Middleware) ---

// --- ATTENDANCE ROUTES ---
Route::middleware(['auth.attendance'])->prefix('attendance')->name('attendance.')->group(function () {

    Route::get('/dashboard', [AttendanceController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [AttendanceController::class, 'logout'])->name('logout');

    Route::get('/api/course-counts', [AttendanceController::class, 'getCourseCounts']);
    Route::get('/api/attendance-data/{course}', [AttendanceController::class, 'getAttendanceData']);
    Route::post('/api/save-attendance', [AttendanceController::class, 'saveAttendance']);
    Route::post('/api/save-attendance-history', [AttendanceController::class, 'saveAttendanceHistory']);
    Route::post('/api/bulk-delete-attendance', [AttendanceController::class, 'bulkDeleteAttendance']);
});

// Attendance Password Reset Routes
Route::prefix('attendance')->name('attendance.')->group(function () {
    Route::get('/forgot-password', [AttendanceController::class, 'showForgotForm'])->name('forgot.form');
    Route::post('/forgot-password', [AttendanceController::class, 'sendOtp'])->name('forgot.send')->middleware(app()->environment('production') ? 'throttle:5,1' : []);
    Route::get('/reset-password', [AttendanceController::class, 'showResetForm'])->name('reset.form');
    Route::post('/verify-otp', [AttendanceController::class, 'verifyOtp'])->name('verify')->middleware(app()->environment('production') ? 'throttle:5,1' : []);
    Route::get('/change-password', [AttendanceController::class, 'showChangePasswordForm'])->name('change.form');
    Route::post('/reset-password', [AttendanceController::class, 'resetPassword'])->name('reset.submit')->middleware(app()->environment('production') ? 'throttle:5,1' : []);
});

// Department Attendance Checker Routes (Public or use different middleware)
Route::get('/bsit', [BsitController::class, 'index'])->name('bsit.index');
Route::get('/bsba', [BsbaController::class, 'index'])->name('bsba.index');
Route::get('/bshm', [BshmController::class, 'index'])->name('bshm.index');
Route::get('/education', [EducationController::class, 'index'])->name('education.index');

Route::get('/terms', function () {
    return view('terms');
})->name('terms');

// Database Migration Route (Securely protected by a token for Vercel/Railway)
Route::get('/deploy/migrate', function () {
    if (request()->query('token') !== env('DEPLOYMENT_TOKEN', 'some-very-long-and-secure-token-here')) {
        abort(403, 'Unauthorized');
    }
    
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return response()->json([
            'status' => 'success',
            'output' => \Illuminate\Support\Facades\Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

// Database Seed Route (Securely protected by a token for Vercel/Railway)
Route::get('/deploy/seed', function () {
    if (request()->query('token') !== env('DEPLOYMENT_TOKEN', 'some-very-long-and-secure-token-here')) {
        abort(403, 'Unauthorized');
    }
    
    try {
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        return response()->json([
            'status' => 'success',
            'output' => \Illuminate\Support\Facades\Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

// Database Fresh Migrate Route (Securely protected by a token for Vercel/Railway)
Route::get('/deploy/fresh', function () {
    if (request()->query('token') !== env('DEPLOYMENT_TOKEN', 'some-very-long-and-secure-token-here')) {
        abort(403, 'Unauthorized');
    }
    
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);
        return response()->json([
            'status' => 'success',
            'output' => \Illuminate\Support\Facades\Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});