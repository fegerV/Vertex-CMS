<?php

use Illuminate\Support\ServiceProvider;

return [
    'name' => env('APP_NAME', 'VertexCMS'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'locale' => env('APP_LOCALE', 'ru'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
    'providers' => ServiceProvider::defaultProviders()->merge([
        App\Providers\AppServiceProvider::class,
        App\Providers\VertexServiceProvider::class,
        App\Vertex\Security\SecurityServiceProvider::class,
        App\Security\Login\Providers\LoginServiceProvider::class,
        App\System\Providers\MailServiceProvider::class,
        Vertex\Forms\VertexFormsServiceProvider::class,
    ])->toArray(),
];
