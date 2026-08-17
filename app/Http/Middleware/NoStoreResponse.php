<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Marks a response as never cacheable.
 *
 * The attendance API returns one checker's departmental roster and their saved
 * time entries over plain GET requests, with no cache directives at all. This
 * host sits behind LiteSpeed cache (responses carry an x-lscache header), and a
 * shared cache is entitled to store an unmarked GET and replay it.
 *
 * Two consequences, one worse than the other:
 *
 *   - The register loads stale entries, or an intercepted HTML page arrives
 *     where JSON was expected and the dashboard reports it could not load.
 *   - A cached roster can be served to a different checker entirely, which
 *     leaks one department's personnel data to another.
 *
 * `private` alone is not enough: it permits browser storage. `no-store` is the
 * directive that forbids writing the response down anywhere.
 */
class NoStoreResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        // LiteSpeed honours its own directive even when it ignores the standard
        // ones, so state it explicitly rather than hoping.
        $response->headers->set('X-LiteSpeed-Cache-Control', 'no-cache');

        return $response;
    }
}
