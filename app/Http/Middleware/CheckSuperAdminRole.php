<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if a user is authenticated and their role is 'super_admin'.
        if (Auth::check() && Auth::user()->role === 'super_admin') {
            return $next($request);
        }

        // If not, redirect to the admin dashboard with an error.
        return redirect()->route('admin.dashboard')->with('error', 'You do not have permission to perform this action.');
    }
}