<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuilderRegistryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedCore();
        $this->markApplicationAsInstalled();
    }

    public function test_builder_blocks_api_returns_registry_contract_metadata(): void
    {
        $editor = $this->makeUserWithRole('editor');

        $response = $this->actingAs($editor)->getJson('/admin/api/builder/blocks');

        $response->assertOk();
        $response->assertJsonPath('registry_version', '1.0');
        $response->assertJsonStructure([
            'registry_version',
            'blocks' => [
                '*' => [
                    'type',
                    'name',
                    'category',
                    'fields',
                    'editor' => [
                        'component',
                        'kind',
                        'supports',
                    ],
                    'default_block' => [
                        'type',
                        'settings',
                    ],
                ],
            ],
        ]);

        $heading = collect($response->json('blocks'))->firstWhere('type', 'heading');

        $this->assertNotNull($heading);
        $this->assertSame('vc-builder-block-heading', $heading['editor']['component']);
        $this->assertSame('heading', $heading['default_block']['type']);
        $this->assertArrayHasKey('text', $heading['default_block']['settings']);
    }
}
