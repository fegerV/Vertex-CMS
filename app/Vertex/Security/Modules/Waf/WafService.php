<?php

namespace App\Vertex\Security\Modules\Waf;

use Illuminate\Http\Request;

class WafService
{
    public function inspect(Request $request): ?array
    {
        if (in_array($request->path(), (array) config('security.waf.excluded_paths', []), true)) {
            return null;
        }

        if (! in_array(strtoupper($request->method()), (array) config('security.waf.allowed_methods', []), true)) {
            return ['rule' => 'method-not-allowed', 'value' => $request->method()];
        }

        if (strlen((string) $request->server('QUERY_STRING')) > (int) config('security.waf.max_query_length', 4096)) {
            return ['rule' => 'query-too-long', 'value' => strlen((string) $request->server('QUERY_STRING'))];
        }

        $agent = strtolower((string) $request->userAgent());
        foreach ((array) config('security.waf.blocked_user_agents', []) as $blocked) {
            if ($blocked !== '' && str_contains($agent, strtolower((string) $blocked))) {
                return ['rule' => 'blocked-user-agent', 'value' => $blocked];
            }
        }

        $input = strtolower(rawurldecode((string) $request->server('QUERY_STRING').' '.json_encode($request->all())));
        $patterns = [
            'path-traversal' => '/(?:\.\.\/|\.\.\\\\)/',
            'script-injection' => '/(?:<script\b|javascript:|onerror\s*=)/i',
            'sql-injection' => '/(?:\bunion\s+(?:all\s+)?select\b|\bsleep\s*\(|\bbenchmark\s*\()/i',
        ];

        foreach ($patterns as $rule => $pattern) {
            if (preg_match($pattern, $input) === 1) {
                return ['rule' => $rule, 'value' => null];
            }
        }

        return null;
    }
}
