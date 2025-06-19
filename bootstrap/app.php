<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies (since we're behind AWS ALB)
        $middleware->trustProxies(at: [
            'headers' => \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
                        \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
                        \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
                        \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO |
                        \Illuminate\Http\Request::HEADER_X_FORWARDED_AWS_ELB,
            'proxies' => '*',
        ]);

        // Session and CSRF protection
        $middleware->web(\App\Http\Middleware\StartSessionMiddleware::class);
        $middleware->web(\Illuminate\Session\Middleware\StartSession::class);
        $middleware->web(\Illuminate\View\Middleware\ShareErrorsFromSession::class);
        $middleware->web(\App\Http\Middleware\VerifyCsrfToken::class);
        $middleware->web(\Illuminate\Routing\Middleware\SubstituteBindings::class);
        
        // Role/Permission middleware
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
