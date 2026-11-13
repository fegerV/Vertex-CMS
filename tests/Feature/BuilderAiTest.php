<?php

namespace Tests\Feature;

use App\Core\Services\SettingsService;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BuilderAiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedCore();
        $this->markApplicationAsInstalled();
    }

    public function test_builder_ai_agent_returns_validated_sections_and_drops_unknown_blocks(): void
    {
        $editor = $this->makeUserWithRole('editor');
        $page = $this->createPage([
            'title' => 'Кофейня',
            'slug' => 'coffee',
            'uri' => '/coffee',
            'content_json' => [
                'version' => '1.0',
                'layout' => 'default',
                'sections' => [],
            ],
        ]);

        $this->configureAi('openai');

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'message' => 'Собрал лендинг кофейни',
                            'sections' => [[
                                'id' => 'ai-hero',
                                'settings' => ['padding_top' => 80, 'padding_bottom' => 80],
                                'blocks' => [
                                    ['type' => 'heading', 'settings' => ['text' => 'Кофе на каждый день', 'level' => 'h1']],
                                    ['type' => 'text', 'settings' => ['content' => 'Свежая обжарка и быстрая доставка.']],
                                    ['type' => 'unknown-widget', 'settings' => []],
                                ],
                            ]],
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                ]],
                'usage' => ['total_tokens' => 120],
            ], 200),
        ]);

        $response = $this->actingAs($editor)->postJson("/admin/pages/{$page->id}/builder/ai", [
            'instruction' => 'Сделай лендинг кофейни',
            'sections' => [],
        ]);

        $response->assertOk();
        $response->assertJsonPath('ok', true);
        $response->assertJsonPath('message', 'Собрал лендинг кофейни');
        $response->assertJsonPath('sections.0.blocks.0.type', 'heading');
        $response->assertJsonPath('sections.0.blocks.1.type', 'text');
        $response->assertJsonPath('provider.id', 'openai');
        $this->assertContains('unknown-widget', $response->json('dropped_blocks'));
        $this->assertCount(2, $response->json('sections.0.blocks'));

        Http::assertSent(function ($request) {
            $payload = $request->data();

            return str_contains((string) ($payload['messages'][0]['content'] ?? ''), 'VertexCMS')
                && str_contains((string) ($payload['messages'][1]['content'] ?? ''), 'Сделай лендинг кофейни')
                && str_contains((string) ($payload['messages'][1]['content'] ?? ''), 'block_catalog');
        });

        $log = ActivityLog::query()->where('action', 'builder.ai.generate')->firstOrFail();
        $this->assertSame('openai', $log->metadata_json['provider']);
        $this->assertSame(1, $log->metadata_json['sections']);
        $this->assertSame(2, $log->metadata_json['blocks']);

        $page->refresh();
        $this->assertSame([], $page->content_json['sections'], 'AI proposal must not be persisted automatically.');
    }

    public function test_builder_ai_agent_retries_once_with_validator_feedback(): void
    {
        $editor = $this->makeUserWithRole('editor');
        $page = $this->createPage();

        $this->configureAi('openai');

        Http::fake([
            'api.openai.com/*' => Http::sequence()
                ->push(['choices' => [['message' => ['content' => 'not json at all']]]], 200)
                ->push([
                    'choices' => [[
                        'message' => [
                            'content' => json_encode([
                                'message' => 'Исправлено',
                                'sections' => [[
                                    'blocks' => [
                                        ['type' => 'heading', 'settings' => ['text' => 'Готово', 'level' => 'h2']],
                                    ],
                                ]],
                            ], JSON_UNESCAPED_UNICODE),
                        ],
                    ]],
                ], 200),
        ]);

        $response = $this->actingAs($editor)->postJson("/admin/pages/{$page->id}/builder/ai", [
            'instruction' => 'Добавь заголовок',
        ]);

        $response->assertOk();
        $response->assertJsonPath('ok', true);
        $response->assertJsonPath('sections.0.blocks.0.settings.text', 'Готово');
        Http::assertSentCount(2);
    }

    public function test_builder_ai_agent_reports_disabled_module_and_unconfigured_provider(): void
    {
        $editor = $this->makeUserWithRole('editor');
        $page = $this->createPage();

        Http::fake();

        app(SettingsService::class)->setMany([
            'ai.enabled' => false,
            'ai.default_provider' => 'openai',
        ]);

        $this->actingAs($editor)
            ->postJson("/admin/pages/{$page->id}/builder/ai", ['instruction' => 'Сделай лендинг'])
            ->assertStatus(422)
            ->assertJsonPath('ok', false)
            ->assertJsonPath('error', 'ai_disabled');

        app(SettingsService::class)->setMany([
            'ai.enabled' => true,
            'ai.default_provider' => 'openai',
            'ai.openai_api_key' => '',
        ]);

        $this->actingAs($editor)
            ->postJson("/admin/pages/{$page->id}/builder/ai", ['instruction' => 'Сделай лендинг'])
            ->assertStatus(422)
            ->assertJsonPath('ok', false)
            ->assertJsonPath('error', 'ai_provider_not_configured');

        Http::assertNothingSent();
    }

    public function test_builder_ai_agent_requires_pages_edit_permission(): void
    {
        $viewer = $this->makeUserWithRole('viewer');
        $page = $this->createPage();

        $this->actingAs($viewer)
            ->postJson("/admin/pages/{$page->id}/builder/ai", ['instruction' => 'Сделай лендинг'])
            ->assertForbidden();
    }

    public function test_builder_screen_exposes_ai_assistant_config(): void
    {
        $editor = $this->makeUserWithRole('editor');
        $page = $this->createPage();
        $this->configureAi('openai');

        $html = $this->actingAs($editor)
            ->get(route('admin.pages.builder', $page))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('"ai":{"enabled":true', $html);
        $this->assertStringContainsString('"configured":true', $html);
    }

    public function test_builder_ai_agent_rewrites_only_the_selected_block(): void
    {
        $editor = $this->makeUserWithRole('editor');
        $page = $this->createPage([
            'content_json' => $this->twoSectionContent(),
        ]);

        $this->configureAi('openai');

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'message' => 'Переписал заголовок',
                            'block' => [
                                'type' => 'heading',
                                'settings' => ['text' => 'Новый заголовок', 'level' => 'h1'],
                            ],
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                ]],
            ], 200),
        ]);

        $response = $this->actingAs($editor)->postJson("/admin/pages/{$page->id}/builder/ai", [
            'instruction' => 'Перепиши заголовок короче',
            'scope' => 'block',
            'sections' => $this->twoSectionContent()['sections'],
            'selection' => ['section_index' => 0, 'block_index' => 0],
        ]);

        $response->assertOk();
        $response->assertJsonPath('ok', true);
        $response->assertJsonPath('scope', 'block');
        $response->assertJsonPath('target.label', 'Секция 1, блок 1');

        $sections = $response->json('sections');

        $this->assertSame('Новый заголовок', $sections[0]['blocks'][0]['settings']['text']);
        $this->assertSame('h1', $sections[0]['blocks'][0]['settings']['level']);
        $this->assertSame('block-one', $sections[0]['blocks'][0]['id'], 'Block id must survive targeted edits.');
        $this->assertSame('Оставить как есть', $sections[0]['blocks'][1]['settings']['content']);
        $this->assertSame('section-one', $sections[0]['id']);
        $this->assertSame(32, $sections[0]['settings']['padding_top']);
        $this->assertSame('Вторая секция', $sections[1]['blocks'][0]['settings']['content']);

        Http::assertSent(function ($request) {
            $payload = $request->data();
            $body = (string) ($payload['messages'][1]['content'] ?? '');

            return str_contains($body, '"scope": "block"')
                && str_contains($body, 'target_block')
                && str_contains($body, 'Перепиши заголовок короче');
        });
    }

    public function test_builder_ai_agent_rewrites_only_the_selected_section_and_keeps_other_sections(): void
    {
        $editor = $this->makeUserWithRole('editor');
        $page = $this->createPage([
            'content_json' => $this->twoSectionContent(),
        ]);

        $this->configureAi('openai');

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'message' => 'Пересобрал первую секцию',
                            'section' => [
                                'settings' => ['padding_top' => 96],
                                'blocks' => [
                                    ['type' => 'heading', 'settings' => ['text' => 'Секция пересобрана', 'level' => 'h2']],
                                    ['type' => 'button', 'settings' => ['text' => 'Записаться', 'url' => '#form']],
                                ],
                            ],
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                ]],
            ], 200),
        ]);

        $response = $this->actingAs($editor)->postJson("/admin/pages/{$page->id}/builder/ai", [
            'instruction' => 'Пересобери первую секцию',
            'scope' => 'section',
            'sections' => $this->twoSectionContent()['sections'],
            'selection' => ['section_index' => 0],
        ]);

        $response->assertOk();
        $response->assertJsonPath('scope', 'section');

        $sections = $response->json('sections');

        $this->assertCount(2, $sections[0]['blocks']);
        $this->assertSame('heading', $sections[0]['blocks'][0]['type']);
        $this->assertSame('block-one', $sections[0]['blocks'][0]['id'], 'Same-type blocks keep their id.');
        $this->assertSame('button', $sections[0]['blocks'][1]['type']);
        $this->assertNotSame('block-two', $sections[0]['blocks'][1]['id']);
        $this->assertSame('section-one', $sections[0]['id']);
        $this->assertSame(96, $sections[0]['settings']['padding_top']);
        $this->assertSame('#f8fafc', $sections[0]['settings']['background_color'], 'Previous settings must be preserved.');
        $this->assertSame('Вторая секция', $sections[1]['blocks'][0]['settings']['content']);
        $this->assertSame('section-two', $sections[1]['id']);
    }

    public function test_builder_ai_agent_rejects_invalid_target_without_calling_provider(): void
    {
        $editor = $this->makeUserWithRole('editor');
        $page = $this->createPage([
            'content_json' => $this->twoSectionContent(),
        ]);

        $this->configureAi('openai');
        Http::fake();

        $this->actingAs($editor)->postJson("/admin/pages/{$page->id}/builder/ai", [
            'instruction' => 'Перепиши блок',
            'scope' => 'block',
            'sections' => $this->twoSectionContent()['sections'],
            'selection' => ['section_index' => 0, 'block_index' => 9],
        ])->assertStatus(422)
            ->assertJsonPath('ok', false)
            ->assertJsonPath('error', 'ai_invalid_target');

        Http::assertNothingSent();
    }

    private function twoSectionContent(): array
    {
        return [
            'version' => '1.0',
            'layout' => 'default',
            'sections' => [
                [
                    'id' => 'section-one',
                    'settings' => ['padding_top' => 32, 'background_color' => '#f8fafc'],
                    'blocks' => [
                        ['id' => 'block-one', 'type' => 'heading', 'settings' => ['text' => 'Старый заголовок', 'level' => 'h2']],
                        ['id' => 'block-two', 'type' => 'text', 'settings' => ['content' => 'Оставить как есть']],
                    ],
                ],
                [
                    'id' => 'section-two',
                    'settings' => [],
                    'blocks' => [
                        ['id' => 'block-three', 'type' => 'text', 'settings' => ['content' => 'Вторая секция']],
                    ],
                ],
            ],
        ];
    }

    private function configureAi(string $provider): void
    {
        app(SettingsService::class)->setMany([
            'ai.enabled' => true,
            'ai.default_provider' => $provider,
            'ai.default_model' => 'gpt-4o-mini',
            'ai.openai_api_key' => 'test-openai-key',
            'ai.store_prompts' => false,
            'ai.store_responses' => false,
        ]);
    }
}
