<?php

namespace Tests\Feature;

use App\System\Services\EmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;
use Vertex\Forms\Contracts\FormRepositoryInterface;
use Vertex\Forms\Models\Form;
use Vertex\Forms\Repositories\EloquentFormRepository;
use Vertex\Forms\Services\FormCalculatorEngine;
use Vertex\Forms\Services\FormConditionEngine;
use Vertex\Forms\Services\FormService;

class FormProductionHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_repository_contract_resolves_to_eloquent_implementation(): void
    {
        $this->assertInstanceOf(EloquentFormRepository::class, app(FormRepositoryInterface::class));
    }

    public function test_honeypot_is_rejected_and_uploads_are_stored_privately(): void
    {
        Storage::fake('local');
        config()->set('forms.upload_disk', 'local');

        $form = Form::query()->create([
            'name' => 'Secure form',
            'slug' => 'secure-form',
            'is_active' => true,
            'settings' => ['honeypot_enabled' => true],
        ]);
        $form->fields()->create([
            'name' => 'document',
            'label' => 'Document',
            'type' => 'file',
            'required' => true,
            'options' => ['mime_types' => ['text/plain'], 'max_size' => 64],
        ]);
        $form->load('fields');

        $service = $this->formService();
        $honeypot = 'form_'.md5($form->slug.'_hp');
        $spamRequest = Request::create('/forms/secure-form/submit', 'POST', [$honeypot => 'bot']);

        $spamValidator = $service->validate($form, $spamRequest);
        $this->assertTrue($spamValidator->fails());
        $this->assertArrayHasKey($honeypot, $spamValidator->errors()->toArray());

        $request = Request::create('/forms/secure-form/submit', 'POST');
        $request->files->set('document', UploadedFile::fake()->create('proof.txt', 2, 'text/plain'));
        $submission = $service->submit($form, $request);
        $stored = $submission->values->first()->value;

        $this->assertSame('local', $stored['disk']);
        Storage::disk('local')->assertExists($stored['path']);
        $this->assertDatabaseCount('form_submissions', 1);
        $this->assertDatabaseCount('form_submission_values', 1);
    }

    public function test_inactive_forms_are_not_publicly_available(): void
    {
        $form = Form::query()->create([
            'name' => 'Draft form',
            'slug' => 'draft-form',
            'is_active' => false,
        ]);

        $this->get('/forms/'.$form->slug)->assertNotFound();
        $this->postJson('/forms/'.$form->slug.'/submit')->assertNotFound();
    }

    private function formService(): FormService
    {
        return new FormService(
            Mockery::mock(EmailService::class),
            app('validator'),
            new FormCalculatorEngine,
            new FormConditionEngine,
        );
    }
}
