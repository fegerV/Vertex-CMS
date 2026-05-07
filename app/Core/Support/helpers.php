<?php

if (! function_exists('config_value')) {
    function config_value(string $key, mixed $default = null): mixed
    {
        return app(\App\Core\Services\SettingsService::class)->get($key, $default);
    }
}

