<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AI\ChatBotService;
use App\Services\AI\ContentGenerationService;
use App\Services\AI\ImageAnalysisService;
use App\Services\AI\SmartSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AIController extends Controller
{
    public function __construct(
        private ChatBotService $chatBotService,
        private ContentGenerationService $contentGenerationService,
        private ImageAnalysisService $imageAnalysisService,
        private SmartSearchService $smartSearchService
    ) {}

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'context' => 'nullable|array',
        ]);

        $response = $this->chatBotService->handle_message(
            $validated['message'],
            $validated['context'] ?? []
        );

        return response()->json($response);
    }

    public function faq(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => 'required|string|max:1000',
        ]);

        $response = $this->chatBotService->answerFAQ($validated['question']);

        return response()->json($response);
    }

    public function generateContent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:product_description,meta_tags,blog_post,summarize',
            'data' => 'required|array',
        ]);

        $response = match ($validated['type']) {
            'product_description' => $this->contentGenerationService->generateProductDescription($validated['data']),
            'meta_tags' => $this->contentGenerationService->generateMetaTags($validated['data']),
            'blog_post' => $this->contentGenerationService->generateBlogPost($validated['data']),
            'summarize' => $this->contentGenerationService->summarizeText(
                $validated['data']['text'] ?? '',
                $validated['data']['max_length'] ?? 200
            ),
            default => ['success' => false, 'error' => 'Unknown type'],
        };

        return response()->json($response);
    }

    public function analyzeImage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image_path' => 'required|string',
        ]);

        $response = $this->imageAnalysisService->generateAITags($validated['image_path']);

        return response()->json($response);
    }

    public function moderateContent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => 'required|string|max:10000',
        ]);

        $response = $this->imageAnalysisService->moderateContent($validated['text']);

        return response()->json($response);
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => 'required|string|max:500',
            'filters' => 'nullable|array',
            'model' => ['nullable', 'string', Rule::in(SmartSearchService::SEARCHABLE_MODELS)],
        ]);

        if ($validated['model'] ?? null) {
            $response = $this->smartSearchService->searchWithFilters(
                $validated['query'],
                $validated['filters'] ?? [],
                $validated['model']
            );
        } else {
            $response = $this->smartSearchService->search($validated['query'], [
                'filters' => $validated['filters'] ?? [],
            ]);
        }

        return response()->json(['results' => $response]);
    }

    public function suggestQueries(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'partial_query' => 'required|string|max:100',
        ]);

        $suggestions = $this->smartSearchService->suggestQueries($validated['partial_query']);

        return response()->json(['suggestions' => $suggestions]);
    }

    public function extractKeywords(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => 'required|string|max:10000',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $response = $this->imageAnalysisService->extractKeywords(
            $validated['text'],
            $validated['limit'] ?? 10
        );

        return response()->json($response);
    }

    public function detectLanguage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => 'required|string|max:2000',
        ]);

        $response = $this->imageAnalysisService->detectLanguage($validated['text']);

        return response()->json($response);
    }

    public function recommendProducts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'categories' => 'nullable|array',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        $preferences = [
            'categories' => $validated['categories'] ?? [],
            'min_price' => $validated['min_price'] ?? 0,
            'max_price' => $validated['max_price'] ?? 100000,
        ];

        $response = $this->chatBotService->recommendProducts(
            $preferences,
            $validated['limit'] ?? 5
        );

        return response()->json($response);
    }
}
