<?php

namespace Tests\Feature\Backups;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedCore();
        $this->markApplicationAsInstalled();

        // system.edit/system.view permissions live on the super-admin role.
        $this->adminUser = $this->makeUserWithRole('super-admin');
        Storage::fake('local');
    }

    public function test_admin_can_create_backup(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson('/admin/api/backups/create', ['type' => 'files']);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $filename = $response->json('backup');

        $this->assertIsString($filename);
        $this->assertStringStartsWith('files_', $filename);
        Storage::disk('local')->assertExists('backups/'.$filename);
    }

    public function test_backup_command_runs_successfully(): void
    {
        $exitCode = Artisan::call('backup:create', ['--files' => true]);
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Резервное копирование завершено успешно', $output);
        $this->assertNotEmpty(Storage::disk('local')->files('backups'));
    }

    public function test_backup_listing_is_available_to_admins_only(): void
    {
        Storage::disk('local')->put('backups/db_sqlite_existing.sql', 'dump');

        $this->actingAs($this->adminUser)
            ->getJson('/admin/api/backups')
            ->assertOk()
            ->assertJsonPath('success', true);

        $editor = $this->makeUserWithRole('editor', ['email' => 'editor-backup@example.com']);

        $this->actingAs($editor)
            ->getJson('/admin/api/backups')
            ->assertForbidden();
    }

    public function test_non_admin_cannot_create_backup(): void
    {
        $editor = $this->makeUserWithRole('editor', ['email' => 'editor-backup-2@example.com']);

        $this->actingAs($editor)
            ->postJson('/admin/api/backups/create', ['type' => 'files'])
            ->assertForbidden();

        $this->assertEmpty(Storage::disk('local')->files('backups'));
    }

    public function test_admin_can_delete_backup(): void
    {
        Storage::disk('local')->put('backups/db_sqlite_test.sql', 'dump');

        $response = $this->actingAs($this->adminUser)
            ->deleteJson('/admin/api/backups/db_sqlite_test.sql');

        $response->assertOk();
        $response->assertJsonPath('success', true);

        Storage::disk('local')->assertMissing('backups/db_sqlite_test.sql');
    }
}
