<?php

namespace App\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;

class SettingsImported
{
    use Dispatchable;

    public function __construct(public readonly array $keys, public readonly string $schemaVersion) {}
}
