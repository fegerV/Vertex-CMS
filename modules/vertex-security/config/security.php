<?php

return [
    'enabled' => env('VERTEX_SECURITY_ENABLED', true),

    'dashboard' => [
        'enabled' => env('VERTEX_SECURITY_DASHBOARD_ENABLED', true),
    ],

    'pipeline' => [
        'log_async' => env('VERTEX_SECURITY_LOG_ASYNC', true),
        'cache_fallback' => env('VERTEX_SECURITY_CACHE_FALLBACK', 'file'),
        'queue_fallback' => env('VERTEX_SECURITY_QUEUE_FALLBACK', 'database'),
    ],

    'modules' => [
        'login' => [
            'enabled' => env('VERTEX_SECURITY_LOGIN_ENABLED', true),
            'driver' => 'fortify',
            'rate_limiter' => 'login',
            'two_factor' => [
                'totp' => true,
                'email' => false,
                'passkeys' => false,
            ],
        ],
        'waf' => [
            'enabled' => env('VERTEX_SECURITY_WAF_ENABLED', false),
            'cloudflare_sync' => false,
            'geoip' => false,
        ],
        'headers' => [
            'enabled' => env('VERTEX_SECURITY_HEADERS_ENABLED', true),
            'preset' => env('VERTEX_SECURITY_HEADERS_PRESET', 'relaxed'),
        ],
        'audit' => [
            'enabled' => env('VERTEX_SECURITY_AUDIT_ENABLED', true),
            'retention_days' => (int) env('VERTEX_SECURITY_AUDIT_RETENTION_DAYS', 90),
        ],
        'integrity' => [
            'enabled' => env('VERTEX_SECURITY_INTEGRITY_ENABLED', false),
            'baseline_path' => storage_path('security/baseline.json'),
        ],
        'password' => [
            'enabled' => env('VERTEX_SECURITY_PASSWORD_ENABLED', true),
            'history' => 5,
            'expiry_days' => 90,
            'hibp' => false,
        ],
        'scanner' => [
            'enabled' => env('VERTEX_SECURITY_SCANNER_ENABLED', false),
            'quarantine_disk' => 'quarantine',
        ],
        'api_sec' => [
            'enabled' => env('VERTEX_SECURITY_API_ENABLED', true),
            'token_rotation' => true,
        ],
    ],
];
