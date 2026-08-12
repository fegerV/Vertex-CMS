<?php

namespace App\Vertex\Security\Modules\Waf;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class WafMiddleware
{
    public function __construct(private readonly WafService $waf) {}

    public function handle(Request $request, Closure $next): Response
    {
        $finding = $this->waf->inspect($request);
        if ($finding === null) {
            return $next($request);
        }

        Log::warning('Vertex WAF detected a suspicious request.', [
            'rule' => $finding['rule'], 'ip' => $request->ip(), 'path' => $request->path(),
        ]);

        if (config('security.waf.mode', 'block') === 'monitor') {
            return $next($request);
        }

        abort(403, 'Request blocked by security policy.');
    }
}
