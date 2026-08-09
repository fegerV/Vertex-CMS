<?php

namespace Vertex\Forms\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Vertex\Forms\Models\Form;
use Vertex\Forms\Services\FormAnalyticsService;
use Vertex\Forms\Services\FormDraftService;
use Vertex\Forms\Services\FormResultsService;
use Vertex\Forms\Services\FormService;

class FormPublicController extends Controller
{
    public function __construct(
        private readonly FormService $formService,
        private readonly FormAnalyticsService $analyticsService,
        private readonly FormDraftService $draftService,
        private readonly FormResultsService $resultsService,
    ) {}

    /**
     * Render public form page.
     */
    public function show(Form $form): View
    {
        $this->assertFormAvailable($form, false);
        if (config('forms.log_form_views', true)) {
            $this->analyticsService->recordView($form, (string) request()->ip(), request()->userAgent());
        }

        return view('forms::public.show', [
            'form' => $form->load('fields'),
            'formConfig' => $this->formService->renderForm($form),
            'actionUrl' => route('public.forms.submit', $form),
            'settings' => $this->publicSettings($form),
        ]);
    }

    /**
     * Submit form (AJAX endpoint)
     */
    public function submit(Request $request, Form $form): JsonResponse
    {
        $this->assertFormAvailable($form, true);

        try {
            $submission = $this->formService->submit($form, $request);
            $this->draftService->consume($form, $request->input('resume_token'));

            return response()->json([
                'success' => true,
                'message' => $form->settings['success_message'] ?? __('forms.submit_success'),
                'submission_id' => $submission->submission_id,
                'result' => $submission->meta['outcome'] ?? null,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $form->settings['error_message'] ?? __('forms.validation_fix_errors'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 400;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $status, $e instanceof HttpExceptionInterface ? $e->getHeaders() : []);
        }
    }

    /**
     * Get form configuration for SPA/JS rendering
     */
    public function config(Form $form): JsonResponse
    {
        $this->assertFormAvailable($form, false);

        $config = $this->formService->renderForm($form);

        return response()->json([
            'form' => [
                'id' => $form->id,
                'name' => $form->name,
                'description' => $form->description,
                'settings' => $this->publicSettings($form),
                'config' => $config,
            ],
        ]);
    }

    public function saveDraft(Request $request, Form $form): JsonResponse
    {
        $this->assertFormAvailable($form, true);

        return response()->json(['draft' => $this->draftService->save($form, $request)]);
    }

    public function loadDraft(Form $form, string $token): JsonResponse
    {
        $this->assertFormAvailable($form, false);

        return response()->json(['draft' => $this->draftService->load($form, $token)]);
    }

    public function results(Form $form): JsonResponse
    {
        $this->assertFormAvailable($form, false);

        return response()->json(['results' => $this->resultsService->poll($form)]);
    }

    private function assertFormAvailable(Form $form, bool $submitting): void
    {
        abort_unless($form->is_active, 404);

        $requiresLogin = $form->require_login
            || config($submitting ? 'forms.require_login_for_submit' : 'forms.require_login_for_view', false);

        abort_if($requiresLogin && auth()->guest(), 403, __('forms.error_login_required'));
    }

    private function publicSettings(Form $form): array
    {
        return Arr::only($form->settings ?? [], [
            'title', 'description', 'submit_label', 'success_message', 'error_message',
            'theme', 'layout_density', 'button_style', 'show_progress', 'show_page_titles',
            'redirect_url', 'custom_css',
            'save_resume_enabled', 'resume_days',
        ]);
    }
}
