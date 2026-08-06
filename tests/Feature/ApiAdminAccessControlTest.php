<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiAdminAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedCore();
        $this->markApplicationAsInstalled();
    }

    /**
     * Test: Anonymous user cannot access Pages API
     */
    public function test_anonymous_cannot_access_pages_api(): void
    {
        // GET /api/pages - index
        $this->getJson('/api/pages')->assertUnauthorized();

        // POST /api/pages - store
        $this->postJson('/api/pages', [
            'title' => 'Test Page',
            'slug' => 'test-page',
            'status' => 'draft',
        ])->assertUnauthorized();

        // System info
        $this->getJson('/api/system/info')->assertUnauthorized();

        // Cache clear
        $this->postJson('/api/cache/clear')->assertUnauthorized();

        // Builder blocks
        $this->getJson('/api/builder/blocks')->assertUnauthorized();

        // Builder render preview
        $this->postJson('/api/builder/render-preview', [
            'content' => [],
        ])->assertUnauthorized();
    }

    /**
     * Test: Authenticated user without permissions cannot access Pages API
     */
    public function test_authenticated_user_without_permissions_cannot_access_pages_api(): void
    {
        $user = User::factory()->create(['email' => 'noperms@example.com']);
        Sanctum::actingAs($user);

        // User has no permissions assigned
        $this->getJson('/api/pages')->assertForbidden();
        $this->postJson('/api/pages', [
            'title' => 'Test Page',
            'slug' => 'test-page',
            'status' => 'draft',
        ])->assertForbidden();
        $this->getJson('/api/system/info')->assertForbidden();
        $this->postJson('/api/cache/clear')->assertForbidden();
        $this->getJson('/api/builder/blocks')->assertForbidden();
        $this->postJson('/api/builder/render-preview', ['content' => []])->assertForbidden();
    }

    /**
     * Test: User with pages.view permission can read pages but not modify
     */
    public function test_user_with_pages_view_can_read_but_not_modify(): void
    {
        $user = $this->makeUserWithRole('viewer');
        Sanctum::actingAs($user);

        // Can read pages list
        $this->getJson('/api/pages')->assertOk();

        // Cannot create page (needs pages.create)
        $this->postJson('/api/pages', [
            'title' => 'Test Page',
            'slug' => 'test-page',
            'status' => 'draft',
        ])->assertForbidden();

        // Cannot access system info (needs system.view)
        $this->getJson('/api/system/info')->assertForbidden();

        // Cannot clear cache (needs cache.clear)
        $this->postJson('/api/cache/clear')->assertForbidden();

        // Cannot access builder (needs pages.edit)
        $this->getJson('/api/builder/blocks')->assertForbidden();
        $this->postJson('/api/builder/render-preview', ['content' => []])->assertForbidden();
    }

    /**
     * Test: User with pages.create permission can create pages
     */
    public function test_user_with_pages_create_can_create_pages(): void
    {
        $user = $this->makeUserWithRole('editor');
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/pages', [
            'title' => 'New Page',
            'slug' => 'new-page',
            'status' => 'draft',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.title', 'New Page');
    }

    /**
     * Test: User with pages.edit permission can update pages and access builder
     */
    public function test_user_with_pages_edit_can_update_and_use_builder(): void
    {
        $user = $this->makeUserWithRole('editor');
        Sanctum::actingAs($user);

        // First create a page
        $page = $this->postJson('/api/pages', [
            'title' => 'Editable Page',
            'slug' => 'editable-page',
            'status' => 'draft',
        ])->json('data');

        // Update the page
        $response = $this->putJson("/api/pages/{$page['id']}", [
            'title' => 'Updated Page',
            'status' => 'published',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.title', 'Updated Page');

        // Access builder blocks
        $this->getJson('/api/builder/blocks')->assertOk();

        // Render preview
        $this->postJson('/api/builder/render-preview', [
            'content' => [['type' => 'hero', 'props' => ['title' => 'Test']]],
        ])->assertOk();
    }

    /**
     * Test: User with pages.delete permission can delete pages
     */
    public function test_user_with_pages_delete_can_delete_pages(): void
    {
        $user = $this->makeUserWithRole('editor');
        Sanctum::actingAs($user);

        // Create a page first
        $page = $this->postJson('/api/pages', [
            'title' => 'Deletable Page',
            'slug' => 'deletable-page',
            'status' => 'draft',
        ])->json('data');

        // Delete the page
        $response = $this->deleteJson("/api/pages/{$page['id']}");

        $response->assertOk();
        $response->assertJson(['ok' => true, 'id' => $page['id']]);
    }

    /**
     * Test: User with system.view permission can access system info
     */
    public function test_user_with_system_view_can_access_system_info(): void
    {
        $user = $this->makeUserWithRole('super-admin');
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/system/info');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'app',
                'php',
                'server',
                'database',
            ],
        ]);
    }

    /**
     * Test: User with cache.clear permission can clear cache
     */
    public function test_user_with_cache_clear_can_clear_cache(): void
    {
        $user = $this->makeUserWithRole('super-admin');
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/cache/clear', [
            'scope' => 'all',
        ]);

        $response->assertOk();
        $response->assertJsonPath('ok', true);
        $response->assertJsonStructure(['data']);
    }

    /**
     * Test: Scoped cache clear works correctly
     */
    public function test_cache_clear_respects_scope_parameter(): void
    {
        $user = $this->makeUserWithRole('super-admin');
        Sanctum::actingAs($user);

        // Application cache only
        $response = $this->postJson('/api/cache/clear', ['scope' => 'application']);
        $response->assertOk();

        // Pages cache only
        $response = $this->postJson('/api/cache/clear', ['scope' => 'pages']);
        $response->assertOk();

        // Invalid scope is rejected
        $response = $this->postJson('/api/cache/clear', ['scope' => 'invalid']);
        $response->assertStatus(422);
    }
}
