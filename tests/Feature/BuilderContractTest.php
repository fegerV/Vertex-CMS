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

    public function test_builder_preview_can_return_editor_document_for_live_canvas(): void
    {
        $editor = $this->makeUserWithRole('editor');
        $page = $this->createPage([
            'title' => 'Live Preview Page',
            'slug' => 'live-preview-page',
            'uri' => '/live-preview-page',
        ]);

        $response = $this->actingAs($editor)->postJson("/admin/pages/{$page->id}/builder/preview", [
            'document' => true,
            'content' => [[
                'id' => 'section_live',
                'settings' => [],
                'blocks' => [[
                    'id' => 'block_live_heading',
                    'type' => 'heading',
                    'settings' => [
                        'level' => 'h2',
                        'text' => 'Live canvas',
                    ],
                ]],
            ]],
        ]);

        $response->assertOk();
        $response->assertJsonPath('ok', true);
        $document = (string) $response->json('document');

        $this->assertStringContainsString('<!DOCTYPE html>', $document);
        $this->assertStringContainsString('data-vc-section-index="0"', $document);
        $this->assertStringContainsString('data-vc-block-index="0"', $document);
        $this->assertStringContainsString('data-vc-block-depth="0"', $document);
        $this->assertStringContainsString('Live canvas', $document);
    }

    public function test_builder_preview_allows_empty_media_blocks_as_editor_placeholders(): void
    {
        $editor = $this->makeUserWithRole('editor');
        $page = $this->createPage([
            'title' => 'Empty Media Preview Page',
            'slug' => 'empty-media-preview-page',
            'uri' => '/empty-media-preview-page',
        ]);

        $response = $this->actingAs($editor)->postJson("/admin/pages/{$page->id}/builder/preview", [
            'document' => true,
            'content' => [[
                'id' => 'section_media',
                'settings' => [],
                'blocks' => [[
                    'id' => 'block_empty_video',
                    'type' => 'video',
                    'settings' => [
                        'type' => 'youtube',
                        'url' => '',
                    ],
                ], [
                    'id' => 'block_empty_image',
                    'type' => 'image',
                    'settings' => [
                        'media_id' => null,
                        'url' => '',
                    ],
                ]],
            ]],
        ]);

        $response->assertOk();
        $document = (string) $response->json('document');

        $this->assertStringContainsString('Video placeholder', $document);
        $this->assertStringContainsString('Image placeholder', $document);
    }

    public function test_builder_preview_rejects_invalid_blocks_with_json_error(): void
    {
        $editor = $this->makeUserWithRole('editor');
        $page = $this->createPage([
            'title' => 'Preview Validation Page',
            'slug' => 'preview-validation-page',
            'uri' => '/preview-validation-page',
        ]);

        $response = $this->actingAs($editor)->postJson("/admin/pages/{$page->id}/builder/preview", [
            'content' => [[
                'type' => 'unknown-widget',
                'settings' => [],
            ]],
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('ok', false);
        $this->assertStringContainsString('Unknown block type: unknown-widget', $response->json('errors.0'));
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
        $this->assertStringContainsString('"blocks"', $html);
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

    public function test_builder_design_library_endpoint_returns_workspace_contract(): void
    {
        $editor = $this->makeUserWithRole('editor');

        $this->actingAs($editor)
            ->getJson(route('admin.pages.builder.design-library.api'))
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('data.version', '1.0')
            ->assertJsonPath('data.navigation.0.id', 'templates')
            ->assertJsonPath('data.collections.0.id', 'templates')
            ->assertJsonPath('data.collections.1.id', 'starters')
            ->assertJsonStructure([
                'ok',
                'data' => [
                    'version',
                    'generated_at',
                    'navigation',
                    'stats',
                    'categories',
                    'collections',
                    'empty_states',
                ],
            ]);
    }

    public function test_builder_design_library_screen_mounts_vue_workspace(): void
    {
        $editor = $this->makeUserWithRole('editor');

        $this->actingAs($editor)
            ->get(route('admin.pages.builder.design-library.index'))
            ->assertOk()
            ->assertSee('data-vc-design-library', false)
            ->assertSee('data-api-url', false)
            ->assertSee('Design Library', false);
    }

    public function test_builder_autosave_rejects_invalid_blocks_and_does_not_create_revision(): void
    {
        $editor = $this->makeUserWithRole('editor');
        $page = $this->createPage();

        $response = $this->actingAs($editor)->postJson("/admin/pages/{$page->id}/builder/auto-save", [
            'content' => [[
                'type' => 'unknown-widget',
                'settings' => [],
            ]],
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('ok', false);
        $this->assertDatabaseCount('page_revisions', 0);
    }

    public function test_builder_import_returns_sections_without_persisting_page_content(): void
    {
        $editor = $this->makeUserWithRole('editor');
        $page = $this->createPage([
            'content_json' => [
                'version' => '1.0',
                'layout' => 'default',
                'sections' => [[
                    'id' => 'original-section',
                    'settings' => [],
                    'blocks' => [[
                        'id' => 'original-block',
                        'type' => 'heading',
                        'settings' => ['text' => 'Original'],
                    ]],
                ]],
            ],
        ]);

        $import = json_encode([
            'version' => '2.0',
            'exported_at' => now()->toIso8601String(),
            'sections' => [[
                'type' => 'text',
                'settings' => ['content' => 'Imported copy'],
            ]],
        ], JSON_THROW_ON_ERROR);

        $response = $this->actingAs($editor)->postJson('/admin/pages/import-sections', [
            'import_data' => $import,
            'page_id' => $page->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('ok', true);
        $response->assertJsonPath('sections.0.blocks.0.type', 'text');

        $page->refresh();
        $this->assertSame('Original', $page->content_json['sections'][0]['blocks'][0]['settings']['text']);
    }

    public function test_builder_restore_revision_restores_full_content_shape_and_seo_snapshot(): void
    {
        $editor = $this->makeUserWithRole('editor');
        $page = $this->createPage([
            'title' => 'Restorable page',
            'content_json' => [
                'version' => '1.0',
                'layout' => 'landing',
                'sections' => [[
                    'id' => 'page-section',
                    'settings' => [],
                    'blocks' => [[
                        'id' => 'page-block',
                        'type' => 'heading',
                        'settings' => ['text' => 'Current copy'],
                    ]],
                ]],
            ],
        ], [
            'title' => 'Current SEO',
            'description' => 'Current description',
            'robots' => 'index, follow',
            'include_in_sitemap' => true,
        ]);

        $revision = PageRevision::query()->create([
            'page_id' => $page->id,
            'user_id' => $editor->id,
            'title' => 'Restored title',
            'content_json' => [
                'sections' => [[
                    'id' => 'revision-section',
                    'settings' => [],
                    'blocks' => [[
                        'id' => 'revision-block',
                        'type' => 'text',
                        'settings' => ['content' => 'Revision copy'],
                    ]],
                ]],
            ],
            'custom_fields_json' => [],
            'seo_json' => [
                'title' => 'Revision SEO',
                'description' => 'Revision description',
                'canonical_url' => 'https://example.com/revision',
                'robots' => 'noindex, follow',
                'og_title' => 'Revision OG',
                'og_description' => 'Revision OG description',
                'og_image' => null,
                'schema_json' => ['@type' => 'WebPage'],
                'include_in_sitemap' => false,
            ],
            'action' => 'manual-save',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($editor)
            ->postJson("/admin/pages/{$page->id}/revisions/{$revision->id}/restore");

        $response->assertOk();
        $response->assertJsonPath('ok', true);

        $page->refresh();
        $page->load('seoMeta');

        $this->assertSame('1.0', $page->content_json['version']);
        $this->assertSame('landing', $page->content_json['layout']);
        $this->assertSame('text', $page->content_json['sections'][0]['blocks'][0]['type']);
        $this->assertSame('Revision SEO', $page->seoMeta?->title);
        $this->assertSame('noindex, follow', $page->seoMeta?->robots);
        $this->assertFalse($page->seoMeta?->include_in_sitemap ?? true);
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
