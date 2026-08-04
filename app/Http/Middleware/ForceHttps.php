<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceHttps
{
    /**
     * Redirect any non-secure requests to HTTPS in production.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $request->secure() && app()->environment('production')) {
            // preserve query string and path
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
