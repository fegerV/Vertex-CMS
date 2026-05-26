<?php

namespace Tests\Feature;

use App\Builder\Config\BlockRegistry;
use App\Builder\Support\BuilderContractSerializer;
use Tests\TestCase;

class BuilderContractSerializerTest extends TestCase
{
    public function test_builder_contract_serializer_normalizes_registry_into_runtime_contract(): void
    {
        $serializer = app(BuilderContractSerializer::class);
        $blocks = $serializer->serializeRegistry(BlockRegistry::all());

        $this->assertArrayHasKey('heading', $blocks);
        $this->assertSame('vc-builder-block-heading', $blocks['heading']['editor']['component']);
        $this->assertSame('H', $blocks['heading']['editor']['preview']['badge']);
        $this->assertSame('content', $blocks['heading']['editor']['inspector']['default_tab']);
        $this->assertSame('Typography', $blocks['heading']['editor']['packs']['typography-pack']['label']);
        $this->assertContains('text', $blocks['heading']['editor']['packs']['typography-pack']['fields']);
        $this->assertSame('content', $blocks['heading']['fields']['text']['group']);
        $this->assertSame(100, $blocks['heading']['fields']['text']['priority']);
        $this->assertSame('primary', $blocks['heading']['fields']['text']['importance']);
        $this->assertSame('stacked', $blocks['heading']['fields']['text']['layout']['variant']);
        $this->assertSame('heading-structure', $blocks['heading']['fields']['level']['layout']['row']);
        $this->assertSame('segmented-select', $blocks['heading']['fields']['level']['control']['variant']);
        $this->assertSame('typography', $blocks['heading']['fields']['level']['control']['family']);
        $this->assertSame('Typography', $blocks['heading']['fields']['level']['control']['family_label']);
        $this->assertSame('typography-pack', $blocks['heading']['fields']['level']['control']['pack']);
        $this->assertSame('Typography', $blocks['heading']['fields']['level']['control']['pack_label']);
        $this->assertSame('color-swatch', $blocks['heading']['fields']['color']['control']['variant']);
        $this->assertSame('surface', $blocks['heading']['fields']['color']['control']['family']);
        $this->assertSame('surface-pack', $blocks['heading']['fields']['color']['control']['pack']);
        $this->assertSame('spacing-slider', $blocks['image']['fields']['width']['control']['variant']);
        $this->assertSame('spacing', $blocks['image']['fields']['width']['control']['family']);
        $this->assertSame('spacing-pack', $blocks['image']['fields']['width']['control']['pack']);
        $this->assertSame('link-composer', $blocks['button']['fields']['url']['control']['variant']);
        $this->assertSame('link', $blocks['button']['fields']['url']['control']['family']);
        $this->assertSame('button-treatment-pack', $blocks['button']['fields']['style']['control']['pack']);
        $this->assertSame('Button treatment', $blocks['button']['editor']['packs']['button-treatment-pack']['label']);
        $this->assertArrayHasKey('quick_add', $blocks['button']['editor']);
        $this->assertArrayHasKey('capabilities', $blocks['image']['editor']);
        $this->assertArrayHasKey('presentation', $blocks['button']['editor']);
        $this->assertArrayHasKey('inline_editing', $blocks['heading']['editor']);
        $this->assertSame('move-up', $blocks['button']['editor']['actions'][0]['id']);
        $this->assertSame('inline-edit', $blocks['button']['editor']['commands'][0]['id']);
        $this->assertSame('duplicate-block', $blocks['button']['editor']['commands'][1]['id']);
        $this->assertSame('multi', $blocks['button']['editor']['presentation']['selection']['mode']);
        $this->assertSame('hover-or-selected', $blocks['button']['editor']['presentation']['toolbar']['visibility']);
        $this->assertTrue($blocks['heading']['editor']['inline_editing']['enabled']);
        $this->assertSame('double-click', $blocks['heading']['editor']['inline_editing']['trigger']);
        $this->assertSame('Edit heading', $blocks['heading']['editor']['inline_editing']['label']);
    }

    public function test_builder_contract_serializer_adds_catalog_pack_recipes_for_unmigrated_blocks(): void
    {
        $serializer = app(BuilderContractSerializer::class);
        $blocks = $serializer->serializeRegistry(BlockRegistry::all());

        $this->assertSame('List content', $blocks['list']['editor']['packs']['content-pack']['label']);
        $this->assertContains('items', $blocks['list']['editor']['packs']['content-pack']['fields']);
        $this->assertSame('Column layout', $blocks['columns']['editor']['packs']['layout-pack']['label']);
        $this->assertContains('gap', $blocks['columns']['editor']['packs']['layout-pack']['fields']);
        $this->assertSame('Form behavior', $blocks['form']['editor']['packs']['form-behavior-pack']['label']);
        $this->assertContains('notify_admin', $blocks['form']['editor']['packs']['form-behavior-pack']['fields']);
        $this->assertSame('Progress', $blocks['progress-bar']['editor']['packs']['progress-pack']['label']);
        $this->assertContains('show_label', $blocks['progress-bar']['editor']['packs']['progress-pack']['fields']);
    }
}
