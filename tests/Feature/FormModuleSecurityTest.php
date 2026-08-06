<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;
use Vertex\Forms\Controllers\FormSubmissionController;
use Vertex\Forms\Models\Form;
use Vertex\Forms\Models\FormSubmission;
use Vertex\Forms\Services\FormService;

class FormModuleSecurityTest extends TestCase
{
    public function test_honeypot_rejects_a_filled_submission(): void
    {
        $form = new Form([
            'slug' => 'contact',
            'settings' => ['honeypot_enabled' => true],
        ]);
        $form->setRelation('fields', collect());
        $honeypot = 'form_'.md5('contact_hp');
        $request = Request::create('/forms/contact/submit', 'POST', [
            $honeypot => 'https://spam.example',
        ]);

        $validator = app(FormService::class)->validate($form, $request);

        $this->expectException(ValidationException::class);
        $validator->validate();
    }

    public function test_submission_from_another_form_cannot_be_read(): void
    {
        $form = new Form();
        $form->id = 10;
        $submission = new FormSubmission();
        $submission->form_id = 11;

        $this->expectException(NotFoundHttpException::class);

        app(FormSubmissionController::class)->show($form, $submission);
    }
}
