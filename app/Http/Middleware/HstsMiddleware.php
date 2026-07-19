<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HstsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Add the Strict-Transport-Security header only for HTTPS & production
        if ($request->isSecure() && app()->environment('production')) {
            $response->headers->set(
                'Strict-Transport-Security',
                env('HSTS_HEADER', 'max-age=63072000; includeSubDomains; preload')
            );
        }

        return $response;
    }
}
