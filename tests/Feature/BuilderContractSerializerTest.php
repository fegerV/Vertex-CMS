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
        $this->assertSame('content', $blocks['heading']['fields']['text']['group']);
        $this->assertArrayHasKey('quick_add', $blocks['button']['editor']);
        $this->assertArrayHasKey('capabilities', $blocks['image']['editor']);
        $this->assertArrayHasKey('presentation', $blocks['button']['editor']);
        $this->assertSame('move-up', $blocks['button']['editor']['actions'][0]['id']);
        $this->assertSame('duplicate-block', $blocks['button']['editor']['commands'][0]['id']);
        $this->assertSame('multi', $blocks['button']['editor']['presentation']['selection']['mode']);
        $this->assertSame('hover-or-selected', $blocks['button']['editor']['presentation']['toolbar']['visibility']);
    }
}
