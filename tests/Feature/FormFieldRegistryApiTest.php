<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Vertex\Forms\Models\Form;

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

    public function test_builder_api_rejects_unknown_and_duplicate_field_names_atomically(): void
    {
        $editor = $this->makeUserWithRole('editor');

        $response = $this->actingAs($editor)->postJson('/admin/api/forms', [
            'name' => 'Invalid form',
            'fields' => [
                ['type' => 'text', 'name' => 'duplicate', 'label' => 'First'],
                ['type' => 'missing-widget', 'name' => 'duplicate', 'label' => 'Second'],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_error')
            ->assertJsonStructure([
                'error' => ['details' => ['fields.1.type', 'fields.1.name']],
            ]);
        $this->assertDatabaseMissing('forms', ['name' => 'Invalid form']);
    }

    public function test_builder_api_persists_registry_defaults_in_field_options(): void
    {
        $editor = $this->makeUserWithRole('editor');

        $response = $this->actingAs($editor)->postJson('/admin/api/forms', [
            'name' => 'Defaults form',
            'fields' => [
                ['type' => 'number', 'name' => 'quantity', 'label' => 'Quantity'],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('form.fields.0.options.step', 1);
    }

    public function test_legacy_duplicate_action_attaches_copied_fields_to_new_form(): void
    {
        $editor = $this->makeUserWithRole('editor');
        $form = Form::query()->create([
            'name' => 'Original',
            'slug' => 'original',
            'type' => 'standard',
            'created_by' => $editor->id,
        ]);
        $form->fields()->create([
            'type' => 'text',
            'name' => 'customer_name',
            'label' => 'Customer name',
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($editor)->post('/admin/forms/'.$form->id.'/duplicate');

        $copy = Form::query()->whereKeyNot($form->id)->firstOrFail();
        $response->assertRedirect(route('admin.forms.edit', $copy));
        $this->assertCount(1, $copy->fields);
        $this->assertSame('customer_name', $copy->fields->first()->name);
    }
}
