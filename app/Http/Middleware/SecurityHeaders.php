<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Content Security Policy
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://js.sentry-cdn.com; " .
            "style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; " .
            "img-src 'self' data: https:; " .
            "font-src 'self' https:; " .
            "connect-src 'self' https://o1*.ingest.sentry.io wss:;"
        );

        // X-Frame-Options (Clickjacking protection)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // X-Content-Type-Options (Prevent MIME sniffing)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Referrer-Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions-Policy (Feature-Policy replacement)
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=()'
        );

        // Strict-Transport-Security (HSTS) - only in production
        if (config('app.env') === 'production') {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        return $response;
    }
}
