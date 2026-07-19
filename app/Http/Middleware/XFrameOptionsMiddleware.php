<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class XFrameOptionsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Add the X-Frame-Options header to block iframe embedding
        $response->headers->set(
            'X-Frame-Options',
            env('X_FRAME_OPTIONS', 'SAMEORIGIN')
        );

        return $response;
    }
}
