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

        $csp = (string) $csp;

        if (! app()->runningUnitTests() && ! app()->environment('production') && is_file(public_path('hot'))) {
            return $this->allowViteDevServer($csp);
        }

        return $csp;
    }

    private function allowViteDevServer(string $csp): string
    {
        $viteHttpSources = ['http://localhost:5173', 'http://127.0.0.1:5173'];
        $viteWsSources = ['ws://localhost:5173', 'ws://127.0.0.1:5173'];

        $csp = $this->appendSourcesToDirective($csp, 'script-src', $viteHttpSources);
        $csp = $this->appendSourcesToDirective($csp, 'style-src', $viteHttpSources);
        $csp = $this->appendSourcesToDirective($csp, 'connect-src', array_merge(["'self'"], $viteHttpSources, $viteWsSources));

        return $csp;
    }

    private function appendSourcesToDirective(string $csp, string $directive, array $sources): string
    {
        $sources = array_values(array_unique(array_filter($sources)));
        if ($sources === []) {
            return $csp;
        }

        $directives = array_map('trim', explode(';', $csp));
        $found = false;

        foreach ($directives as $index => $entry) {
            if ($entry === '' || ! str_starts_with($entry, $directive . ' ')) {
                continue;
            }

            $existingSources = preg_split('/\s+/', trim(substr($entry, strlen($directive)))) ?: [];
            $directives[$index] = trim($directive . ' ' . implode(' ', array_values(array_unique(array_merge($existingSources, $sources)))));
            $found = true;
            break;
        }

        if (! $found) {
            $directives[] = trim($directive . ' ' . implode(' ', $sources));
        }

        return implode('; ', array_values(array_filter($directives))) . ';';
    }
}
