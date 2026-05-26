<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormFieldRegistryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedCore();
        $this->markApplicationAsInstalled();
    }

    public function test_form_field_registry_api_returns_registry_driven_builder_contract(): void
    {
        $editor = $this->makeUserWithRole('editor');

        $response = $this->actingAs($editor)->getJson('/admin/api/forms/field-registry');

        $response->assertOk();
        $response->assertJsonPath('registry_version', '1.0');
        $response->assertJsonStructure([
            'registry_version',
            'fields' => [
                '*' => [
                    'type',
                    'label',
                    'category',
                    'icon',
                    'props',
                    'editor' => [
                        'component',
                        'tabs',
                    ],
                    'default_field' => [
                        'type',
                        'name',
                        'label',
                    ],
                ],
            ],
            'categories',
            'count',
        ]);

        $text = collect($response->json('fields'))->firstWhere('type', 'text');

        $this->assertNotNull($text);
        $this->assertSame('basic', $text['category']);
        $this->assertSame('vc-form-field-text', $text['editor']['component']);
        $this->assertArrayHasKey('label', $text['props']);
        $this->assertSame('text', $text['default_field']['type']);

        $fields = collect($response->json('fields'))->pluck('type')->all();

        $this->assertContains('url', $fields);
        $this->assertContains('time', $fields);
        $this->assertContains('name', $fields);
        $this->assertContains('address', $fields);
        $this->assertContains('consent', $fields);
        $this->assertContains('rating', $fields);
    }
}
