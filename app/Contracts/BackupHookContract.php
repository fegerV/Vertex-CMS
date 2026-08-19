<?php

namespace App\Contracts;

use Throwable;

interface BackupHookContract
{
    public function beforeBackup(string $type, array $context): void;

    public function afterBackup(string $type, string $filename, array $context): void;

    public function backupFailed(string $type, Throwable $exception, array $context): void;
}
