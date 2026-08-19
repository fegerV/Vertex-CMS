<?php

namespace Tests\Feature;

use App\Builder\Config\SectionRegistry;
use Tests\TestCase;

class BuilderSectionRegistryTest extends TestCase
{
    public function test_section_registry_exposes_contract_metadata(): void
    {
        $config = SectionRegistry::config();

        $this->assertSame(['content', 'style', 'advanced'], $config['tabs']);
        $this->assertArrayHasKey('default_settings', $config);
        $this->assertArrayHasKey('surface_tokens', $config);
        $this->assertArrayHasKey('presets', $config);
        $this->assertArrayHasKey('capabilities', $config);
        $this->assertArrayHasKey('actions', $config);
        $this->assertArrayHasKey('commands', $config);
        $this->assertArrayHasKey('presentation', $config);
        $this->assertTrue($config['capabilities']['presets']);
        $this->assertSame('hero-surface', $config['presets'][0]['id']);
        $this->assertSame('#ecfeff', $config['presets'][0]['settings']['background_color']);
        $this->assertSame('quick-add', $config['actions'][0]['id']);
        $this->assertSame('move', $config['actions'][1]['id']);
        $this->assertSame('delete', $config['actions'][5]['id']);
        $this->assertSame('duplicate-section', $config['commands'][1]['id']);
        $this->assertSame('delete-section', $config['commands'][2]['id']);
        $this->assertSame('hover-or-selected', $config['presentation']['toolbar']['visibility']);
        $this->assertTrue($config['presentation']['selection']['clear_block_selection']);
    }
}
