<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckEmployeeRole
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || ($user->role ?? null) !== 'employee') {
            return redirect('/')
                ->with('error', 'Access denied.');
        }

        return $next($request);
    }
}

