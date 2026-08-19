<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GdprMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add GDPR banner data to shared view if it's an HTML response
        if ($request->expectsJson()) {
            return $response;
        }

        // Check if user has already given consent
        $hasConsent = $request->cookie('gdpr_consent') 
            || session()->has('gdpr_consent');

        if (!$hasConsent) {
            // Share variable for blade views
            view()->share('showGdprBanner', true);
        }

        return $response;
    }
}
