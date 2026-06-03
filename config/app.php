<?php

return [
    'name' => env('APP_NAME', 'VertexCMS'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'locale' => env('APP_LOCALE', 'ru'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'ru'),
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
    'providers' => [
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        App\Providers\AppServiceProvider::class,
        App\Providers\VertexServiceProvider::class,
    ],
];

