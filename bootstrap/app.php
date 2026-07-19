<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Http\Middleware\HstsMiddleware;
use App\Http\Middleware\XFrameOptionsMiddleware;
use App\Http\Middleware\XContentTypeOptionsMiddleware;
use App\Http\Middleware\ReferrerPolicyMiddleware;
use App\Http\Middleware\PermissionsPolicyMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',      // <-- add this line
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // ✅ Global middleware (replaces $middleware array)
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
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();