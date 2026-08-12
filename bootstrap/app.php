<?php

use App\Core\Http\Middleware\EnsureInstalled;
use App\Core\Http\Middleware\EnsureNotInstalled;
use App\Core\Http\Middleware\GdprCookieMiddleware;
use App\Core\Http\Middleware\IpFilterMiddleware;
use App\Core\Http\Middleware\RequirePermission;
use App\Security\Login\Http\Middleware\PasswordExpiryMiddleware;
use App\Seo\Http\Middleware\ResolveSeoRedirect;
use App\System\Http\Middleware\CheckMaintenanceMode;
use App\Support\Api\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
        commands: __DIR__.'/../routes/console.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(EnsureInstalled::class);
        $middleware->append(\App\Core\Http\Middleware\SetLocale::class);
        $middleware->append(GdprCookieMiddleware::class);
        $middleware->append(IpFilterMiddleware::class);
        $middleware->append(CheckMaintenanceMode::class);
        $middleware->append(ResolveSeoRedirect::class);

        $middleware->redirectGuestsTo(function (Request $request) {
            return $request->is('admin', 'admin/*') ? route('admin.login') : null;
        });

        $middleware->alias([
            'vertex.not_installed' => EnsureNotInstalled::class,
            'vertex.permission' => RequirePermission::class,
            'maintenance.check' => CheckMaintenanceMode::class,
            'login.password.expiry' => PasswordExpiryMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request, \Throwable $exception): bool {
            return ApiResponse::isApiRequest($request);
        });

        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! ApiResponse::isApiRequest($request)) {
                return null;
            }

            return ApiResponse::validation($exception->errors(), $exception->getMessage());
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (! ApiResponse::isApiRequest($request)) {
                return null;
            }

            return ApiResponse::error('unauthenticated', 'Authentication is required.', status: 401);
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            if (! ApiResponse::isApiRequest($request)) {
                return null;
            }

            return ApiResponse::error('forbidden', $exception->getMessage() ?: 'This action is forbidden.', status: 403);
        });

        $exceptions->render(function (\Throwable $exception, Request $request) {
            if (! ApiResponse::isApiRequest($request)) {
                return null;
            }

            $status = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
            $message = $status >= 500
                ? 'Server error.'
                : ($exception->getMessage() ?: 'Request failed.');
            $code = match ($status) {
                401 => 'unauthenticated',
                403 => 'forbidden',
                404 => 'not_found',
                429 => 'rate_limited',
                default => $status >= 500 ? 'server_error' : 'request_failed',
            };

            return ApiResponse::error($code, $message, status: $status);
        });

        $exceptions->reportable(function (\Throwable $e) {
            if (class_exists(\Sentry\Laravel\Integration::class)) {
                \Sentry\Laravel\Integration::captureUnhandledException($e);
            }
        });
    })
    ->create();
