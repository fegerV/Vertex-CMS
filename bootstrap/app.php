<?php

use App\Core\Http\Middleware\EnsureInstalled;
use App\Core\Http\Middleware\EnsureNotInstalled;
use App\Core\Http\Middleware\GdprCookieMiddleware;
use App\Core\Http\Middleware\IpFilterMiddleware;
use App\Core\Http\Middleware\RequirePermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(EnsureInstalled::class);
        $middleware->append(\App\Core\Http\Middleware\SetLocale::class);
        $middleware->append(GdprCookieMiddleware::class);
        $middleware->append(IpFilterMiddleware::class);

        $middleware->alias([
            'vertex.not_installed' => EnsureNotInstalled::class,
            'vertex.permission' => RequirePermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->reportable(function (\Throwable $e) {
            if (class_exists(\Sentry\Laravel\Integration::class)) {
                \Sentry\Laravel\Integration::captureUnhandledException($e);
            }
        });
    })
    ->create();
