<?php

namespace App\Vertex\Security\Modules\Hibp;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Validator;

class HibpModule
{
    public function register(Application $app): void
    {
        $app->singleton(HibpService::class);
        Validator::extend('hibp_uncompromised', function (string $attribute, mixed $value) use ($app): bool {
            return ! is_string($value) || ! $app->make(HibpService::class)->isCompromised($value);
        }, 'The :attribute has appeared in a data breach. Choose a different password.');
        $app->singleton(self::class, fn () => $this);
    }
}
