<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SettingsSeeder::class);
        $this->markApplicationAsInstalled();
    }

    public function test_editor_does_not_see_user_management_navigation(): void
    {
        $editor = $this->makeUserWithRole('editor');

        $response = $this->actingAs($editor)->get('/admin');

        $response->assertOk();
        $response->assertDontSee('Пользователи');
        $response->assertDontSee('Роли');
        $response->assertSee('Страницы');
    }

    public function test_super_admin_sees_system_sections_in_navigation(): void
    {
        $superAdmin = $this->makeUserWithRole('super-admin');

        $response = $this->actingAs($superAdmin)->get('/admin');

        $response->assertOk();
        $response->assertSee('Система');
        $response->assertSee('Кеш');
        $response->assertSee('Логи');
    }

    public function test_viewer_cannot_update_settings(): void
    {
        $viewer = $this->makeUserWithRole('viewer');
        $before = Setting::query()
            ->where('group_name', 'site')
            ->where('setting_key', 'name')
            ->value('setting_value');

        $response = $this->actingAs($viewer)->put('/admin/settings', [
            'settings' => [
                'site' => [
                    'name' => 'Changed by viewer',
                    'url' => 'https://example.com',
                    'locale' => 'ru',
                    'timezone' => 'UTC',
                ],
                'api' => [
                    'version' => 'v1',
                ],
                'cache' => [
                    'driver' => 'file',
                ],
            ],
        ]);

        $response->assertForbidden();

        $after = Setting::query()
            ->where('group_name', 'site')
            ->where('setting_key', 'name')
            ->value('setting_value');

        $this->assertSame($before, $after);
    }

    public function test_viewer_cannot_access_system_sections(): void
    {
        $viewer = $this->makeUserWithRole('viewer');

        $this->actingAs($viewer)->get('/admin/system/info')->assertForbidden();
        $this->actingAs($viewer)->get('/admin/system/logs')->assertForbidden();
        $this->actingAs($viewer)->get('/admin/system/cache')->assertForbidden();
    }

    private function makeUserWithRole(string $roleSlug): User
    {
        $user = User::query()->create([
            'name' => ucfirst($roleSlug).' User',
            'email' => $roleSlug.'@example.com',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        $role = Role::query()->where('slug', $roleSlug)->firstOrFail();
        $user->roles()->sync([$role->id]);

        return $user;
    }

    private function markApplicationAsInstalled(): void
    {
        $path = config('vertex.install_lock_path');
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($path, json_encode(['installed_at' => now()->toISOString()]));
    }
}
