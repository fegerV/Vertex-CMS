<?php

namespace Vertex\Forms\Controllers;

use App\Http\Controllers\Controller;
use Vertex\Forms\Models\Form;
use Vertex\Forms\Services\FormService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FormPublicController extends Controller
{
    public function __construct(
        private readonly FormService $formService,
    ) {
    }

    /**
     * Submit form (AJAX endpoint)
     */
    public function submit(Request $request, Form $form): JsonResponse
    {
        try {
            $submission = $this->formService->submit($form, $request);

            return response()->json([
                'success' => true,
                'message' => $form->settings['success_message'] ?? 'Форма успешно отправлена!',
                'submission_id' => $submission->submission_id,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $form->settings['error_message'] ?? 'Пожалуйста, исправьте ошибки в форме.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get form configuration for SPA/JS rendering
     */
    public function config(Form $form): JsonResponse
    {
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
}
