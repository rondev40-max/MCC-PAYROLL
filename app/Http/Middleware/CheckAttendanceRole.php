<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckAttendanceRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $hasFlag = $request->session()->has('is_attendance');
        $flagValue = $request->session()->get('is_attendance');
        $isAuthenticated = $hasFlag && $flagValue === true;

        // Debug logging
        Log::debug('CheckAttendanceRole middleware check', [
            'path' => $request->path(),
            'has_is_attendance' => $hasFlag,
            'is_attendance_value' => $flagValue,
            'is_authenticated' => $isAuthenticated,
            'session_keys' => array_keys($request->session()->all()),
            'user_id' => $request->session()->get('user_id'),
            'user_role' => $request->session()->get('user_role'),
        ]);

        if ($isAuthenticated) {
            return $next($request);
        }

        Log::warning('Attendance authentication failed - redirecting to login', [
            'path' => $request->path(),
        ]);

        return redirect('/attendance/attendlog')->with('error', 'You must log in to access attendance.');
    }
}