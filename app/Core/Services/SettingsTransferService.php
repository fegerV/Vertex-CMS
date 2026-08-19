<?php

namespace App\Core\Services;

use App\Contracts\SettingsTransferContract;
use App\Core\Events\SettingsImported;
use App\Core\Support\SettingCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SettingsTransferService implements SettingsTransferContract
{
    public const SCHEMA_VERSION = '1.0';

    public function __construct(private readonly SettingsService $settings) {}

    public function export(bool $includeSecrets = false): array
    {
        $values = $this->settings->all();

        if (! $includeSecrets) {
            $values = array_diff_key($values, array_flip(SettingCatalog::secretKeys()));
        }

        ksort($values);

        return [
            'format' => 'vertexcms-settings',
            'schema_version' => self::SCHEMA_VERSION,
            'core_version' => (string) config('vertex.version'),
            'exported_at' => now()->toISOString(),
            'includes_secrets' => $includeSecrets,
            'settings' => $values,
        ];
    }

    public function import(array $document, bool $allowSecrets = false): array
    {
        $this->validateDocument($document);
        $secretKeys = array_flip(SettingCatalog::secretKeys());
        $values = [];

        foreach ($document['settings'] as $key => $value) {
            if (! is_string($key) || SettingCatalog::definition($key) === null) {
                continue;
            }

            if (isset($secretKeys[$key]) && ! $allowSecrets) {
                continue;
            }

            $values[$key] = $value;
        }

        DB::transaction(fn () => $this->settings->setMany($values));
        SettingsImported::dispatch(array_keys($values), (string) $document['schema_version']);

        return array_keys($values);
    }

    private function validateDocument(array $document): void
    {
        if (($document['format'] ?? null) !== 'vertexcms-settings') {
            throw ValidationException::withMessages(['format' => 'Unsupported settings document format.']);
        }

        if (($document['schema_version'] ?? null) !== self::SCHEMA_VERSION) {
            throw ValidationException::withMessages(['schema_version' => 'Unsupported settings schema version.']);
        }

        if (! isset($document['settings']) || ! is_array($document['settings'])) {
            throw ValidationException::withMessages(['settings' => 'Settings must be an object.']);
        }
    }
}
