<?php

use App\Core\Http\Middleware\EnsureInstalled;
use App\Core\Http\Middleware\EnsureNotInstalled;
use App\Core\Http\Middleware\RequirePermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(EnsureInstalled::class);

        $middleware->alias([
            'vertex.not_installed' => EnsureNotInstalled::class,
            'vertex.permission' => RequirePermission::class,
        ]);
    })
    ->create();
