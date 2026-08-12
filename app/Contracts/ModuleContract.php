<?php

namespace App\Contracts;

use Illuminate\Contracts\Foundation\Application;

interface ModuleContract
{
    public function id(): string;

    public function register(Application $app): void;

    public function boot(Application $app): void;
}
