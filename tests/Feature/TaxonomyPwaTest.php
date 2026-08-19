<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\Taxonomy;
use App\Models\Term;
use App\Theme\Services\ThemeManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxonomyPwaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedCore();
        $this->markApplicationAsInstalled();
    }

    public function test_pwa_routes_follow_settings_and_render_offline_page(): void
    {
        $this->get('/manifest.webmanifest')->assertNotFound();
        $this->get('/service-worker.js')->assertNotFound();
        $this->get('/offline')->assertNotFound();

        $offlinePage = $this->createPage([
            'title' => 'Offline Support',
            'slug' => 'offline-support',
            'uri' => '/offline-support',
            'content_json' => [
                'version' => '1.0',
                'layout' => 'default',
                'sections' => [[
                    'id' => 'offline-section',
                    'settings' => [],
                    'blocks' => [[
                        'id' => 'offline-block',
                        'type' => 'heading',
                        'settings' => [
                            'level' => 'h1',
                            'text' => 'Offline Support',
                        ],
                    ]],
                ]],
            ],
        ]);

        foreach ([
            'pwa.enabled' => ['1', 'boolean'],
            'pwa.name' => ['Vertex App', 'string'],
            'pwa.short_name' => ['Vertex', 'string'],
            'pwa.start_url' => ['/', 'string'],
            'pwa.display' => ['standalone', 'string'],
            'pwa.theme_color' => ['#123456', 'string'],
            'pwa.background_color' => ['#ffffff', 'string'],
            'pwa.offline_page_id' => [(string) $offlinePage->id, 'integer'],
        ] as $key => [$value, $type]) {
            [$group, $settingKey] = explode('.', $key, 2);
            Setting::query()->updateOrCreate(
                ['group_name' => $group, 'setting_key' => $settingKey],
                ['setting_value' => $value, 'type' => $type]
            );
        }

        app(\App\Core\Services\SettingsService::class)->forgetCache();

        $manifestResponse = $this->getJson('/manifest.webmanifest');
        $manifestResponse->assertOk();
        $manifestResponse->assertHeader('Content-Type', 'application/manifest+json');
        $manifestResponse->assertJsonPath('name', 'Vertex App');
        $manifestResponse->assertJsonPath('short_name', 'Vertex');
        $manifestResponse->assertJsonPath('theme_color', '#123456');

        $workerResponse = $this->get('/service-worker.js');
        $workerResponse->assertOk();
        $workerResponse->assertHeader('Service-Worker-Allowed', '/');
        $workerResponse->assertSee("const OFFLINE_URL = '".route('frontend.offline')."';", false);

        $offlineResponse = $this->get('/offline');
        $offlineResponse->assertOk();
        $offlineResponse->assertSee('Offline Support');
    }

    public function test_taxonomy_archive_and_api_only_expose_published_pages_for_term(): void
    {
        $taxonomy = Taxonomy::query()->create([
            'name' => 'Categories',
            'slug' => 'category',
            'entity_type' => 'page',
            'hierarchical' => true,
            'settings_json' => [
                'archive_title' => 'Category archive',
            ],
        ]);

        $term = Term::query()->create([
            'taxonomy_id' => $taxonomy->id,
            'name' => 'News',
            'slug' => 'news',
            'description' => 'Latest updates',
            'seo_json' => [
                'title' => 'News Archive',
                'description' => 'News archive description',
                'robots' => 'index, follow',
                'include_in_sitemap' => true,
            ],
        ]);

        $publishedPage = $this->createPage([
            'title' => 'Published News',
            'slug' => 'published-news',
            'uri' => '/published-news',
        ], [
            'description' => 'Published page description',
            'robots' => 'index, follow',
            'include_in_sitemap' => true,
        ]);
        $publishedPage->terms()->sync([$term->id]);

        $draftPage = $this->createPage([
            'title' => 'Draft News',
            'slug' => 'draft-news',
            'uri' => '/draft-news',
            'status' => 'draft',
            'published_at' => null,
        ]);
        $draftPage->terms()->sync([$term->id]);

        $archiveResponse = $this->get('/taxonomy/category/news');
        $archiveResponse->assertOk();
        $archiveResponse->assertSee('News Archive', false);
        $archiveResponse->assertSee('Published News');
        $archiveResponse->assertDontSee('Draft News');
        $archiveResponse->assertSee(route('frontend.term-archive', ['category', 'news']), false);

        $taxonomyIndexResponse = $this->getJson('/api/v1/public/taxonomies');
        $taxonomyIndexResponse->assertOk();
        $taxonomyIndexResponse->assertJsonPath('data.0.slug', 'category');
        $taxonomyIndexResponse->assertJsonPath('data.0.terms.0.slug', 'news');

        $termPagesResponse = $this->getJson('/api/v1/public/taxonomies/category/terms/news/pages');
        $termPagesResponse->assertOk();
        $termPagesResponse->assertJsonPath('meta.api_version', 'v1');
        $termPagesResponse->assertJsonPath('meta.term.slug', 'news');
        $termPagesResponse->assertJsonPath('meta.pagination.total', 1);
        $termPagesResponse->assertJsonPath('data.0.title', 'Published News');
    }

    public function test_theme_manager_uses_default_theme_fallbacks(): void
    {
        $page = $this->createPage([
            'template' => 'missing-template',
        ]);

        $themes = app(ThemeManager::class);

        $this->assertSame('default', $themes->activeTheme());
        $this->assertSame('default', $themes->metadata()['slug']);
        $this->assertSame('themes.default.page', $themes->pageView($page));
        $this->assertSame('themes.default.offline', $themes->offlineView());
        $this->assertSame('themes.default.term-archive', $themes->termArchiveView());
        $this->assertSame('builder.blocks.heading', $themes->blockView('heading'));
    }
}
