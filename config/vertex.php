<?php

return [
    'version' => env('VERTEX_VERSION', '0.1.0'),
    'installed' => (bool) env('VERTEX_INSTALLED', false),
    'install_lock_path' => storage_path('app/installed.lock'),
    'theme' => env('VERTEX_THEME', 'default'),
    'cache' => [
        'page_store' => storage_path('vertex-cache/pages'),
        'settings_key' => 'vertex.settings',
        'menus_key' => 'vertex.menus',
        'seo_key' => 'vertex.seo',
    ],
    'uploads' => [
        'path' => public_path('uploads'),
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'pdf'],
    ],
];

