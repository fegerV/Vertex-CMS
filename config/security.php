<?php

return [
    'core' => true,

    'headers' => [
        'csp' => env('SECURITY_CSP', "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:;"),
        'frame_options' => env('SECURITY_FRAME_OPTIONS', 'DENY'),
        'content_type_options' => env('SECURITY_CONTENT_TYPE_OPTIONS', 'nosniff'),
        'referrer_policy' => env('SECURITY_REFERRER_POLICY', 'strict-origin-when-cross-origin'),
        'permissions_policy' => env('SECURITY_PERMISSIONS_POLICY', 'camera=(), microphone=(), geolocation=()'),
        'hsts' => [
            'enabled' => env('SECURITY_HSTS', true),
            'max_age' => (int) env('SECURITY_HSTS_MAX_AGE', 31536000),
            'include_subdomains' => env('SECURITY_HSTS_INCLUDE_SUBDOMAINS', true),
            'preload' => env('SECURITY_HSTS_PRELOAD', true),
        ],
    ],

    'rate_limiter' => [
        'limit' => env('SECURITY_RATE_LIMIT', '60/min'),
        'fallback_limit' => env('SECURITY_RATE_LIMIT_FALLBACK', '30/min'),
        'key_prefix' => env('SECURITY_RATE_LIMIT_PREFIX', 'vertex-security:core'),
    ],

    'session' => [
        'secure' => env('SESSION_SECURE_COOKIE', true),
        'http_only' => true,
        'same_site' => 'lax',
        'regenerate' => env('SECURITY_SESSION_REGENERATE', true),
        'rotation_minutes' => (int) env('SECURITY_SESSION_ROTATION_MINUTES', 30),
        'bind_user_agent' => env('SECURITY_SESSION_BIND_USER_AGENT', true),
        'bind_ip' => env('SECURITY_SESSION_BIND_IP', false),
    ],

    'password_policy' => [
        'min_length' => (int) env('SECURITY_PASSWORD_MIN_LENGTH', 12),
        'require_mixed_case' => env('SECURITY_PASSWORD_REQUIRE_MIXED_CASE', false),
        'require_numbers' => env('SECURITY_PASSWORD_REQUIRE_NUMBERS', true),
        'require_symbols' => env('SECURITY_PASSWORD_REQUIRE_SYMBOLS', false),
        'uncompromised' => env('SECURITY_PASSWORD_UNCOMPROMISED', false),
    ],

    'audit' => [
        'enabled' => env('SECURITY_AUDIT_ENABLED', true),
        'driver' => env('SECURITY_AUDIT_DRIVER', 'log'),
        'channel' => env('SECURITY_AUDIT_CHANNEL', 'stack'),
    ],

    'fallback' => [
        'cache_driver' => env('SECURITY_CACHE_FALLBACK', 'file'),
        'queue_driver' => env('SECURITY_QUEUE_FALLBACK', 'database'),
        'geoip_source' => env('SECURITY_GEOIP_SOURCE', 'local_csv'),
    ],

    'modules' => [
        'waf' => env('SECURITY_WAF', false),
        'geoip' => env('SECURITY_GEOIP', false),
        'integrity' => env('SECURITY_INTEGRITY', true),
        'hibp' => env('SECURITY_HIBP', false),
        'cloudflare' => env('SECURITY_CLOUDFLARE', false),
        'scanner' => env('SECURITY_SCANNER', true),
        'alerts' => env('SECURITY_ALERTS', true),
    ],

    'waf' => [
        'mode' => env('SECURITY_WAF_MODE', 'block'),
        'allowed_methods' => ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'max_query_length' => (int) env('SECURITY_WAF_MAX_QUERY_LENGTH', 4096),
        'blocked_user_agents' => array_values(array_filter(array_map('trim', explode(',', (string) env('SECURITY_WAF_BLOCKED_USER_AGENTS', 'sqlmap,nikto,acunetix'))))),
        'excluded_paths' => ['up'],
    ],

    'geoip' => [
        'trusted_headers' => env('SECURITY_GEOIP_TRUSTED_HEADERS', false),
        'allowed_countries' => array_values(array_filter(array_map('strtoupper', array_map('trim', explode(',', (string) env('SECURITY_GEOIP_ALLOWED_COUNTRIES', '')))))),
        'blocked_countries' => array_values(array_filter(array_map('strtoupper', array_map('trim', explode(',', (string) env('SECURITY_GEOIP_BLOCKED_COUNTRIES', '')))))),
        'local_database' => env('SECURITY_GEOIP_DATABASE', storage_path('app/security/geoip.csv')),
    ],

    'hibp' => [
        'endpoint' => env('SECURITY_HIBP_ENDPOINT', 'https://api.pwnedpasswords.com/range'),
        'timeout' => (int) env('SECURITY_HIBP_TIMEOUT', 5),
        'minimum_occurrences' => (int) env('SECURITY_HIBP_MINIMUM_OCCURRENCES', 1),
    ],

    'cloudflare' => [
        'api_token' => env('CLOUDFLARE_API_TOKEN'),
        'zone_id' => env('CLOUDFLARE_ZONE_ID'),
        'api_url' => env('CLOUDFLARE_API_URL', 'https://api.cloudflare.com/client/v4'),
        'timeout' => (int) env('CLOUDFLARE_API_TIMEOUT', 10),
        'trust_visitor_headers' => env('CLOUDFLARE_TRUST_VISITOR_HEADERS', false),
        'trusted_proxies' => array_values(array_filter(array_map('trim', explode(',', (string) env('CLOUDFLARE_TRUSTED_PROXIES', ''))))),
    ],

    'integrity' => [
        'tracked_paths' => [
            'app',
            'bootstrap',
            'config',
            'database',
            'resources',
            'routes',
            'composer.json',
        ],
        'excluded_paths' => [
            'bootstrap/cache',
            'storage',
            'vendor',
            'node_modules',
            'public/build',
            '.git',
            '.kilo',
        ],
        'baseline_path' => storage_path('app/security/integrity/baseline.json'),
        'report_path' => storage_path('app/security/integrity/latest-report.json'),
        'max_file_size_kb' => (int) env('SECURITY_INTEGRITY_MAX_FILE_SIZE_KB', 5120),
    ],

    'scanner' => [
        'paths' => [
            public_path('uploads'),
        ],
        'report_path' => storage_path('app/security/scanner/latest-report.json'),
        'max_file_size_kb' => (int) env('SECURITY_SCANNER_MAX_FILE_SIZE_KB', 2048),
        'stale_after_hours' => (int) env('SECURITY_SCANNER_STALE_HOURS', 24),
        'schedule' => env('SECURITY_SCANNER_SCHEDULE', 'hourly'),
        'executable_extensions' => [
            'php',
            'phtml',
            'phar',
            'cgi',
            'pl',
            'py',
            'sh',
            'bash',
            'js',
            'exe',
            'dll',
            'bat',
            'cmd',
            'com',
            'msi',
        ],
        'ignored_filenames' => [
            '.htaccess',
            'web.config',
            'index.html',
            'index.htm',
        ],
        'suspicious_svg_tokens' => [
            '<script',
            'javascript:',
            'data:text/html',
            'onload=',
            'onerror=',
            '<foreignobject',
        ],
    ],
];
