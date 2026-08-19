<?php

namespace Tests\Feature;

use App\Models\PageRevision;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPageFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedCore();
        $this->markApplicationAsInstalled();
    }

    public function test_published_page_is_accessible_on_public_uri(): void
    {
        $page = $this->createPage([
            'title' => 'About Vertex',
            'slug' => 'about',
            'uri' => '/about',
            'content_json' => [
                'version' => '1.0',
                'layout' => 'default',
                'sections' => [[
                    'id' => 'section-1',
                    'settings' => [],
                    'blocks' => [[
                        'id' => 'block-1',
                        'type' => 'heading',
                        'settings' => [
                            'level' => 'h1',
                            'text' => 'About Vertex',
                        ],
                    ]],
                ]],
            ],
        ], [
            'title' => 'About Vertex SEO',
            'description' => 'Public description',
            'robots' => 'index, follow',
            'include_in_sitemap' => true,
        ]);

        $response = $this->get('/about');

        $response->assertOk();
        $response->assertSee('About Vertex');
        $response->assertSee('About Vertex SEO', false);
        $response->assertSee('Public description', false);
        $response->assertSee('index, follow', false);
        $response->assertSee(url($page->uri), false);
    }

    public function test_draft_page_is_not_publicly_accessible(): void
    {
        $this->createPage([
            'title' => 'Draft Page',
            'slug' => 'draft-page',
            'uri' => '/draft-page',
            'status' => 'draft',
            'published_at' => null,
        ]);

        $this->get('/draft-page')->assertNotFound();
    }

    public function test_sitemap_includes_only_indexable_published_pages(): void
    {
        $included = $this->createPage([
            'title' => 'Included Page',
            'slug' => 'included',
            'uri' => '/included',
        ], [
            'robots' => 'index, follow',
            'include_in_sitemap' => true,
        ]);

        $this->createPage([
            'title' => 'Draft Page',
            'slug' => 'draft-only',
            'uri' => '/draft-only',
            'status' => 'draft',
            'published_at' => null,
        ], [
            'robots' => 'index, follow',
            'include_in_sitemap' => true,
        ]);

        $this->createPage([
            'title' => 'Noindex Page',
            'slug' => 'noindex',
            'uri' => '/noindex',
        ], [
            'robots' => 'noindex, nofollow',
            'include_in_sitemap' => true,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertSee(url($included->uri), false);
        $response->assertDontSee('/draft-only', false);
        $response->assertDontSee('/noindex', false);
    }

    public function test_robots_txt_uses_saved_setting_content(): void
    {
        Setting::query()->updateOrCreate(
            ['group_name' => 'seo', 'setting_key' => 'robots_txt'],
            ['setting_value' => "User-agent: *\nDisallow: /private", 'type' => 'string']
        );
        app(\App\Core\Services\SettingsService::class)->forgetCache();

        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSeeText('Disallow: /private');
    }
}
