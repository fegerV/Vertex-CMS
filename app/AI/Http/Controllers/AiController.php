<?php

namespace App\AI\Http\Controllers;

use App\AI\Services\AiDraftService;
use App\AI\Services\AiProviderRegistry;
use App\AI\Services\SiteWizardService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use App\System\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AiController extends Controller
{
    public function __construct(
        private readonly AiProviderRegistry $providers,
        private readonly AiDraftService $drafts,
        private readonly SiteWizardService $wizard,
        private readonly ActivityLogService $activityLog,
    ) {
    }

    public function providers(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasAnyPermission(['ai.view', 'ai.use', 'ai.manage_providers', 'ai.manage_keys']), 403);

        return ApiResponse::success([
            'enabled' => (bool) config_value('ai.enabled', false),
            'default_provider' => config_value('ai.default_provider', 'openai'),
            'default_model' => config_value('ai.default_model', ''),
            'items' => $this->providers->all(),
        ], [
            'draft_only' => true,
        ]);
    }

    public function chat(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('ai.use'), 403);

        if (! (bool) config_value('ai.enabled', false)) {
            return ApiResponse::error(
                'ai_disabled',
                'The AI module is disabled.',
                [],
                422,
                ['draft_only' => true]
            );
        }

        $payload = $request->validate([
            'provider' => ['nullable', 'string', 'max:50'],
            'action' => ['required', Rule::in(['text', 'faq', 'cta', 'seo', 'builder'])],
            'instruction' => ['nullable', 'string', 'max:5000'],
            'page' => ['nullable', 'array'],
            'page.title' => ['nullable', 'string', 'max:255'],
            'page.uri' => ['nullable', 'string', 'max:500'],
            'page.status' => ['nullable', 'string', 'max:50'],
            'seo' => ['nullable', 'array'],
            'seo.title' => ['nullable', 'string', 'max:255'],
            'seo.description' => ['nullable', 'string', 'max:500'],
        ]);

        $provider = $this->providers->find($payload['provider'] ?? null);

        if (! $provider || ! ($provider['configured'] ?? false)) {
            return ApiResponse::error(
                'ai_provider_not_configured',
                'The selected AI provider is not configured.',
                [],
                422,
                ['draft_only' => true]
            );
        }

        $draft = $this->drafts->generate(
            $payload['action'],
            (string) ($payload['instruction'] ?? ''),
            $payload
        );

        $storePrompts = (bool) config_value('ai.store_prompts', false);
        $storeResponses = (bool) config_value('ai.store_responses', false);

        $this->activityLog->record('ai.chat', 'ai_request', null, 'AI draft generated.', [
            'action' => $payload['action'],
            'provider' => $provider['id'],
            'draft_only' => true,
            'page_title' => Arr::get($payload, 'page.title'),
            'prompt_preview' => $storePrompts ? Str::limit((string) ($payload['instruction'] ?? ''), 500, '') : null,
            'response_preview' => $storeResponses ? Str::limit((string) ($draft['preview'] ?? ''), 1000, '') : null,
        ], $request);

        return ApiResponse::success([
            'provider' => [
                'id' => $provider['id'],
                'name' => $provider['name'],
                'default_model' => $provider['default_model'],
            ],
            'draft' => $draft,
        ], [
            'draft_only' => true,
            'action' => $payload['action'],
        ]);
    }

    /**
     * Site Wizard: Generate complete site structure
     */
    public function wizardGenerateStructure(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('ai.use'), 403);

        if (! (bool) config_value('ai.enabled', false)) {
            return ApiResponse::error(
                'ai_disabled',
                'The AI module is disabled.',
                [],
                422
            );
        }

        $payload = $request->validate([
            'provider' => ['nullable', 'string', 'max:50'],
            'description' => ['required', 'string', 'max:5000'],
            'niche' => ['nullable', 'string', 'max:255'],
            'target_audience' => ['nullable', 'string', 'max:500'],
            'tone' => ['nullable', 'string', 'max:100'],
            'language' => ['nullable', 'string', 'max:10'],
        ]);

        $result = $this->wizard->generateSiteStructure($payload);

        if (!$result['success']) {
            return ApiResponse::error(
                'ai_wizard_error',
                $result['error'] ?? 'Failed to generate structure',
                [],
                422
            );
        }

        $this->activityLog->record('ai.wizard.structure', 'ai_request', null, 'Site structure generated via wizard', [
            'provider' => $payload['provider'] ?? 'default',
            'description_preview' => Str::limit($payload['description'], 200),
        ], $request);

        return ApiResponse::success([
            'structure' => $result['structure'],
            'usage' => $result['usage'] ?? [],
        ]);
    }

    /**
     * Site Wizard: Generate semantic core
     */
    public function wizardGenerateSemanticCore(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('ai.use'), 403);

        $payload = $request->validate([
            'provider' => ['nullable', 'string', 'max:50'],
            'description' => ['required', 'string', 'max:5000'],
            'niche' => ['nullable', 'string', 'max:255'],
        ]);

        $result = $this->wizard->generateSemanticCore($payload);

        if (!$result['success']) {
            return ApiResponse::error(
                'ai_wizard_error',
                $result['error'] ?? 'Failed to generate semantic core',
                [],
                422
            );
        }

        return ApiResponse::success([
            'keywords' => $result['keywords'],
            'usage' => $result['usage'] ?? [],
        ]);
    }

    /**
     * Site Wizard: Generate article plan for section
     */
    public function wizardGenerateArticlePlan(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('ai.use'), 403);

        $payload = $request->validate([
            'provider' => ['nullable', 'string', 'max:50'],
            'section_title' => ['required', 'string', 'max:255'],
            'section_description' => ['nullable', 'string', 'max:2000'],
            'topic' => ['nullable', 'string', 'max:500'],
            'article_count' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $result = $this->wizard->generateArticlePlan($payload);

        if (!$result['success']) {
            return ApiResponse::error(
                'ai_wizard_error',
                $result['error'] ?? 'Failed to generate article plan',
                [],
                422
            );
        }

        return ApiResponse::success([
            'articles' => $result['articles'],
            'usage' => $result['usage'] ?? [],
        ]);
    }

    /**
     * Site Wizard: Generate full article content
     */
    public function wizardGenerateArticleContent(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('ai.use'), 403);

        $payload = $request->validate([
            'provider' => ['nullable', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:500'],
            'outline' => ['nullable', 'array'],
            'keywords' => ['nullable', 'array'],
            'tone' => ['nullable', 'string', 'max:100'],
            'word_count' => ['nullable', 'integer', 'min:500', 'max:5000'],
        ]);

        $result = $this->wizard->generateArticleContent($payload);

        if (!$result['success']) {
            return ApiResponse::error(
                'ai_wizard_error',
                $result['error'] ?? 'Failed to generate article content',
                [],
                422
            );
        }

        return ApiResponse::success([
            'content' => $result['content'],
            'usage' => $result['usage'] ?? [],
        ]);
    }

    /**
     * Site Wizard: Generate image prompt
     */
    public function wizardGenerateImagePrompt(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('ai.use'), 403);

        $payload = $request->validate([
            'provider' => ['nullable', 'string', 'max:50'],
            'subject' => ['required', 'string', 'max:1000'],
            'context' => ['nullable', 'string', 'max:2000'],
            'style' => ['nullable', 'string', 'max:255'],
        ]);

        $result = $this->wizard->generateImagePrompt($payload);

        if (!$result['success']) {
            return ApiResponse::error(
                'ai_wizard_error',
                $result['error'] ?? 'Failed to generate image prompt',
                [],
                422
            );
        }

        return ApiResponse::success([
            'image_prompt' => $result['image_prompt'],
            'usage' => $result['usage'] ?? [],
        ]);
    }

    /**
     * Site Wizard: Generate image
     */
    public function wizardGenerateImage(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('ai.use'), 403);

        $payload = $request->validate([
            'prompt' => ['required', 'string', 'max:5000'],
            'model' => ['nullable', 'string', 'max:100'],
            'size' => ['nullable', 'string', 'in:1024x1024,1024x1792,1792x1024'],
            'quality' => ['nullable', 'string', 'in:standard,hd'],
            'count' => ['nullable', 'integer', 'min:1', 'max:4'],
        ]);

        $result = $this->wizard->generateImage(
            $payload['prompt'],
            [
                'model' => $payload['model'] ?? null,
                'size' => $payload['size'] ?? null,
                'quality' => $payload['quality'] ?? null,
                'count' => $payload['count'] ?? 1,
            ]
        );

        if (!$result['success']) {
            return ApiResponse::error(
                'ai_image_generation_error',
                $result['error'] ?? 'Failed to generate image',
                [],
                422
            );
        }

        return ApiResponse::success([
            'images' => $result['images'],
            'usage' => $result['usage'] ?? [],
        ]);
    }

    /**
     * Site Wizard: Save generated structure to database
     */
    public function wizardSaveStructure(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('pages.create'), 403);

        $payload = $request->validate([
            'structure' => ['required', 'array'],
            'structure.site_name' => ['required', 'string', 'max:255'],
            'structure.pages' => ['required', 'array'],
            'structure.menu' => ['nullable', 'array'],
            'options' => ['nullable', 'array'],
            'options.menu_name' => ['nullable', 'string', 'max:255'],
        ]);

        $result = $this->wizard->saveSiteStructure(
            $payload['structure'],
            $payload['options'] ?? []
        );

        if (!$result['success']) {
            return ApiResponse::error(
                'wizard_save_error',
                $result['error'] ?? 'Failed to save structure',
                [],
                422
            );
        }

        $this->activityLog->record('ai.wizard.save', 'site_created', null, 'Site structure saved via wizard', [
            'site_name' => $payload['structure']['site_name'],
            'pages_count' => count($payload['structure']['pages'] ?? []),
        ], $request);

        return ApiResponse::success([
            'results' => $result['results'],
            'message' => 'Site structure successfully created',
        ]);
    }
}
