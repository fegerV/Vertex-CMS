<?php

namespace App\Core\Http\Middleware;

use App\Core\Services\InstallationService;
use App\Models\GdprSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GdprCookieMiddleware
{
    public function __construct(private readonly InstallationService $installation) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldShowBanner($request)) {
            return $response;
        }

        $settings = GdprSetting::getActive();

        if ($settings->enabled) {
            $response->header('X-GDPR-Banner', 'required');
        }

        return $response;
    }

    protected function shouldShowBanner(Request $request): bool
    {
        if (! $this->installation->isInstalled()) {
            return false;
        }

        if ($request->is('admin/*') || $request->is('api/*')) {
            return false;
        }

        return ! $request->cookie('gdpr_accepted');
    }
}
