<?php

namespace App\Contracts;

interface SettingsTransferContract
{
    /** Export a portable, versioned document. Secrets are excluded by default. */
    public function export(bool $includeSecrets = false): array;

    /** Import a document and return the keys that were applied. */
    public function import(array $document, bool $allowSecrets = false): array;
}
