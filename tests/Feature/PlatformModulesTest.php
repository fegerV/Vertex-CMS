<?php

namespace Tests\Feature;

use App\AI\Services\VisualPageGenerator;
use App\Automation\Services\AutomationEngine;
use App\Builder\Services\PageBuilderService;
use App\Compliance\Services\ComplianceAuditService;
use App\Integrations\Services\N8nService;
use App\Localization\Services\ContentTranslator;
use App\Marketplace\Services\MarketplaceCatalog;
use App\Modules\Services\PlatformModuleRegistry;
use App\Recommendations\Services\RecommendationEngine;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class PlatformModulesTest extends TestCase
{
    public function test_registry_exposes_all_twelve_post_v1_modules(): void
    {
        $registry = app(PlatformModuleRegistry::class);
        $this->assertCount(12, $registry->all());
        foreach (array_keys($registry->all()) as $module) {
            $this->assertTrue($registry->available($module), "Module {$module} is not registered.");
        }
    }

    public function test_marketplace_separates_module_and_theme_catalogs(): void
    {
        config(['platform-modules.marketplace.catalog_url' => 'https://market.example.test/v1']);
        Http::fake([
            '*/modules' => Http::response(['data' => [['id' => 'seo-pro', 'version' => '1.0.0', 'download_url' => 'https://cdn.example/module.zip'], ['invalid' => true]]]),
            '*/themes' => Http::response(['data' => [['id' => 'studio', 'version' => '2.0.0', 'download_url' => 'https://cdn.example/theme.zip']]]),
        ]);
        $catalog = app(MarketplaceCatalog::class);
        $this->assertSame('seo-pro', $catalog->modules()[0]['id']);
        $this->assertSame('studio', $catalog->themes()[0]['id']);
    }

    public function test_localization_resolves_fallback_and_builds_localized_uri(): void
    {
        config(['platform-modules.localization.default' => 'ru', 'platform-modules.localization.supported' => ['ru', 'en']]);
        $translator = app(ContentTranslator::class);
        $this->assertSame('Hello', $translator->resolve(['ru' => 'Привет', 'en' => 'Hello'], 'en'));
        $this->assertSame('/en/about', $translator->localizedUri('/about', 'en'));
        $this->assertSame('/about', $translator->localizedUri('/about', 'ru'));
    }

    public function test_n8n_payload_is_signed_and_idempotent(): void
    {
        config(['platform-modules.n8n.webhook_url' => 'https://n8n.example.test/webhook/vertex', 'platform-modules.n8n.secret' => 'secret']);
        Http::fake(['*' => Http::response(['accepted' => true])]);
        $this->assertTrue(app(N8nService::class)->trigger('publish', ['page_id' => 1])['accepted']);
        Http::assertSent(fn ($request) => $request->hasHeader('X-Vertex-Signature') && $request->hasHeader('Idempotency-Key'));
    }

    public function test_automation_runs_only_matching_steps(): void
    {
        $workflow = ['steps' => [
            ['type' => 'notify', 'when' => ['event' => 'published'], 'config' => ['channel' => 'telegram']],
            ['type' => 'archive', 'when' => ['event' => 'deleted']],
        ]];
        $results = app(AutomationEngine::class)->run($workflow, ['event' => 'published'], fn ($type, $config) => compact('type', 'config'));
        $this->assertCount(1, $results);
        $this->assertSame('notify', $results[0]['type']);
    }

    public function test_visual_ai_output_is_normalized_and_validated_before_use(): void
    {
        $builder = Mockery::mock(PageBuilderService::class);
        $builder->expects('normalizeSections')->once()->andReturn([['id' => 'hero', 'blocks' => []]]);
        $builder->expects('validateBlocks')->once()->andReturn([]);
        $generator = new VisualPageGenerator($builder);

        $document = $generator->generate(
            ['title' => 'Landing', 'goal' => 'Collect leads'],
            fn (array $prompt) => ['layout' => 'landing', 'sections' => [['prompt' => $prompt]]],
        );

        $this->assertSame('landing', $document['layout']);
        $this->assertSame('hero', $document['sections'][0]['id']);
    }

    public function test_recommendations_are_personalized_and_exclude_seen_items(): void
    {
        $ranked = app(RecommendationEngine::class)->rank([
            ['id' => 1, 'tags' => ['php'], 'weight' => 1],
            ['id' => 2, 'tags' => ['laravel'], 'weight' => 2],
            ['id' => 3, 'tags' => ['laravel'], 'weight' => 1],
        ], ['interests' => ['laravel'], 'seen_ids' => [2]], 2);
        $this->assertSame([3, 1], array_column($ranked, 'id'));
    }

    public function test_compliance_audit_reports_critical_controls(): void
    {
        $failed = app(ComplianceAuditService::class)->audit([]);
        $this->assertFalse($failed['compliant']);
        $passed = app(ComplianceAuditService::class)->audit([
            'privacy_policy_url' => 'https://example.test/privacy', 'data_export_enabled' => true,
            'data_deletion_enabled' => true, 'consent_logging' => true, 'retention_days' => 365,
        ]);
        $this->assertTrue($passed['compliant']);
        $this->assertSame(100, $passed['score']);
    }
}
