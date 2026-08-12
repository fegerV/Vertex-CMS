<?php

namespace Tests\Feature;

use App\Builder\Services\DesignSystemService;
use App\Core\Services\SettingsService;
use App\Core\Support\SettingCatalog;
use Mockery;
use PHPUnit\Framework\TestCase;

class BuilderDesignSystemTest extends TestCase
{
    public function test_design_catalog_exposes_global_brand_controls(): void
    {
        $fields = SettingCatalog::groups()['design']['fields'];

        $this->assertArrayHasKey('design.primary_color', $fields);
        $this->assertArrayHasKey('design.heading_font', $fields);
        $this->assertArrayHasKey('design.content_width', $fields);
        $this->assertArrayHasKey('design.button_radius', $fields);
    }

    public function test_tokens_are_sanitized_and_constrained_before_becoming_css(): void
    {
        $settings = Mockery::mock(SettingsService::class);
        $settings->shouldReceive('get')->andReturn(null);
        $service = new DesignSystemService($settings);

        $tokens = $service->normalize([
            'primary' => 'red;}</style><script>',
            'heading_font' => 'Inter"; color:red',
            'body_font' => 'Source Sans 3',
            'content_width' => 99999,
            'button_radius' => -10,
        ]);

        $this->assertSame('#0f766e', $tokens['primary']);
        $this->assertSame('Manrope', $tokens['heading_font']);
        $this->assertSame('Source Sans 3', $tokens['body_font']);
        $this->assertSame(1920, $tokens['content_width']);
        $this->assertSame(0, $tokens['button_radius']);
        $this->assertCount(5, $tokens['palette']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
