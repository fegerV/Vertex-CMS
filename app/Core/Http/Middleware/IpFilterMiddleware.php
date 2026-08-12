<?php

namespace App\Core\Http\Middleware;

use App\Core\Services\InstallationService;
use App\Models\IpFilter;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IpFilterMiddleware
{
    public function __construct(private readonly InstallationService $installation) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->installation->isInstalled()) {
            return $next($request);
        }

        $ip = $request->ip();

        if (! $ip) {
            return $next($request);
        }

        $whitelist = IpFilter::active()->whitelist()->get(['ip_address']);
        $blacklist = IpFilter::active()->blacklist()->get(['ip_address']);

        if ($whitelist->isNotEmpty()) {
            $isWhitelisted = $whitelist->contains(function ($filter) use ($ip) {
                return $this->ipMatches($ip, $filter->ip_address);
            });

            if (! $isWhitelisted) {
                return response()->view('errors.403', [
                    'message' => 'Доступ запрещён. Ваш IP-адрес не находится в белом списке.',
                ], 403);
            }
        }

        $isBlacklisted = $blacklist->contains(function ($filter) use ($ip) {
            return $this->ipMatches($ip, $filter->ip_address);
        });

        if ($isBlacklisted) {
            return response()->view('errors.403', [
                'message' => 'Доступ запрещён. Ваш IP-адрес находится в чёрном списке.',
            ], 403);
        }

        return $next($request);
    }

    protected function ipMatches(string $requestIp, string $filterIp): bool
    {
        if (str_contains($filterIp, '*')) {
            $pattern = str_replace('*', '.*', preg_quote($filterIp, '/'));

            return (bool) preg_match('/^'.$pattern.'$/', $requestIp);
        }

        return $requestIp === $filterIp;
    }
}
