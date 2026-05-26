<?php

namespace Tests\Feature;

use App\Builder\Config\BlockRegistry;
use Tests\TestCase;

class BuilderBlockRegistryPacksTest extends TestCase
{
    public function test_primary_blocks_define_pack_recipes_in_main_registry(): void
    {
        $heading = BlockRegistry::get('heading');
        $button = BlockRegistry::get('button');
        $hero = BlockRegistry::get('hero');

        $this->assertSame('Typography', $heading['editor']['packs']['typography-pack']['label']);
        $this->assertContains('color', $heading['editor']['packs']['surface-pack']['fields']);
        $this->assertSame('Button treatment', $button['editor']['packs']['button-treatment-pack']['label']);
        $this->assertContains('style', $button['editor']['packs']['button-treatment-pack']['fields']);
        $this->assertSame('Media settings', $hero['editor']['packs']['media-settings-pack']['label']);
        $this->assertContains('background', $hero['editor']['packs']['media-settings-pack']['fields']);
    }
}
