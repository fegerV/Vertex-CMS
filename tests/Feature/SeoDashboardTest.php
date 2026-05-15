<?php

namespace Tests\Feature;

use App\Models\Redirect;
use App\Models\Taxonomy;
use App\Models\Term;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedCore();
        $this->markApplicationAsInstalled();
    }

    public function test_seo_dashboard_renders_audit_summary_and_issues(): void
    {
        $user = $this->makeUserWithRole('admin');

        $this->createPage([
            'title' => 'Audit Page',
            'slug' => 'audit-page',
            'uri' => '/audit-page',
        ], [
            'robots' => 'noindex, follow',
            'include_in_sitemap' => true,
        ]);

        $taxonomy = Taxonomy::query()->create([
            'name' => 'Topics',
            'slug' => 'topic',
            'entity_type' => 'page',
            'hierarchical' => false,
        ]);

        $term = Term::query()->create([
            'taxonomy_id' => $taxonomy->id,
            'name' => 'Vertex',
            'slug' => 'vertex',
            'seo_json' => [
                'robots' => 'index, follow',
                'include_in_sitemap' => true,
            ],
        ]);

        $page = $this->createPage([
            'title' => 'Attached Page',
            'slug' => 'attached-page',
            'uri' => '/attached-page',
        ], [
            'title' => 'Shared Duplicate Title',
            'description' => 'Attached page description',
            'robots' => 'index, follow',
            'include_in_sitemap' => true,
        ]);
        $page->terms()->sync([$term->id]);

        $this->createPage([
            'title' => 'Duplicate Page',
            'slug' => 'duplicate-page',
            'uri' => '/duplicate-page',
        ], [
            'title' => 'Shared Duplicate Title',
            'description' => 'Second page description',
            'robots' => 'index, follow',
            'include_in_sitemap' => true,
        ]);

        Redirect::query()->create([
            'from_url' => '/legacy-page',
            'to_url' => '/audit-page',
            'status_code' => 301,
            'enabled' => true,
            'hits' => 17,
        ]);

        $response = $this->actingAs($user)->get(route('admin.seo.dashboard'));

        $response->assertOk();
        $response->assertSee('SEO');
        $response->assertSee('Audit Page');
        $response->assertSee('Runtime');
        $response->assertSee('/legacy-page');
        $response->assertSee('Shared Duplicate Title');
    }

    public function test_seo_dashboard_shows_content_analysis_hints(): void
    {
        $user = $this->makeUserWithRole('admin');

        $this->createPage([
            'title' => 'Builder Audit Page',
            'slug' => 'builder-audit-page',
            'uri' => '/builder-audit-page',
            'content_json' => [
                'version' => '1.0',
                'layout' => 'default',
                'sections' => [[
                    'blocks' => [
                        [
                            'type' => 'image',
                            'settings' => [
                                'src' => '/storage/media/hero.jpg',
                                'alt' => '',
                            ],
                        ],
                        [
                            'type' => 'text',
                            'settings' => [
                                'content' => '<p>Длинный текст о преимуществах студии и услугах печати на холсте для более полезного сниппета.</p>',
                            ],
                        ],
                    ],
                ]],
            ],
        ], [
            'title' => 'Builder Audit Page',
            'robots' => 'index, follow',
            'include_in_sitemap' => true,
        ]);

        $response = $this->actingAs($user)->get(route('admin.seo.dashboard'));

        $response->assertOk();
        $response->assertSee('Content analysis');
        $response->assertSee('Builder Audit Page');
        $response->assertSee('Нет H1 в builder-контенте');
        $response->assertSee('Изображения без alt');
        $response->assertSee('Подсказка для meta description');
    }

    public function test_redirect_manager_renders_and_supports_html_crud_flow(): void
    {
        $user = $this->makeUserWithRole('admin');

        $this->actingAs($user)
            ->get(route('admin.redirects.index'))
            ->assertOk()
            ->assertSee('SEO Redirects')
            ->assertSee('Новое правило');

        $this->actingAs($user)
            ->post(route('admin.redirects.store'), [
                'from_url' => 'legacy-offer',
                'to_url' => 'new-offer',
                'status_code' => 301,
                'enabled' => '1',
            ])
            ->assertRedirect(route('admin.redirects.index'));

        $redirect = Redirect::query()->where('from_url', '/legacy-offer')->firstOrFail();

        $this->assertDatabaseHas('redirects', [
            'id' => $redirect->id,
            'from_url' => '/legacy-offer',
            'to_url' => '/new-offer',
            'enabled' => true,
        ]);

        $this->actingAs($user)
            ->put(route('admin.redirects.update', $redirect), [
                'from_url' => 'legacy-offer',
                'to_url' => 'new-offer-final',
                'status_code' => 302,
            ])
            ->assertRedirect(route('admin.redirects.index'));

        $this->assertDatabaseHas('redirects', [
            'id' => $redirect->id,
            'to_url' => '/new-offer-final',
            'status_code' => 302,
            'enabled' => false,
        ]);

        $this->actingAs($user)
            ->delete(route('admin.redirects.destroy', $redirect))
            ->assertRedirect(route('admin.redirects.index'));

        $this->assertDatabaseMissing('redirects', [
            'id' => $redirect->id,
        ]);
    }

    public function test_public_redirect_runtime_resolves_redirect_and_increments_hits(): void
    {
        Redirect::query()->create([
            'from_url' => '/legacy',
            'to_url' => '/fresh-url',
            'status_code' => 301,
            'enabled' => true,
            'hits' => 0,
        ]);

        $response = $this->get('/legacy');

        $response->assertStatus(301);
        $response->assertRedirect('/fresh-url');
        $this->assertDatabaseHas('redirects', [
            'from_url' => '/legacy',
            'hits' => 1,
        ]);
    }

    public function test_disabled_redirect_does_not_intercept_public_request(): void
    {
        Redirect::query()->create([
            'from_url' => '/disabled-legacy',
            'to_url' => '/fresh-url',
            'status_code' => 301,
            'enabled' => false,
            'hits' => 0,
        ]);

        $this->get('/disabled-legacy')->assertNotFound();
        $this->assertDatabaseHas('redirects', [
            'from_url' => '/disabled-legacy',
            'hits' => 0,
        ]);
    }
}
