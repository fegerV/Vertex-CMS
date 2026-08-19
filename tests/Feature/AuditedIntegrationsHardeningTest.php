<?php

namespace Tests\Feature;

use App\AI\Services\AiProviderRegistry;
use App\AI\Services\SiteWizardService;
use App\Core\Services\SettingsService;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Services\AI\SmartSearchService;
use App\Services\Webhooks\WebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Mockery;
use Tests\TestCase;

class AuditedIntegrationsHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_wizard_saves_pages_and_menu_using_current_schema(): void
    {
        $service = new SiteWizardService(
            Mockery::mock(AiProviderRegistry::class),
            Mockery::mock(SettingsService::class),
        );

        $result = $service->saveSiteStructure([
            'pages' => [[
                'title' => 'About us',
                'uri' => '/about',
                'content' => '<p>Our story</p>',
                'meta_title' => 'About Vertex',
                'meta_description' => 'Our company story.',
            ]],
            'menu' => [['title' => 'About', 'url' => '/about']],
        ]);

        $this->assertTrue($result['success'], $result['error'] ?? 'Wizard save failed.');
        $page = Page::query()->firstOrFail();
        $item = MenuItem::query()->firstOrFail();

        $this->assertSame('/about', $page->uri);
        $this->assertSame('<p>Our story</p>', $page->content_json['blocks'][0]['data']['content']);
        $this->assertSame('About Vertex', $page->seoMeta->title);
        $this->assertSame(0, $item->sort_order);
        $this->assertSame($page->id, $item->entity_id);
        $this->assertSame('primary', Menu::query()->firstOrFail()->location);
    }

    public function test_smart_search_rejects_arbitrary_php_classes_before_querying(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(SmartSearchService::class)->searchWithFilters('secret', [], SettingsService::class);
    }

    public function test_webhook_signature_covers_the_exact_body_and_timestamp(): void
    {
        $body = json_encode(['event' => 'order.created', 'timestamp' => 123, 'data' => ['id' => 42]]);
        $signature = hash_hmac('sha256', $body.'123', 'secret');
        $service = app(WebhookService::class);

        $this->assertTrue($service->verifySignature($body, $signature, 'secret', 123));
        $this->assertFalse($service->verifySignature($body, $signature, 'secret', 124));
    }
}
