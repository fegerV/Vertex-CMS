<?php

namespace Tests\Feature;

use App\Contracts\BackupHookContract;
use App\Contracts\CacheInvalidatorContract;
use App\Contracts\SettingsRepositoryContract;
use App\Contracts\SettingsTransferContract;
use App\Core\Events\SettingsImported;
use App\Core\Support\SettingCatalog;
use App\System\Support\BackupHookRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;
use Throwable;

class StableCoreContractsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_core_contracts_are_bound(): void
    {
        $this->assertInstanceOf(SettingsRepositoryContract::class, app(SettingsRepositoryContract::class));
        $this->assertInstanceOf(SettingsTransferContract::class, app(SettingsTransferContract::class));
        $this->assertInstanceOf(CacheInvalidatorContract::class, app(CacheInvalidatorContract::class));
    }

    public function test_settings_round_trip_is_versioned_and_excludes_secrets_by_default(): void
    {
        Event::fake([SettingsImported::class]);
        $settings = app(SettingsRepositoryContract::class);
        $transfer = app(SettingsTransferContract::class);
        $secretKey = SettingCatalog::secretKeys()[0];

        $settings->setMany(['site.name' => 'Exported site', $secretKey => 'private-value']);
        $document = $transfer->export();

        $this->assertSame('vertexcms-settings', $document['format']);
        $this->assertSame('1.0', $document['schema_version']);
        $this->assertSame('Exported site', $document['settings']['site.name']);
        $this->assertArrayNotHasKey($secretKey, $document['settings']);

        $document['settings']['site.name'] = 'Imported site';
        $document['settings']['unknown.key'] = 'ignored';
        $keys = $transfer->import($document);

        $this->assertSame(['site.name'], $keys);
        $this->assertSame('Imported site', $settings->get('site.name'));
        Event::assertDispatched(SettingsImported::class, fn ($event) => $event->keys === ['site.name']);
    }

    public function test_settings_import_rejects_an_unknown_schema(): void
    {
        $this->expectException(ValidationException::class);

        app(SettingsTransferContract::class)->import([
            'format' => 'vertexcms-settings',
            'schema_version' => '2.0',
            'settings' => [],
        ]);
    }

    public function test_backup_registry_runs_failure_hooks(): void
    {
        $hook = new class implements BackupHookContract
        {
            public array $calls = [];

            public function beforeBackup(string $type, array $context): void
            {
                $this->calls[] = "before:{$type}";
            }

            public function afterBackup(string $type, string $filename, array $context): void
            {
                $this->calls[] = "after:{$filename}";
            }

            public function backupFailed(string $type, Throwable $exception, array $context): void
            {
                $this->calls[] = "failed:{$exception->getMessage()}";
            }
        };

        $registry = new BackupHookRegistry;
        $registry->add($hook);
        $registry->before('database');
        $registry->failed('database', new RuntimeException('disk full'));

        $this->assertSame(['before:database', 'failed:disk full'], $hook->calls);
    }
}
