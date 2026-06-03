<?php

namespace App\Seo\Http\Middleware;

use App\Seo\Services\RedirectResolver;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveSeoRedirect
{
    public function __construct(
        private readonly RedirectResolver $resolver,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $redirect = $this->resolver->resolve($request);
        } catch (QueryException) {
            return $next($request);
        }

        if (! $redirect) {
            return $next($request);
        }

        $redirect->increment('hits');

        return redirect($redirect->to_url, $redirect->status_code);
    }
}
