<?php

namespace App\Vertex\Security\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', (string) config('security.headers.content_type_options', 'nosniff'));
        $response->headers->set('X-Frame-Options', (string) config('security.headers.frame_options', 'DENY'));
        $response->headers->set('Referrer-Policy', (string) config('security.headers.referrer_policy', 'strict-origin-when-cross-origin'));
        $response->headers->set('Permissions-Policy', (string) config('security.headers.permissions_policy', 'camera=(), microphone=(), geolocation=()'));

        $csp = $this->resolveCsp($request);
        if (filled($csp)) {
            $response->headers->set('Content-Security-Policy', (string) $csp);
        }

        $hstsEnabled = (bool) config('security.headers.hsts.enabled', true);
        if ($hstsEnabled && app()->environment('production')) {
            $value = 'max-age='.(int) config('security.headers.hsts.max_age', 31536000);

            if (config('security.headers.hsts.include_subdomains', true)) {
                $value .= '; includeSubDomains';
            }

            if (config('security.headers.hsts.preload', true)) {
                $value .= '; preload';
            }

            $response->headers->set('Strict-Transport-Security', $value);
        }

        return $response;
    }

    private function resolveCsp(Request $request): ?string
    {
        $csp = config('security.headers.csp');

        if (! filled($csp)) {
            return null;
        }

        return (string) $csp;
    }
}
