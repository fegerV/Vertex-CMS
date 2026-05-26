<?php

namespace Tests\Feature;

use App\Builder\Config\BlockPackRegistry;
use Tests\TestCase;

class BuilderBlockPackRegistryTest extends TestCase
{
    public function test_block_pack_registry_exposes_builder_pack_recipes(): void
    {
        $all = BlockPackRegistry::all();
        $hero = BlockPackRegistry::for('hero');
        $heading = BlockPackRegistry::for('heading');
        $form = BlockPackRegistry::for('form');
        $progress = BlockPackRegistry::for('progress-bar');

        $this->assertArrayHasKey('heading', $all);
        $this->assertArrayHasKey('hero', $all);
        $this->assertArrayHasKey('form', $all);
        $this->assertArrayHasKey('progress-bar', $all);
        $this->assertSame('Typography', $heading['typography-pack']['label']);
        $this->assertContains('text', $heading['typography-pack']['fields']);
        $this->assertSame('Button treatment', $hero['button-treatment-pack']['label']);
        $this->assertContains('button_text', $hero['button-treatment-pack']['fields']);
        $this->assertSame('Media settings', $hero['media-settings-pack']['label']);
        $this->assertContains('background', $hero['media-settings-pack']['fields']);
        $this->assertSame('Form behavior', $form['form-behavior-pack']['label']);
        $this->assertContains('notify_admin', $form['form-behavior-pack']['fields']);
        $this->assertSame('Progress', $progress['progress-pack']['label']);
        $this->assertContains('show_label', $progress['progress-pack']['fields']);
    }
}
