<?php

return [
    'dsn' => env('SENTRY_LARAVEL_DSN'),
    'breadcrumbs' => [
        'cache_transactions' => true,
        'http_client_requests' => true,
        'console_commands' => true,
        'db_query' => true,
        'logs' => true,
        'redis_transactions' => true,
    ],
    'tracing' => [
        'enabled' => env('SENTRY_TRACING_ENABLED', true),
        'default_integrations' => true,
    ],
    'send_default_pii' => env('SENTRY_SEND_DEFAULT_PII', false),
    'max_breadcrumbs' => env('SENTRY_MAX_BREADCRUMBS', 50),
    'environment' => env('SENTRY_ENVIRONMENT', env('APP_ENV')),
    'release' => env('SENTRY_RELEASE', env('VERTEX_VERSION', '0.1.0')),
];
