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
        
        $this->adminUser = User::factory()->create(['role' => 'admin']);
        
        Storage::fake('backups');
    }

    public function test_admin_can_create_backup(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson('/admin/api/backups/create');

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('backups', [
            'status' => 'completed'
        ]);
    }

    public function test_backup_command_runs_successfully(): void
    {
        Artisan::call('backup:create');
        
        $output = Artisan::output();
        
        $this->assertStringContainsString('Backup completed', $output);
    }

    public function test_non_admin_cannot_create_backup(): void
    {
        $regularUser = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($regularUser)
            ->postJson('/admin/api/backups/create');

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_backup(): void
    {
        // Create a backup record first
        $backup = \App\Modules\System\Models\Backup::create([
            'filename' => 'test-backup.sql',
            'size' => 1024,
            'status' => 'completed',
            'type' => 'database'
        ]);

        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/admin/api/backups/{$backup->id}");

        $response->assertStatus(200);
        
        $this->assertDatabaseMissing('backups', [
            'id' => $backup->id
        ]);
    }
}
