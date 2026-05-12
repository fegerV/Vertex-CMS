<?php

namespace Tests\Feature;

use App\Core\Services\SettingsService;
use App\Admin\Http\Controllers\SettingsController;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AiModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedCore();
        $this->markApplicationAsInstalled();
    }

    public function test_super_admin_can_store_ai_keys_but_regular_admin_cannot_overwrite_them(): void
    {
        $superAdmin = $this->makeUserWithRole('super-admin', ['email' => 'super@example.com']);
        $admin = $this->makeUserWithRole('admin', ['email' => 'admin@example.com']);

        $superAdminResponse = app(SettingsController::class)->update(
            $this->settingsUpdateRequest($superAdmin, [
                'ai' => [
                    'enabled' => '1',
                    'default_provider' => 'openai',
                    'default_model' => 'gpt-test',
                    'content_language' => 'en',
                    'brand_voice' => 'clear and direct',
                    'openai_api_key' => 'super-secret-openai-key',
                    'custom_api_key' => 'custom-secret-key',
                    'custom_api_base' => 'https://llm.example.test',
                ],
            ])
        );

        $this->assertSame(route('admin.settings.edit'), $superAdminResponse->getTargetUrl());

        /** @var SettingsService $settings */
        $settings = app(SettingsService::class);
        $settings->forgetCache();

        $this->assertSame('super-secret-openai-key', $settings->get('ai.openai_api_key'));
        $this->assertSame('custom-secret-key', $settings->get('ai.custom_api_key'));

        $masked = $settings->allMasked();
        $this->assertSame('********', $masked['ai.openai_api_key']);
        $this->assertSame('********', $masked['ai.custom_api_key']);

        $storedOpenAiSetting = Setting::query()
            ->where('group_name', 'ai')
            ->where('setting_key', 'openai_api_key')
            ->firstOrFail();

        $this->assertNotSame('super-secret-openai-key', $storedOpenAiSetting->setting_value);

        $adminResponse = app(SettingsController::class)->update(
            $this->settingsUpdateRequest($admin, [
                'ai' => [
                    'enabled' => '1',
                    'default_provider' => 'openai',
                    'default_model' => 'gpt-admin',
                    'content_language' => 'en',
                    'brand_voice' => 'concise',
                    'openai_api_key' => 'admin-should-not-overwrite',
                    'custom_api_key' => 'admin-should-not-overwrite-custom',
                    'custom_api_base' => 'https://llm.example.test',
                ],
            ])
        );

        $this->assertSame(route('admin.settings.edit'), $adminResponse->getTargetUrl());

        $settings->forgetCache();

        $this->assertSame('super-secret-openai-key', $settings->get('ai.openai_api_key'));
        $this->assertSame('custom-secret-key', $settings->get('ai.custom_api_key'));
        $this->assertSame('gpt-admin', $settings->get('ai.default_model'));

        $log = ActivityLog::query()->where('action', 'settings.edit')->latest('created_at')->firstOrFail();
        $metadata = $log->metadata_json;

        $this->assertArrayHasKey('secret_keys_updated', $metadata);
        $this->assertArrayNotHasKey('super-secret-openai-key', $metadata);
        $this->assertArrayNotHasKey('custom-secret-key', $metadata);
        $this->assertStringNotContainsString('admin-should-not-overwrite', json_encode($metadata, JSON_UNESCAPED_UNICODE));
    }

    public function test_editor_with_ai_use_can_list_configured_providers_and_generate_draft_only_response(): void
    {
        $editor = $this->makeUserWithRole('editor', ['email' => 'editor@example.com']);

        /** @var SettingsService $settings */
        $settings = app(SettingsService::class);
        $settings->setMany([
            'ai.enabled' => true,
            'ai.default_provider' => 'openai',
            'ai.default_model' => 'gpt-test',
            'ai.content_language' => 'en',
            'ai.brand_voice' => 'helpful and concise',
            'ai.openai_api_key' => 'provider-secret-key',
            'ai.store_prompts' => false,
            'ai.store_responses' => false,
        ]);

        $providersResponse = $this->actingAs($editor)->getJson('/admin/api/ai/providers');

        $providersResponse->assertOk();
        $providersResponse->assertJsonPath('meta.api_version', 'v1');
        $providersResponse->assertJsonPath('meta.draft_only', true);
        $providersResponse->assertJsonPath('data.enabled', true);
        $providersResponse->assertJsonPath('data.default_provider', 'openai');
        $providersResponse->assertJsonPath('data.items.0.id', 'openai');
        $providersResponse->assertJsonPath('data.items.0.configured', true);
        $providersResponse->assertJsonMissing(['openai_api_key' => 'provider-secret-key']);

        $chatResponse = $this->actingAs($editor)->postJson('/admin/api/ai/chat', [
            'action' => 'seo',
            'instruction' => 'Create a high-conversion SEO draft',
            'page' => [
                'title' => 'AI Landing Page',
                'uri' => '/ai-landing-page',
            ],
            'seo' => [
                'title' => '',
                'description' => '',
            ],
        ]);

        $chatResponse->assertOk();
        $chatResponse->assertJsonPath('meta.api_version', 'v1');
        $chatResponse->assertJsonPath('meta.draft_only', true);
        $chatResponse->assertJsonPath('meta.action', 'seo');
        $chatResponse->assertJsonPath('data.provider.id', 'openai');
        $chatResponse->assertJsonPath('data.draft.kind', 'seo');
        $this->assertStringContainsString('AI Landing Page', (string) $chatResponse->json('data.draft.preview'));
        $this->assertStringNotContainsString('provider-secret-key', $chatResponse->getContent());

        $log = ActivityLog::query()->where('action', 'ai.chat')->latest('created_at')->firstOrFail();
        $metadata = $log->metadata_json;

        $this->assertSame('seo', $metadata['action']);
        $this->assertSame('openai', $metadata['provider']);
        $this->assertTrue($metadata['draft_only']);
        $this->assertNull($metadata['prompt_preview']);
        $this->assertNull($metadata['response_preview']);
        $this->assertStringNotContainsString('provider-secret-key', json_encode($metadata, JSON_UNESCAPED_UNICODE));
    }

    public function test_ai_endpoints_enforce_permissions_and_return_stable_errors(): void
    {
        $viewer = $this->makeUserWithRole('viewer', ['email' => 'viewer@example.com']);
        $editor = $this->makeUserWithRole('editor', ['email' => 'editor2@example.com']);

        $this->actingAs($viewer)->getJson('/admin/api/ai/providers')
            ->assertForbidden()
            ->assertJsonPath('error.code', 'forbidden');

        $this->actingAs($viewer)->postJson('/admin/api/ai/chat', [
            'action' => 'text',
        ])->assertForbidden()
            ->assertJsonPath('error.code', 'forbidden');

        app(SettingsService::class)->setMany([
            'ai.enabled' => false,
            'ai.default_provider' => 'openai',
        ]);

        $disabledResponse = $this->actingAs($editor)->postJson('/admin/api/ai/chat', [
            'action' => 'text',
            'instruction' => 'Draft body copy',
            'page' => [
                'title' => 'Disabled AI',
                'uri' => '/disabled-ai',
            ],
        ]);

        $disabledResponse->assertStatus(422);
        $disabledResponse->assertJsonPath('error.code', 'ai_disabled');
        $disabledResponse->assertJsonPath('meta.draft_only', true);

        app(SettingsService::class)->setMany([
            'ai.enabled' => true,
            'ai.default_provider' => 'openai',
            'ai.openai_api_key' => '',
        ]);

        $notConfiguredResponse = $this->actingAs($editor)->postJson('/admin/api/ai/chat', [
            'action' => 'builder',
            'instruction' => 'Draft a landing page',
            'page' => [
                'title' => 'Unconfigured AI',
                'uri' => '/unconfigured-ai',
            ],
        ]);

        $notConfiguredResponse->assertStatus(422);
        $notConfiguredResponse->assertJsonPath('error.code', 'ai_provider_not_configured');
        $notConfiguredResponse->assertJsonPath('meta.draft_only', true);
    }

    private function settingsPayload(array $overrides = []): array
    {
        return array_replace_recursive([
            'site' => [
                'name' => 'VertexCMS',
                'url' => 'https://example.test',
                'description' => 'Test site',
                'locale' => 'ru',
                'timezone' => 'UTC',
            ],
            'api' => [
                'version' => 'v1',
            ],
            'cache' => [
                'driver' => 'file',
            ],
            'ai' => [
                'enabled' => '0',
                'default_provider' => 'openai',
                'default_model' => '',
                'content_language' => 'en',
                'brand_voice' => '',
                'custom_api_base' => '',
            ],
        ], $overrides);
    }

    private function settingsUpdateRequest($user, array $overrides = []): Request
    {
        $request = Request::create('/admin/settings', 'POST', [
            '_method' => 'PUT',
        ]);
        $request->request->set('settings', $this->settingsPayload($overrides));

        $request->setUserResolver(fn () => $user);

        return $request;
    }
}
