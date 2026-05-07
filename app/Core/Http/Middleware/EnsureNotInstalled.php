<?php

namespace App\Core\Http\Middleware;

use App\Core\Services\InstallationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotInstalled
{
    public function __construct(
        private readonly InstallationService $installation,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        abort_if($this->installation->isInstalled(), 403);

        return $next($request);
    }
}

