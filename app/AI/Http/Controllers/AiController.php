<?php

namespace App\AI\Http\Controllers;

use App\AI\Services\AiDraftService;
use App\AI\Services\AiProviderRegistry;
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
}
