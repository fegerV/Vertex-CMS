<?php

return [
    'version' => env('VERTEX_VERSION', '1.0.0'),
    'installed' => (bool) env('VERTEX_INSTALLED', false),
    'install_lock_path' => storage_path('app/installed.lock'),
    'theme' => env('VERTEX_THEME', 'default'),
    'cache' => [
        'page_store' => storage_path('vertex-cache/pages'),
        'settings_key' => 'vertex.settings',
        'menus_key' => 'vertex.menus',
        'seo_key' => 'vertex.seo',
        'ttl' => [
            'page' => 3600,
            'settings' => 300,
            'menu' => 600,
        ],
    ],
    'uploads' => [
        'path' => public_path('uploads'),
        'url' => env('APP_URL').'/uploads',
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'pdf'],
        'max_size' => 10240,
    ],
    'api' => [
        'public_enabled' => env('API_PUBLIC_ENABLED', true),
        'version' => 'v1',
        'rate_limit' => [
            'public' => 60,
            'authenticated' => 120,
        ],
    ],
    'seo' => [
        'auto_generate_meta' => true,
        'default_robots' => 'index, follow',
        'default_title_suffix' => ' | VertexCMS',
    ],
    'backup' => [
        'schedule' => [
            'database' => env('BACKUP_DATABASE_SCHEDULE', 'daily'),
            'files' => env('BACKUP_FILES_SCHEDULE', 'weekly'),
            'retention' => env('BACKUP_RETENTION_DAYS', 30),
            'storage' => env('BACKUP_STORAGE', 'local'),
        ],
        'path' => storage_path('app/backups'),
    ],
    'security' => [
        'ip_filter' => [
            'whitelist_mode' => env('IP_FILTER_WHITELIST_MODE', false),
        ],
        'gdpr' => [
            'enabled' => env('GDPR_ENABLED', true),
            'cookie_name' => 'gdpr_consent',
        ],
    ],
];
