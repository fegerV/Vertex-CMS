<?php

namespace App\Vertex\Security\Modules\GeoIp;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GeoIpMiddleware
{
    public function __construct(private readonly GeoIpService $geoIp) {}

    public function handle(Request $request, Closure $next): Response
    {
        $location = $this->geoIp->locate((string) $request->ip(), $request);
        $request->attributes->set('vertex.geoip', $location);

        if (! $this->geoIp->isAllowed($location)) {
            abort(403, 'This service is not available in your region.');
        }

        return $next($request);
    }
}
