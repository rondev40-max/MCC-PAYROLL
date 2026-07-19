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