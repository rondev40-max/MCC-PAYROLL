<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        // Check if a user is authenticated and their role is 'admin' or 'super_admin'.
        // This assumes your User model has a 'role' attribute.
        if ($user && in_array($user->role, ['admin', 'super_admin'])) {
            return $next($request);
        }

        // If the user is not authenticated or does not have the correct role,
        // redirect them to the home page with an error message.
        return redirect('/')->with('error', 'You do not have permission to access this page.');
    }
}