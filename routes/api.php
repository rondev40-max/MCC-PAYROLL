<?php

use App\Http\Controllers\MobileAuthController;
use App\Http\Controllers\MobilePortalController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::post('/mobile/login', [MobileAuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/mobile/me', [MobileAuthController::class, 'me']);
    Route::get('/mobile/dashboard', [MobilePortalController::class, 'dashboard']);
    Route::get('/mobile/attendance', [MobilePortalController::class, 'attendance']);
    Route::get('/mobile/payslips', [MobilePortalController::class, 'payslips']);
    Route::get('/mobile/announcements', [MobilePortalController::class, 'announcements']);
    Route::get('/mobile/profile', [MobilePortalController::class, 'profile']);
});

// ==========================================
// SECURE DEPLOYMENT ENDPOINTS (Bypasses session initialization deadlock)
// ==========================================

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