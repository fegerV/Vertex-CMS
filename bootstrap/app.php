<?php

use App\Core\Http\Middleware\EnsureInstalled;
use App\Core\Http\Middleware\EnsureNotInstalled;
use App\Core\Http\Middleware\RequirePermission;
use App\Support\Api\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
    })
    ->create();
