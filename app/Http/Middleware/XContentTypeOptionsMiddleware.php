<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class XContentTypeOptionsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Add the X-Content-Type-Options header
        $response->headers->set(
            'X-Content-Type-Options',
            env('X_CONTENT_TYPE_OPTIONS', 'nosniff')
        );

        return $response;
    }
}
