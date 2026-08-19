<?php

namespace Tests;

use App\Models\Page;
use App\Models\Role;
use App\Models\SeoMeta;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function seedCore(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SettingsSeeder::class);
    }

    protected function markApplicationAsInstalled(): void
    {
        $path = config('vertex.install_lock_path');
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($path, json_encode(['installed_at' => now()->toISOString()]));
    }

    protected function makeUserWithRole(string $roleSlug, array $attributes = []): User
    {
        $user = User::query()->create(array_merge([
            'name' => ucfirst($roleSlug).' User',
            'email' => $roleSlug.'@example.com',
            'password' => Hash::make('password'),
            'status' => 'active',
        ], $attributes));

        $role = Role::query()->where('slug', $roleSlug)->firstOrFail();
        $user->roles()->sync([$role->id]);

        return $user;
    }

    protected function createPage(array $attributes = [], ?array $seo = null): Page
    {
        $page = Page::query()->create(array_merge([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'uri' => '/test-page',
            'status' => 'published',
            'template' => 'page',
            'content_json' => [
                'version' => '1.0',
                'layout' => 'default',
                'sections' => [],
            ],
            'custom_fields_json' => [],
            'published_at' => now()->subMinute(),
        ], $attributes));

        if ($seo !== null) {
            SeoMeta::query()->create(array_merge([
                'entity_type' => Page::class,
                'entity_id' => $page->id,
                'robots' => 'index, follow',
                'include_in_sitemap' => true,
            ], $seo));
        }

        return $page;
    }
}

