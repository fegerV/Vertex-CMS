<?php

namespace App\Vertex\Security\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SessionGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasSession() || ! $request->session()->isStarted()) {
            return $next($request);
        }

        if ((bool) config('security.session.regenerate', true)) {
            $this->rotateSessionToken($request);
        }

        $this->validateFingerprint($request);

        return $next($request);
    }

    private function rotateSessionToken(Request $request): void
    {
        $rotationMinutes = max(1, (int) config('security.session.rotation_minutes', 30));
        $regeneratedAt = $request->session()->get('__vertex_security.regenerated_at');

        if ($regeneratedAt && now()->diffInMinutes($regeneratedAt) > $rotationMinutes) {
            $request->session()->regenerateToken();
            $request->session()->put('__vertex_security.regenerated_at', now()->toIso8601String());
            return;
        }

        if (! $regeneratedAt) {
            $request->session()->put('__vertex_security.regenerated_at', now()->toIso8601String());
        }
    }

    private function validateFingerprint(Request $request): void
    {
        $bindUserAgent = (bool) config('security.session.bind_user_agent', true);
        $bindIp = (bool) config('security.session.bind_ip', false);

        if (! $bindUserAgent && ! $bindIp) {
            return;
        }

        $parts = [];
        if ($bindUserAgent) {
            $parts[] = (string) $request->userAgent();
        }
        if ($bindIp) {
            $parts[] = (string) $request->ip();
        }

        $fingerprint = hash('sha256', implode('|', $parts));
        $stored = $request->session()->get('__vertex_security.fingerprint');

        if (! $stored) {
            $request->session()->put('__vertex_security.fingerprint', $fingerprint);
            return;
        }

        if (! hash_equals((string) $stored, $fingerprint)) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            abort(419, 'Session validation failed.');
        }
    }
}
