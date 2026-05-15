<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageRevision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuilderContractTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedCore();
        $this->markApplicationAsInstalled();
    }

    public function test_builder_update_saves_normalized_json_and_creates_revision(): void
    {
        $editor = $this->makeUserWithRole('editor');
        $page = $this->createPage([
            'title' => 'Builder Page',
            'slug' => 'builder-page',
            'uri' => '/builder-page',
            'content_json' => [
                'version' => '1.0',
                'layout' => 'default',
                'sections' => [[
                    'id' => 'existing-section',
                    'settings' => [],
                    'blocks' => [[
                        'id' => 'existing-block',
                        'type' => 'heading',
                        'settings' => ['text' => 'Before'],
                    ]],
                ]],
            ],
        ]);

        $response = $this->actingAs($editor)->putJson("/admin/pages/{$page->id}/builder", [
            'title' => 'Builder Page Updated',
            'create_revision' => true,
            'content' => [
                [
                    'type' => 'heading',
                    'settings' => [
                        'level' => 'h1',
                        'text' => 'Hero title',
                    ],
                ],
                [
                    'type' => 'text',
                    'settings' => [
                        'content' => 'Body copy',
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJsonPath('ok', true);
        $response->assertJsonPath('data.title', 'Builder Page Updated');

        $page->refresh();

        $this->assertSame('Builder Page Updated', $page->title);
        $this->assertSame('1.0', $page->content_json['version']);
        $this->assertCount(1, $page->content_json['sections']);
        $this->assertCount(2, $page->content_json['sections'][0]['blocks']);
        $this->assertSame('heading', $page->content_json['sections'][0]['blocks'][0]['type']);
        $this->assertSame('text', $page->content_json['sections'][0]['blocks'][1]['type']);

        $revision = PageRevision::query()->where('page_id', $page->id)->first();
        $this->assertNotNull($revision);
        $this->assertSame('manual-save', $revision->action);
        $this->assertSame('Before', $revision->content_json['sections'][0]['blocks'][0]['settings']['text']);
    }

    public function test_builder_rejects_unknown_block_types_without_persisting_changes(): void
    {
        $editor = $this->makeUserWithRole('editor');
        $page = $this->createPage([
            'title' => 'Validation Page',
            'slug' => 'validation-page',
            'uri' => '/validation-page',
            'content_json' => [
                'version' => '1.0',
                'layout' => 'default',
                'sections' => [],
            ],
        ]);

        $response = $this->actingAs($editor)->putJson("/admin/pages/{$page->id}/builder", [
            'content' => [[
                'type' => 'unknown-widget',
                'settings' => [],
            ]],
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('ok', false);
        $this->assertStringContainsString('Unknown block type: unknown-widget', $response->json('errors.0'));

        $page->refresh();
        $this->assertSame([], $page->content_json['sections']);
        $this->assertDatabaseCount('page_revisions', 0);
    }

    public function test_builder_preview_returns_rendered_html_without_persisting_changes(): void
    {
        $editor = $this->makeUserWithRole('editor');
        $page = $this->createPage([
            'title' => 'Preview Page',
            'slug' => 'preview-page',
            'uri' => '/preview-page',
        ]);

        $response = $this->actingAs($editor)->postJson("/admin/pages/{$page->id}/builder/preview", [
            'content' => [[
                'type' => 'heading',
                'settings' => [
                    'level' => 'h2',
                    'text' => 'Preview only',
                ],
            ]],
        ]);

        $response->assertOk();
        $html = (string) $response->json('html');
        $this->assertStringContainsString('<section class="vc-section">', $html);
        $this->assertStringContainsString('<h2 class="vc-heading"', $html);
        $this->assertStringContainsString('Preview only', $html);

        $page->refresh();
        $this->assertSame([], $page->content_json['sections']);
    }

    public function test_builder_screen_uses_advanced_runtime_and_ux_preview_is_available_for_editing(): void
    {
        $editor = $this->makeUserWithRole('editor');
        $page = $this->createPage([
            'title' => 'Editor Flow Page',
            'slug' => 'editor-flow-page',
            'uri' => '/editor-flow-page',
            'content_json' => [
                'version' => '1.0',
                'layout' => 'default',
                'sections' => [[
                    'blocks' => [[
                        'type' => 'heading',
                        'settings' => [
                            'level' => 'h1',
                            'text' => 'Preview heading',
                        ],
                    ]],
                ]],
            ],
        ]);

        $this->actingAs($editor)
            ->get(route('admin.pages.builder', $page))
            ->assertOk()
            ->assertSee('data-vc-advanced-builder', false);

        $this->actingAs($editor)
            ->get(route('admin.pages.preview', $page))
            ->assertOk()
            ->assertSee('UX Preview', false)
            ->assertSee('Preview heading', false);
    }

    public function test_builder_screen_uses_compiled_runtime_without_unsafe_eval_csp_exception(): void
    {
        $editor = $this->makeUserWithRole('editor');
        $page = $this->createPage([
            'title' => 'Builder CSP Page',
            'slug' => 'builder-csp-page',
            'uri' => '/builder-csp-page',
            'content_json' => [
                'version' => '1.0',
                'layout' => 'default',
                'sections' => [],
            ],
        ]);

        $response = $this->actingAs($editor)
            ->get(route('admin.pages.builder', $page))
            ->assertOk();

        $this->assertStringNotContainsString(
            "'unsafe-eval'",
            (string) $response->headers->get('Content-Security-Policy')
        );
    }

    public function test_builder_screen_exposes_section_contract_metadata(): void
    {
        $editor = $this->makeUserWithRole('editor');
        $page = $this->createPage([
            'title' => 'Builder Section Config Page',
            'slug' => 'builder-section-config-page',
            'uri' => '/builder-section-config-page',
            'content_json' => [
                'version' => '1.0',
                'layout' => 'default',
                'sections' => [],
            ],
        ]);

        $html = $this->actingAs($editor)
            ->get(route('admin.pages.builder', $page))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('"sections"', $html);
        $this->assertStringContainsString('"surface_tokens"', $html);
        $this->assertStringContainsString('"hero-surface"', $html);
        $this->assertStringContainsString('"quick_add"', $html);
        $this->assertStringContainsString('"template-hero-heading"', $html);
    }

    public function test_builder_shared_presets_endpoint_returns_json_payload(): void
    {
        $editor = $this->makeUserWithRole('editor');

        $this->actingAs($editor)
            ->getJson(route('admin.pages.builder.presets.index'))
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonStructure([
                'ok',
                'data',
            ]);
    }

    public function test_builder_shared_templates_endpoint_returns_visual_library_metadata(): void
    {
        $editor = $this->makeUserWithRole('editor');

        $response = $this->actingAs($editor)
            ->getJson(route('admin.pages.builder.shared-templates.index'))
            ->assertOk()
            ->assertJsonPath('ok', true);

        $templates = $response->json('data');

        $this->assertIsArray($templates);
        $this->assertNotEmpty($templates);
        $this->assertArrayHasKey('thumbnail', $templates[0]);
        $this->assertArrayHasKey('sections_count', $templates[0]);
        $this->assertArrayHasKey('blocks_count', $templates[0]);
    }

    public function test_public_renderer_sanitizes_html_and_ignores_unknown_blocks(): void
    {
        $this->createPage([
            'title' => 'Sanitized Page',
            'slug' => 'sanitized-page',
            'uri' => '/sanitized-page',
            'content_json' => [
                'version' => '1.0',
                'layout' => 'default',
                'sections' => [[
                    'id' => 'section-1',
                    'settings' => [],
                    'blocks' => [
                        [
                            'id' => 'block-html',
                            'type' => 'html',
                            'settings' => [
                                'html' => '<a href="javascript:alert(1)" onclick="alert(2)">Safe</a><script>alert(3)</script><p>Paragraph</p>',
                            ],
                        ],
                        [
                            'id' => 'block-unknown',
                            'type' => 'unknown-widget',
                            'settings' => [],
                        ],
                    ],
                ]],
            ],
        ]);

        $response = $this->get('/sanitized-page');

        $response->assertOk();
        $response->assertSee('Safe', false);
        $response->assertSee('<p>Paragraph</p>', false);
        $response->assertSee('Unknown VertexCMS block: unknown-widget', false);
        $response->assertDontSee('javascript:', false);
        $response->assertDontSee('onclick=', false);
        $response->assertDontSee('<script', false);
    }
}
