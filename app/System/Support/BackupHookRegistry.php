<?php

namespace App\System\Support;

use App\Contracts\BackupHookContract;
use InvalidArgumentException;
use Throwable;

class BackupHookRegistry
{
    /** @var list<BackupHookContract> */
    private array $hooks = [];

    public function add(BackupHookContract $hook): void
    {
        $this->hooks[] = $hook;
    }

    public function before(string $type, array $context = []): void
    {
        $this->assertType($type);
        foreach ($this->hooks as $hook) {
            $hook->beforeBackup($type, $context);
        }
    }

    public function after(string $type, string $filename, array $context = []): void
    {
        $this->assertType($type);
        foreach ($this->hooks as $hook) {
            $hook->afterBackup($type, $filename, $context);
        }
    }

    public function failed(string $type, Throwable $exception, array $context = []): void
    {
        $this->assertType($type);
        foreach ($this->hooks as $hook) {
            $hook->backupFailed($type, $exception, $context);
        }
    }

    private function assertType(string $type): void
    {
        if (! in_array($type, ['database', 'files'], true)) {
            throw new InvalidArgumentException("Unsupported backup type [{$type}].");
        }
    }
}
