<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class UpdateLastSeenAt
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $now = Carbon::now();

            // ✅ Check if 'last_seen_at' column actually exists (prevents SQL errors)
            if (Schema::hasColumn('users', 'last_seen_at')) {

                // Update only if null or more than 5 minutes since last update
                if (is_null($user->last_seen_at) || Carbon::parse($user->last_seen_at)->diffInMinutes($now) >= 5) {
                    $user->last_seen_at = $now;
                    $user->save();
                }
            }
        }

        return $next($request);
    }
}
