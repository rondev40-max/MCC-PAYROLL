<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogEmployeePortalAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('Employee portal accessed', [
            'user_id' => $request->user()?->id,
            'url'     => $request->fullUrl(),
            'ip'      => $request->ip(),
            'agent'   => $request->userAgent(),
        ]);

        return $next($request);
    }
}