<?php

namespace Tests\Feature;

use App\Models\EmailLog;
use App\System\Services\EmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;
use Vertex\Forms\Contracts\FormRepositoryInterface;
use Vertex\Forms\Controllers\FormSubmissionFileController;
use Vertex\Forms\Jobs\DeliverFormWebhook;
use Vertex\Forms\Models\Form;
use Vertex\Forms\Models\FormSubmissionValue;
use Vertex\Forms\Models\FormWebhookDelivery;
use Vertex\Forms\Repositories\EloquentFormRepository;
use Vertex\Forms\Services\FormCalculatorEngine;
use Vertex\Forms\Services\FormConditionEngine;
use Vertex\Forms\Services\FormImportExportService;
use Vertex\Forms\Services\FormIntegrationService;
use Vertex\Forms\Services\FormService;
use Vertex\Forms\Services\FormSpamProtectionService;

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

    public function test_public_form_posts_to_submit_endpoint_and_contains_idempotency_field(): void
    {
        $form = Form::query()->create([
            'name' => 'Public form',
            'slug' => 'public-form',
            'is_active' => true,
            'settings' => ['honeypot_enabled' => false],
        ]);

        $this->get('/forms/'.$form->slug)
            ->assertOk()
            ->assertSee('action="'.url('/forms/public-form/submit').'"', false)
            ->assertSee('name="idempotency_key"', false);
    }

    public function test_hidden_conditional_values_are_not_persisted(): void
    {
        $form = Form::query()->create([
            'name' => 'Conditional form',
            'slug' => 'conditional-form',
            'is_active' => true,
            'settings' => ['honeypot_enabled' => false],
        ]);
        $form->fields()->createMany([
            ['name' => 'kind', 'label' => 'Kind', 'type' => 'text', 'sort_order' => 0],
            [
                'name' => 'secret',
                'label' => 'Secret',
                'type' => 'text',
                'sort_order' => 1,
                'options' => [
                    'conditional' => ['depends_on' => 'kind', 'operator' => 'equals', 'value' => 'allowed'],
                ],
            ],
        ]);
        $form->load('fields');

        $request = Request::create('/forms/conditional-form/submit', 'POST', [
            'kind' => 'denied',
            'secret' => 'must-not-be-stored',
        ]);
        $submission = $this->formService()->submit($form, $request);

        $this->assertCount(1, $submission->values);
        $this->assertSame('kind', $submission->values->first()->field->name);
    }

    public function test_multiple_file_fields_validate_and_store_each_upload(): void
    {
        Storage::fake('local');
        config()->set('forms.upload_disk', 'local');

        $form = Form::query()->create([
            'name' => 'Multiple uploads',
            'slug' => 'multiple-uploads',
            'is_active' => true,
            'settings' => ['honeypot_enabled' => false],
        ]);
        $form->fields()->create([
            'name' => 'documents',
            'label' => 'Documents',
            'type' => 'file',
            'required' => true,
            'options' => ['multiple' => true, 'mime_types' => ['text/plain'], 'max_size' => 64],
        ]);
        $form->load('fields');

        $request = Request::create('/forms/multiple-uploads/submit', 'POST');
        $request->files->set('documents', [
            UploadedFile::fake()->create('one.txt', 2, 'text/plain'),
            UploadedFile::fake()->create('two.txt', 2, 'text/plain'),
        ]);
        $submission = $this->formService()->submit($form, $request);
        $storedFiles = $submission->values->first()->value;

        $this->assertCount(2, $storedFiles);
        foreach ($storedFiles as $storedFile) {
            Storage::disk('local')->assertExists($storedFile['path']);
        }
    }

    public function test_recaptcha_v3_is_verified_with_score_and_action(): void
    {
        Http::fake([
            'www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.9,
                'action' => 'form_submit',
            ]),
        ]);
        config()->set('forms.recaptcha_secret_key', 'secret');

        $form = Form::query()->create([
            'name' => 'Captcha form',
            'slug' => 'captcha-form',
            'is_active' => true,
            'settings' => [
                'honeypot_enabled' => false,
                'recaptcha_enabled' => true,
                'recaptcha_version' => 'v3',
                'recaptcha_min_score' => 0.7,
            ],
        ]);
        $request = Request::create('/forms/captcha-form/submit', 'POST', ['recaptcha_token' => 'valid-token']);

        $this->formService()->submit($form, $request);

        Http::assertSent(fn ($request) => $request['secret'] === 'secret'
            && $request['response'] === 'valid-token');
        $this->assertDatabaseCount('form_submissions', 1);
    }

    public function test_idempotency_key_returns_the_original_submission(): void
    {
        $form = Form::query()->create([
            'name' => 'Idempotent form',
            'slug' => 'idempotent-form',
            'is_active' => true,
            'settings' => ['honeypot_enabled' => false],
        ]);
        $request = Request::create('/forms/idempotent-form/submit', 'POST');
        $request->headers->set('Idempotency-Key', 'checkout-123');
        $service = $this->formService();

        $first = $service->submit($form, $request);
        $second = $service->submit($form, $request);

        $this->assertTrue($first->is($second));
        $this->assertDatabaseCount('form_submissions', 1);
    }

    public function test_turnstile_rejects_an_unsuccessful_verification(): void
    {
        Http::fake([
            'challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => false]),
        ]);
        config()->set('forms.turnstile_secret_key', 'secret');

        $form = Form::query()->create([
            'name' => 'Turnstile form',
            'slug' => 'turnstile-form',
            'is_active' => true,
            'settings' => ['honeypot_enabled' => false, 'turnstile_enabled' => true],
        ]);
        $request = Request::create('/forms/turnstile-form/submit', 'POST', [
            'cf-turnstile-response' => 'invalid-token',
        ]);

        $this->expectException(ValidationException::class);
        $this->formService()->submit($form, $request);
    }

    public function test_per_form_rate_limit_returns_http_429(): void
    {
        $form = Form::query()->create([
            'name' => 'Limited form',
            'slug' => 'limited-form',
            'is_active' => true,
            'settings' => ['honeypot_enabled' => false, 'max_submissions_per_minute' => 1],
        ]);
        $request = Request::create('/forms/limited-form/submit', 'POST', server: ['REMOTE_ADDR' => '192.0.2.10']);
        $key = 'forms:submit:'.$form->id.':'.hash('sha256', '192.0.2.10');
        RateLimiter::clear($key);
        $service = $this->formService();
        $service->submit($form, $request);

        try {
            $service->submit($form, $request);
            $this->fail('Expected the second submission to be rate limited.');
        } catch (HttpException $exception) {
            $this->assertSame(429, $exception->getStatusCode());
            $this->assertArrayHasKey('Retry-After', $exception->getHeaders());
        }
    }

    public function test_submission_file_download_requires_matching_models_and_private_disk(): void
    {
        Storage::fake('local');
        config()->set('forms.upload_disk', 'local');
        Storage::disk('local')->put('form-uploads/download/file.txt', 'private-content');

        $form = Form::query()->create([
            'name' => 'Download form',
            'slug' => 'download-form',
            'is_active' => true,
        ]);
        $field = $form->fields()->create(['name' => 'file', 'label' => 'File', 'type' => 'file']);
        $submission = $form->submissions()->create(['submission_id' => fake()->uuid()]);
        $value = FormSubmissionValue::query()->create([
            'submission_id' => $submission->id,
            'field_id' => $field->id,
            'value' => [
                'disk' => 'local',
                'path' => 'form-uploads/download/file.txt',
                'name' => 'file.txt',
                'mime' => 'text/plain',
            ],
        ]);

        $response = app(FormSubmissionFileController::class)->download($form, $submission, $value);

        $this->assertSame('attachment; filename=file.txt', $response->headers->get('content-disposition'));
        $this->assertSame('text/plain', $response->headers->get('content-type'));
    }

    public function test_webhook_delivery_is_signed_queued_and_logged(): void
    {
        Http::fake(['hooks.example.test/forms' => Http::response(['accepted' => true], 202)]);
        Queue::fake();

        $form = Form::query()->create([
            'name' => 'Integrated form',
            'slug' => 'integrated-form',
            'is_active' => true,
            'settings' => [
                'honeypot_enabled' => false,
                'webhooks' => [[
                    'name' => 'CRM',
                    'url' => 'https://hooks.example.test/forms',
                    'secret' => 'integration-secret',
                    'enabled' => true,
                ]],
            ],
        ]);

        $this->formService()->submit($form, Request::create('/forms/integrated-form/submit', 'POST'));

        $this->assertStringNotContainsString('integration-secret', (string) $form->getRawOriginal('settings'));
        $this->assertSame('', (new FormImportExportService)->export($form)['form']['settings']['webhooks'][0]['secret']);
        Queue::assertPushed(DeliverFormWebhook::class);
        $delivery = FormWebhookDelivery::query()->firstOrFail();
        (new DeliverFormWebhook($delivery))->handle();

        Http::assertSent(function ($request): bool {
            $timestamp = $request->header('X-Vertex-Timestamp')[0] ?? '';
            $signature = $request->header('X-Vertex-Signature')[0] ?? '';

            return $request->url() === 'https://hooks.example.test/forms'
                && hash_equals(hash_hmac('sha256', $request->body(), 'integration-secret'), $signature)
                && $timestamp !== '';
        });
        $this->assertDatabaseHas('form_webhook_deliveries', ['status' => 'delivered', 'attempts' => 1]);
    }

    public function test_public_config_does_not_expose_integration_or_notification_secrets(): void
    {
        $form = Form::query()->create([
            'name' => 'Secret form',
            'slug' => 'secret-form',
            'is_active' => true,
            'settings' => [
                'submit_label' => 'Send safely',
                'notify_admin_emails' => 'private@example.test',
                'webhooks' => [['url' => 'https://example.test', 'secret' => 'do-not-expose']],
            ],
        ]);

        $this->getJson('/forms/'.$form->slug.'/config')
            ->assertOk()
            ->assertJsonPath('form.settings.submit_label', 'Send safely')
            ->assertJsonMissingPath('form.settings.notify_admin_emails')
            ->assertJsonMissingPath('form.settings.webhooks');
    }

    public function test_builder_email_settings_drive_admin_and_autoresponder_notifications(): void
    {
        $form = Form::query()->create([
            'name' => 'Email form',
            'slug' => 'email-form',
            'is_active' => true,
            'settings' => [
                'honeypot_enabled' => false,
                'notify_admin_emails' => 'first@example.test; second@example.test',
                'autoresponder_enabled' => true,
                'autoresponder_body' => 'Thanks for contacting us.',
            ],
        ]);
        $form->fields()->create(['name' => 'contact', 'label' => 'Contact', 'type' => 'email']);
        $form->load('fields');
        $emailService = Mockery::mock(EmailService::class);
        $emailService->shouldReceive('send')->times(3)->andReturn(new EmailLog);

        $this->formService($emailService)->submit(
            $form,
            Request::create('/forms/email-form/submit', 'POST', ['contact' => 'visitor@example.test'])
        );

        $this->assertDatabaseCount('form_submissions', 1);
    }

    private function formService(?EmailService $emailService = null): FormService
    {
        return new FormService(
            $emailService ?? Mockery::mock(EmailService::class),
            app('validator'),
            new FormCalculatorEngine,
            new FormConditionEngine,
            new FormSpamProtectionService,
            new FormIntegrationService,
        );
    }
}
