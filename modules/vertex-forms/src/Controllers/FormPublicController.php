<?php

namespace Vertex\Forms\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Vertex\Forms\Models\Form;
use Vertex\Forms\Services\FormService;

class FormPublicController extends Controller
{
    public function __construct(
        private readonly FormService $formService,
    ) {}

    /**
     * Render public form page.
     */
    public function show(Form $form): View
    {
        $this->assertFormAvailable($form, false);

        return view('forms::public.show', [
            'form' => $form->load('fields'),
            'formConfig' => $this->formService->renderForm($form),
            'actionUrl' => route('public.forms.submit', $form),
            'settings' => $form->settings ?? [],
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

            return response()->json([
                'success' => true,
                'message' => $form->settings['success_message'] ?? __('forms.submit_success'),
                'submission_id' => $submission->submission_id,
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
                'settings' => $form->settings,
                'config' => $config,
            ],
        ]);
    }

    private function assertFormAvailable(Form $form, bool $submitting): void
    {
        abort_unless($form->is_active, 404);

        $requiresLogin = $form->require_login
            || config($submitting ? 'forms.require_login_for_submit' : 'forms.require_login_for_view', false);

        abort_if($requiresLogin && auth()->guest(), 403, __('forms.error_login_required'));
    }
}
