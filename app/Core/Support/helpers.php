<?php

if (! function_exists('config_value')) {
    function config_value(string $key, mixed $default = null): mixed
    {
        return app(\App\Core\Services\SettingsService::class)->get($key, $default);
    }
}

$vertexSecurityAliases = [
    \Vertex\Security\SecurityServiceProvider::class => \App\Vertex\Security\SecurityServiceProvider::class,
    \Vertex\Security\Middleware\SecureHeaders::class => \App\Vertex\Security\Middleware\SecureHeaders::class,
    \Vertex\Security\Middleware\SessionGuard::class => \App\Vertex\Security\Middleware\SessionGuard::class,
    \Vertex\Security\Middleware\BasicRateLimiter::class => \App\Vertex\Security\Middleware\BasicRateLimiter::class,
    \Vertex\Security\Support\ModuleRegistry::class => \App\Vertex\Security\Support\ModuleRegistry::class,
];

foreach ($vertexSecurityAliases as $alias => $target) {
    if (! class_exists($alias, false) && class_exists($target)) {
        class_alias($target, $alias);
    }
}

