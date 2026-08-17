<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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

        // NOTE: this app no longer runs behind Vercel/Railway's edge proxy,
        // so there's nothing forwarding X-Forwarded-Proto that needs trusting.
        // Hostinger terminates HTTPS directly, so $request->secure() already
        // reflects the real connection without any proxy trust configured.
        // Blindly trusting '*' here (any IP) is also a spoofing risk, and on
        // this host it was the likely cause of ForceHttps below occasionally
        // misjudging a request as insecure and injecting an extra redirect
        // hop right after login — which silently ate the one-shot session
        // flash data the OTP modal depends on.

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
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();