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

    /*
     * Package Service Providers
     */
    'providers' => [
        // Laravel Framework Providers...

        // Application Providers...
        App\Providers\AppServiceProvider::class,
        App\Providers\SentryServiceProvider::class,

        // Third-party Providers
        L5Swagger\L5SwaggerServiceProvider::class,
    ],

    /*
     * Class Aliases
     */
    'aliases' => [
        'L5Swagger' => L5Swagger\Facades\L5Swagger::class,
    ],
];