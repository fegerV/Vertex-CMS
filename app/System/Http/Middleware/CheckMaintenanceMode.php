<?php

namespace App\System\Http\Middleware;

use App\System\Services\MaintenanceService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function __construct(
        private readonly MaintenanceService $maintenance,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Skip maintenance for admin routes, API routes, and excluded pages
        if ($this->shouldBypass($request)) {
            return $next($request);
        }

        if (!$this->maintenance->isEnabled()) {
            return $next($request);
        }

        // Check if admin is logged in - allow access
        if ($this->maintenance->bypassForAdmins() && Auth::guard('admin')->check()) {
            return $next($request);
        }

        // Check allowed IPs
        if ($this->maintenance->shouldShowForIp($request->ip())) {
            return $next($request);
        }

        // Show maintenance page
        return $this->handleMaintenance($request);
    }

    private function shouldBypass(Request $request): bool
    {
        // Bypass for admin and API routes
        if ($request->is('admin*') || $request->is('api/*')) {
            return true;
        }

        // Check excluded pages
        $uri = $request->path();
        if ($this->maintenance->isExcluded($uri)) {
            return true;
        }

        return false;
    }

    private function handleMaintenance(Request $request): Response
    {
        $statusCode = $this->maintenance->getHttpStatusCode();

        $response = response()->view('maintenance.index', [
            'title' => $this->maintenance->getTitle(),
            'slogan' => $this->maintenance->getSlogan(),
            'text' => $this->maintenance->getText(),
            'theme' => $this->maintenance->getTheme(),
            'backgroundImage' => $this->maintenance->getBackgroundImage(),
            'backgroundBlur' => $this->maintenance->hasBackgroundBlur(),
            'logo' => $this->maintenance->getLogo(),
            'colors' => $this->maintenance->getColors(),
            'loginFormEnabled' => $this->maintenance->isLoginFormEnabled(),
            'googleAnalyticsId' => $this->maintenance->getGoogleAnalyticsId(),
        ], $statusCode);

        // Cache compatibility headers
        if ($this->maintenance->isCacheCompatibilityEnabled()) {
            $response->header('Retry-After', '3600');
            $response->header('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->header('Pragma', 'no-cache');
            $response->header('Expires', '0');
        }

        return $response;
    }
}
