<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicApiV1Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedCore();
        $this->markApplicationAsInstalled();
    }

    public function test_public_pages_endpoints_return_stable_envelope_and_only_published_pages(): void
    {
        $published = $this->createPage([
            'title' => 'Published API Page',
            'slug' => 'published-api-page',
            'uri' => '/published-api-page',
        ], [
            'title' => 'Published API SEO',
            'description' => 'Published API Description',
            'robots' => 'index, follow',
            'include_in_sitemap' => true,
        ]);

        $this->createPage([
            'title' => 'Draft API Page',
            'slug' => 'draft-api-page',
            'uri' => '/draft-api-page',
            'status' => 'draft',
            'published_at' => null,
        ]);

        $indexResponse = $this->getJson('/api/v1/public/pages');

        $indexResponse->assertOk();
        $indexResponse->assertJsonPath('meta.api_version', 'v1');
        $indexResponse->assertJsonPath('meta.pagination.total', 1);
        $indexResponse->assertJsonPath('data.0.title', 'Published API Page');
        $indexResponse->assertJsonPath('data.0.uri', '/published-api-page');
        $indexResponse->assertJsonMissingPath('data.0.attributes');

        $queryResponse = $this->getJson('/api/v1/public/pages/by-uri?uri=/published-api-page');
        $queryResponse->assertOk();
        $queryResponse->assertJsonPath('data.title', 'Published API Page');
        $queryResponse->assertJsonPath('data.seo.title', 'Published API SEO');

        $pathResponse = $this->getJson('/api/v1/public/pages/by-uri/published-api-page');
        $pathResponse->assertOk();
        $pathResponse->assertJsonPath('data.id', $published->id);

        $showResponse = $this->getJson('/api/v1/public/pages/'.$published->id);
        $showResponse->assertOk();
        $showResponse->assertJsonPath('data.slug', 'published-api-page');

        $missingResponse = $this->getJson('/api/v1/public/pages/by-uri?uri=/draft-api-page');
        $missingResponse->assertNotFound();
        $missingResponse->assertJsonPath('error.code', 'not_found');
        $missingResponse->assertJsonPath('meta.api_version', 'v1');
    }

    public function test_public_settings_and_menu_endpoints_return_expected_contract(): void
    {
        Menu::query()->create([
            'name' => 'Main Menu',
            'slug' => 'main-menu',
            'location' => 'header',
        ]);

        $menu = Menu::query()->firstOrFail();

        MenuItem::query()->create([
            'menu_id' => $menu->id,
            'title' => 'Home',
            'url' => '/',
            'sort_order' => 1,
        ]);

        $siteResponse = $this->getJson('/api/v1/public/settings/site');
        $siteResponse->assertOk();
        $siteResponse->assertJsonPath('meta.api_version', 'v1');
        $siteResponse->assertJsonPath('data.site.name', 'VertexCMS');
        $siteResponse->assertJsonPath('data.api.version', 'v1');

        $menuResponse = $this->getJson('/api/v1/public/menus/header');
        $menuResponse->assertOk();
        $menuResponse->assertJsonPath('data.location', 'header');
        $menuResponse->assertJsonPath('data.items.0.title', 'Home');
        $menuResponse->assertJsonPath('data.items.0.url', '/');
    }

    public function test_public_api_returns_stable_forbidden_error_when_disabled(): void
    {
        Setting::query()->updateOrCreate(
            ['group_name' => 'api', 'setting_key' => 'public_enabled'],
            ['setting_value' => '0', 'type' => 'boolean']
        );
        app(\App\Core\Services\SettingsService::class)->forgetCache();

        $response = $this->getJson('/api/v1/public/pages');

        $response->assertForbidden();
        $response->assertJsonPath('error.code', 'forbidden');
        $response->assertJsonPath('error.message', 'Public API is disabled.');
        $response->assertJsonPath('meta.api_version', 'v1');
    }
}
