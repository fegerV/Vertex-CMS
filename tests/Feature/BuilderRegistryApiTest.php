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
            'blocks',
            'entries' => [
                '*' => [
                    'type',
                    'name',
                    'category',
                    'fields',
                    'editor' => [
                        'component',
                        'kind',
                        'supports',
                        'tabs',
                    ],
                    'default_block' => [
                        'type',
                        'settings',
                    ],
                ],
            ],
        ]);

        $heading = $response->json('blocks.heading');
        $video = $response->json('blocks.video');
        $image = $response->json('blocks.image');
        $button = $response->json('blocks.button');

        $this->assertNotNull($heading);
        $this->assertNotNull($video);
        $this->assertNotNull($image);
        $this->assertNotNull($button);
        $this->assertSame('vc-builder-block-heading', $heading['editor']['component']);
        $this->assertSame('heading', $heading['default_block']['type']);
        $this->assertArrayHasKey('text', $heading['default_block']['settings']);
        $this->assertSame('Typography', $heading['editor']['packs']['typography-pack']['label']);
        $this->assertContains('content', $heading['editor']['tabs']);
        $this->assertSame('content', $heading['editor']['inspector']['default_tab']);
        $this->assertSame('content', $heading['fields']['text']['group']);
        $this->assertSame(100, $heading['fields']['text']['priority']);
        $this->assertSame('primary', $heading['fields']['text']['importance']);
        $this->assertSame('stacked', $heading['fields']['text']['layout']['variant']);
        $this->assertSame('heading-structure', $heading['fields']['level']['layout']['row']);
        $this->assertSame('segmented-select', $heading['fields']['level']['control']['variant']);
        $this->assertSame('typography', $heading['fields']['level']['control']['family']);
        $this->assertSame('typography-pack', $heading['fields']['level']['control']['pack']);
        $this->assertSame('color-swatch', $heading['fields']['color']['control']['variant']);
        $this->assertSame('surface', $heading['fields']['color']['control']['family']);
        $this->assertSame('surface-pack', $heading['fields']['color']['control']['pack']);
        $this->assertSame('style', $heading['fields']['color']['group']);
        $this->assertSame('H', $heading['editor']['preview']['badge']);
        $this->assertSame('content', $video['fields']['url']['group']);
        $this->assertSame('Paste the YouTube, Vimeo or direct video URL here.', $video['fields']['url']['help']);
        $this->assertSame('link-composer', $video['fields']['url']['control']['variant']);
        $this->assertSame('link', $video['fields']['url']['control']['family']);
        $this->assertSame('media-settings-pack', $video['fields']['url']['control']['pack']);
        $this->assertContains('advanced', $video['editor']['tabs']);
        $this->assertSame('Video placeholder', $video['editor']['preview']['empty_state']['title']);
        $this->assertSame('Image placeholder', $image['editor']['preview']['empty_state']['title']);
        $this->assertSame('spacing-slider', $image['fields']['width']['control']['variant']);
        $this->assertSame('spacing', $image['fields']['width']['control']['family']);
        $this->assertSame('spacing-pack', $image['fields']['width']['control']['pack']);
        $this->assertSame('Media settings', $image['editor']['packs']['media-settings-pack']['label']);
        $this->assertSame('Call to action with link', $button['editor']['quick_add']['hint']);
        $this->assertContains('cta', $button['editor']['quick_add']['keywords']);
        $this->assertSame('link-composer', $button['fields']['url']['control']['variant']);
        $this->assertSame('link', $button['fields']['url']['control']['family']);
        $this->assertSame('button-treatment-pack', $button['fields']['style']['control']['pack']);
        $this->assertSame('Button treatment', $button['editor']['packs']['button-treatment-pack']['label']);
        $this->assertTrue($button['editor']['capabilities']['duplicate']);
        $this->assertSame('move-up', $button['editor']['actions'][0]['id']);
        $this->assertSame('delete', $button['editor']['actions'][3]['id']);
        $this->assertSame('inline-edit', $button['editor']['commands'][0]['id']);
        $this->assertSame('duplicate-block', $button['editor']['commands'][1]['id']);
        $this->assertSame('multi', $button['editor']['presentation']['selection']['mode']);
        $this->assertSame('hover-or-selected', $button['editor']['presentation']['toolbar']['visibility']);
        $this->assertSame('visual', $image['editor']['presentation']['canvas']['preview']);
        $this->assertTrue($heading['editor']['inline_editing']['enabled']);
        $this->assertSame('double-click', $heading['editor']['inline_editing']['trigger']);
        $this->assertSame('Enter', $heading['editor']['inline_editing']['shortcut']);
    }
}
