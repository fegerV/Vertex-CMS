<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\IpFilter;

class IpFilterMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        
        // Check if IP is in blacklist
        $blacklisted = IpFilter::where('ip', $ip)
            ->where('type', 'blacklist')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();

        if ($blacklisted) {
            abort(403, 'Access denied: Your IP address has been blocked.');
        }

        // Check if whitelist mode is enabled
        $whitelistMode = config('security.ip_filter.whitelist_mode', false);
        
        if ($whitelistMode) {
            $whitelisted = IpFilter::where('ip', $ip)
                ->where('type', 'whitelist')
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->exists();

            if (!$whitelisted) {
                abort(403, 'Access denied: Your IP address is not in the whitelist.');
            }
        }

        return $next($request);
    }
}
