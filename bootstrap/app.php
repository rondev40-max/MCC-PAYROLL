<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

use App\Http\Middleware\HstsMiddleware;
use App\Http\Middleware\XFrameOptionsMiddleware;
use App\Http\Middleware\XContentTypeOptionsMiddleware;
use App\Http\Middleware\ReferrerPolicyMiddleware;
use App\Http\Middleware\PermissionsPolicyMiddleware;
use App\Http\Middleware\ForceHttps;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',      // <-- add this line
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // This host DOES sit behind a reverse proxy: LiteSpeed terminates TLS
        // and forwards to PHP over plain HTTP from the loopback address, which
        // is why responses carry x-lscache and requests carry
        // X-Forwarded-Proto / X-Forwarded-For.
        //
        // With no proxy trusted, $request->secure() returned false on every
        // HTTPS request, so ForceHttps below redirected all of them — and the
        // redirected request looked identical, so it redirected again. Ordinary
        // page loads survived on the LiteSpeed cache; the attendance register's
        // fetch() looped until the browser gave up, which the dashboard could
        // only report as "the register could not be loaded".
        //
        // $request->ip() was also 127.0.0.1 for everyone, which quietly pooled
        // every visitor into one rate-limit bucket and recorded the proxy in
        // logs instead of the actual client.
        //
        // Only loopback and private ranges are trusted, never '*'. LiteSpeed
        // forwards from this machine, and a remote attacker cannot make their
        // REMOTE_ADDR appear local, so forged X-Forwarded-* headers from the
        // internet are still ignored.
        $middleware->trustProxies(
            at: [
                '127.0.0.1',
                '::1',
                '10.0.0.0/8',
                '172.16.0.0/12',
                '192.168.0.0/16',
            ],
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
        );

        // ✅ Global middleware (replaces $middleware array)
        // ForceHttps runs first so nothing downstream processes a plaintext request.
        $middleware->append(ForceHttps::class);
        $middleware->append(HstsMiddleware::class);
        $middleware->append(XFrameOptionsMiddleware::class);
        $middleware->append(XContentTypeOptionsMiddleware::class);
        $middleware->append(ReferrerPolicyMiddleware::class);
        $middleware->append(PermissionsPolicyMiddleware::class);

        // ✅ Web group middleware (replaces $middlewareGroups['web'])
        $middleware->web(append: [
            \App\Http\Middleware\UpdateLastSeenAt::class,
        ]);

        // ✅ Aliases (replaces $middlewareAliases)
        $middleware->alias([
            'auth.admin'          => \App\Http\Middleware\CheckAdminRole::class,
            'auth.attendance'     => \App\Http\Middleware\CheckAttendanceRole::class,
            'auth.superadmin'     => \App\Http\Middleware\CheckSuperAdminRole::class,
            'role'                => \App\Http\Middleware\RoleMiddleware::class,
            'log.employee.portal' => \App\Http\Middleware\LogEmployeePortalAccess::class,
            'payslip.unlocked'    => \App\Http\Middleware\EnsurePayslipUnlocked::class,
            'no-store'            => \App\Http\Middleware\NoStoreResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();