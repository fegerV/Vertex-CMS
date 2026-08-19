<?php

namespace App\AI\Services;

use App\Core\Services\SettingsService;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SiteWizardService
{
    public function __construct(
        private readonly AiProviderRegistry $providers,
        private readonly SettingsService $settings,
    ) {}

    /**
     * Generate complete site structure from description
     */
    public function generateSiteStructure(array $data): array
    {
        $provider = $this->providers->find($data['provider'] ?? null);

        if (! $provider || ! ($provider['configured'] ?? false)) {
            return [
                'success' => false,
                'error' => 'AI provider not configured',
            ];
        }

        $prompt = $this->buildSiteStructurePrompt($data);

        $response = $this->callAiApi($provider, $prompt, [
            'max_tokens' => 4000,
            'temperature' => 0.7,
            'response_format' => ['type' => 'json_object'],
        ]);

        if (! $response['success']) {
            return $response;
        }

        $structure = json_decode($response['content'], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'error' => 'Failed to parse AI response as JSON',
                'raw_content' => $response['content'],
            ];
        }

        return [
            'success' => true,
            'structure' => $this->normalizeStructure($structure),
            'usage' => $response['usage'] ?? [],
        ];
    }

    /**
     * Generate semantic core (keywords) for the site
     */
    public function generateSemanticCore(array $siteData): array
    {
        $provider = $this->providers->find($siteData['provider'] ?? null);

        if (! $provider || ! ($provider['configured'] ?? false)) {
            return ['success' => false, 'error' => 'AI provider not configured'];
        }

        $prompt = $this->buildSemanticCorePrompt($siteData);

        $response = $this->callAiApi($provider, $prompt, [
            'max_tokens' => 3000,
            'temperature' => 0.6,
            'response_format' => ['type' => 'json_object'],
        ]);

        if (! $response['success']) {
            return $response;
        }

        $keywords = json_decode($response['content'], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'error' => 'Failed to parse keywords JSON',
            ];
        }

        return [
            'success' => true,
            'keywords' => $keywords,
            'usage' => $response['usage'] ?? [],
        ];
    }

    /**
     * Generate article titles and outlines for a section
     */
    public function generateArticlePlan(array $sectionData): array
    {
        $provider = $this->providers->find($sectionData['provider'] ?? null);

        if (! $provider || ! ($provider['configured'] ?? false)) {
            return ['success' => false, 'error' => 'AI provider not configured'];
        }

        $prompt = $this->buildArticlePlanPrompt($sectionData);

        $response = $this->callAiApi($provider, $prompt, [
            'max_tokens' => 2500,
            'temperature' => 0.7,
            'response_format' => ['type' => 'json_object'],
        ]);

        if (! $response['success']) {
            return $response;
        }

        $articles = json_decode($response['content'], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'error' => 'Failed to parse articles JSON',
            ];
        }

        return [
            'success' => true,
            'articles' => $articles['articles'] ?? [],
            'usage' => $response['usage'] ?? [],
        ];
    }

    /**
     * Generate full article content
     */
    public function generateArticleContent(array $articleData): array
    {
        $provider = $this->providers->find($articleData['provider'] ?? null);

        if (! $provider || ! ($provider['configured'] ?? false)) {
            return ['success' => false, 'error' => 'AI provider not configured'];
        }

        $prompt = $this->buildArticleContentPrompt($articleData);

        $response = $this->callAiApi($provider, $prompt, [
            'max_tokens' => 5000,
            'temperature' => 0.7,
        ]);

        if (! $response['success']) {
            return $response;
        }

        return [
            'success' => true,
            'content' => $response['content'],
            'usage' => $response['usage'] ?? [],
        ];
    }

    /**
     * Generate image prompt for article/section
     */
    public function generateImagePrompt(array $contextData): array
    {
        $provider = $this->providers->find($contextData['provider'] ?? null);

        if (! $provider || ! ($provider['configured'] ?? false)) {
            return ['success' => false, 'error' => 'AI provider not configured'];
        }

        $prompt = $this->buildImagePromptPrompt($contextData);

        $response = $this->callAiApi($provider, $prompt, [
            'max_tokens' => 500,
            'temperature' => 0.8,
        ]);

        if (! $response['success']) {
            return $response;
        }

        return [
            'success' => true,
            'image_prompt' => trim($response['content']),
            'usage' => $response['usage'] ?? [],
        ];
    }

    /**
     * Generate image using DALL-E or similar
     */
    public function generateImage(string $prompt, array $options = []): array
    {
        $apiKey = $this->settings->get('ai.openai_api_key');

        if (! $apiKey) {
            return ['success' => false, 'error' => 'OpenAI API key not configured'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/images/generations', [
                'model' => $options['model'] ?? 'dall-e-3',
                'prompt' => $prompt,
                'n' => $options['count'] ?? 1,
                'size' => $options['size'] ?? '1024x1024',
                'quality' => $options['quality'] ?? 'standard',
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'images' => collect($data['data'] ?? [])
                        ->pluck('url')
                        ->toArray(),
                    'usage' => $data['usage'] ?? [],
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Save generated site structure to database
     */
    public function saveSiteStructure(array $structure, array $options = []): array
    {
        DB::beginTransaction();

        try {
            $results = [
                'pages' => [],
                'menu_items' => [],
                'categories' => [],
            ];

            // Create pages
            $pagesByUri = [];
            foreach ($structure['pages'] ?? [] as $pageData) {
                $slug = Str::slug($pageData['slug'] ?? $pageData['title']);
                $uri = '/'.ltrim((string) ($pageData['uri'] ?? $slug), '/');
                $page = Page::query()->create([
                    'title' => $pageData['title'],
                    'slug' => $slug,
                    'uri' => $uri,
                    'status' => 'draft',
                    'content_json' => $this->normalizePageContent($pageData),
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);

                $page->seoMeta()->create([
                    'title' => $pageData['meta_title'] ?? $pageData['title'],
                    'description' => $pageData['meta_description'] ?? '',
                ]);
                $pagesByUri[$uri] = $page;
                $results['pages'][] = $page->id;
            }

            $menu = Menu::query()->create([
                'name' => $options['menu_name'] ?? 'Main Menu',
                'slug' => $this->uniqueMenuSlug($options['menu_slug'] ?? 'main-menu'),
                'location' => $options['menu_location'] ?? 'primary',
            ]);

            foreach ($structure['menu'] ?? [] as $index => $menuItem) {
                $url = (string) ($menuItem['url'] ?? '#');
                $page = $pagesByUri[$url] ?? null;
                $item = MenuItem::query()->create([
                    'menu_id' => $menu->id,
                    'title' => $menuItem['title'],
                    'url' => $url,
                    'sort_order' => $index,
                    'parent_id' => null,
                    'type' => $page ? 'page' : 'custom',
                    'entity_type' => $page ? Page::class : null,
                    'entity_id' => $page?->id,
                ]);
                $results['menu_items'][] = $item->id;
            }

            DB::commit();

            return [
                'success' => true,
                'results' => $results,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Site Wizard: Failed to save structure', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build prompt for site structure generation
     */
    private function buildSiteStructurePrompt(array $data): string
    {
        $description = $data['description'] ?? '';
        $niche = $data['niche'] ?? 'general';
        $targetAudience = $data['target_audience'] ?? 'general audience';
        $tone = $data['tone'] ?? 'professional';
        $language = $data['language'] ?? 'ru';

        return <<<PROMPT
You are an expert website architect and content strategist. Create a complete website structure based on the following requirements:

**Website Description:** {$description}

**Niche/Industry:** {$niche}
**Target Audience:** {$targetAudience}
**Tone of Voice:** {$tone}
**Language:** {$language}

Generate a JSON structure with:
1. "site_name": Catchy, relevant name for the website
2. "tagline": Short, memorable tagline
3. "pages": Array of pages, each with:
   - "title": Page title
   - "uri": URL path (e.g., "/about", "/services/consulting")
   - "meta_title": SEO title (max 60 chars)
   - "meta_description": SEO description (max 160 chars)
   - "content": Main page content (HTML or markdown, 200-500 words)
   - "blocks": Array of content blocks for the page builder
   - "keywords": Array of target keywords for this page
4. "menu": Array of main navigation items with:
   - "title": Menu item text
   - "url": Link URL
   - "children": (optional) Array of submenu items

Return ONLY valid JSON. No explanations, no markdown formatting.
PROMPT;
    }

    /**
     * Build prompt for semantic core generation
     */
    private function buildSemanticCorePrompt(array $data): string
    {
        $description = $data['description'] ?? '';
        $niche = $data['niche'] ?? 'general';

        return <<<PROMPT
You are an SEO expert. Generate a comprehensive semantic core (keyword list) for a website about: {$description}

Industry/Niche: {$niche}

Create a JSON structure with:
1. "core_keywords": 10-15 most important high-frequency keywords
2. "long_tail_keywords": 20-30 specific long-tail keywords
3. "question_keywords": 10-15 keywords in question format (what, how, why, etc.)
4. "commercial_keywords": 10-15 keywords with commercial intent (buy, order, price, etc.)
5. "informational_keywords": 15-20 keywords for blog/content marketing

Group keywords by intent and frequency. Return ONLY valid JSON.
PROMPT;
    }

    /**
     * Build prompt for article plan generation
     */
    private function buildArticlePlanPrompt(array $data): string
    {
        $sectionTitle = $data['section_title'] ?? 'Blog';
        $sectionDescription = $data['section_description'] ?? '';
        $topic = $data['topic'] ?? '';
        $count = $data['article_count'] ?? 5;

        return <<<PROMPT
You are a content strategist. Create a content plan for the section "{$sectionTitle}".

Section description: {$sectionDescription}
Main topic focus: {$topic}
Number of articles needed: {$count}

Generate a JSON structure with:
"articles": Array of article plans, each containing:
- "title": Compelling article title
- "slug": URL-friendly slug
- "meta_title": SEO title (max 60 chars)
- "meta_description": SEO description (max 160 chars)
- "outline": Array of H2/H3 headings for the article structure
- "target_keywords": 3-5 primary keywords
- "estimated_word_count": Recommended length (800-3000)
- "difficulty": "easy", "medium", or "hard"
- "priority": "high", "medium", or "low"

Return ONLY valid JSON.
PROMPT;
    }

    /**
     * Build prompt for article content generation
     */
    private function buildArticleContentPrompt(array $data): string
    {
        $title = $data['title'] ?? '';
        $outline = is_array($data['outline']) ? implode("\n", $data['outline']) : ($data['outline'] ?? '');
        $keywords = is_array($data['keywords']) ? implode(', ', $data['keywords']) : ($data['keywords'] ?? '');
        $tone = $data['tone'] ?? 'professional';
        $wordCount = $data['word_count'] ?? 1500;

        return <<<PROMPT
Write a comprehensive, well-structured article.

**Title:** {$title}

**Outline:**
{$outline}

**Target Keywords:** {$keywords}
**Tone:** {$tone}
**Target Word Count:** ~{$wordCount} words

Requirements:
- Use proper HTML formatting (h2, h3, p, ul, ol, li tags)
- Include introduction and conclusion
- Make content engaging and informative
- Naturally incorporate keywords (avoid keyword stuffing)
- Add actionable tips and examples where relevant
- Optimize for readability (short paragraphs, lists, subheadings)

Write the full article content in HTML format.
PROMPT;
    }

    /**
     * Build prompt for image prompt generation
     */
    private function buildImagePromptPrompt(array $data): string
    {
        $subject = $data['subject'] ?? '';
        $context = $data['context'] ?? '';
        $style = $data['style'] ?? 'professional photography';

        return <<<PROMPT
Create a detailed image generation prompt for an AI image generator (like DALL-E 3 or Midjourney).

Subject: {$subject}
Context: {$context}
Desired Style: {$style}

The prompt should:
- Be descriptive and specific
- Include lighting, composition, and mood details
- Specify quality indicators (e.g., "high resolution", "professional")
- Be in English
- Be 50-150 words

Return ONLY the image prompt, no explanations.
PROMPT;
    }

    /**
     * Call AI API with proper error handling
     */
    private function callAiApi(array $provider, string $prompt, array $options = []): array
    {
        $apiKey = match ($provider['id']) {
            'openai' => $this->settings->get('ai.openai_api_key'),
            'anthropic' => $this->settings->get('ai.anthropic_api_key'),
            'custom' => $this->settings->get('ai.custom_api_key'),
            default => null,
        };

        if (! $apiKey) {
            return ['success' => false, 'error' => 'API key not configured'];
        }

        try {
            $endpoint = match ($provider['id']) {
                'openai' => 'https://api.openai.com/v1/chat/completions',
                'anthropic' => 'https://api.anthropic.com/v1/messages',
                'custom' => $this->settings->get('ai.custom_api_base'),
                default => null,
            };

            if (! $endpoint) {
                return ['success' => false, 'error' => 'Invalid provider'];
            }

            $payload = $this->buildApiPayload($provider['id'], $prompt, $options);

            $headers = [
                'Content-Type' => 'application/json',
            ];

            if ($provider['id'] === 'openai') {
                $headers['Authorization'] = "Bearer {$apiKey}";
            } elseif ($provider['id'] === 'anthropic') {
                $headers['x-api-key'] = $apiKey;
                $headers['anthropic-version'] = '2023-06-01';
            } else {
                $headers['Authorization'] = "Bearer {$apiKey}";
            }

            $response = Http::withHeaders($headers)->post($endpoint, $payload);

            if ($response->successful()) {
                $data = $response->json();

                $content = $this->extractContent($provider['id'], $data);

                return [
                    'success' => true,
                    'content' => $content,
                    'usage' => $data['usage'] ?? [],
                    'raw_response' => $data,
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown API error',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build API payload based on provider
     */
    private function buildApiPayload(string $provider, string $prompt, array $options): array
    {
        if ($provider === 'openai') {
            return [
                'model' => $options['model'] ?? 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful assistant specialized in website creation and content strategy. Always respond with valid JSON when requested.',
                    ],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => $options['max_tokens'] ?? 4000,
                'temperature' => $options['temperature'] ?? 0.7,
            ] + ($options['response_format'] ?? []);
        }

        if ($provider === 'anthropic') {
            return [
                'model' => $options['model'] ?? 'claude-sonnet-4-20250514',
                'max_tokens' => $options['max_tokens'] ?? 4000,
                'system' => 'You are a helpful assistant specialized in website creation and content strategy. Always respond with valid JSON when requested.',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ];
        }

        // Custom provider (OpenAI-compatible)
        return [
            'model' => $options['model'] ?? 'gpt-4o-mini',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => $options['max_tokens'] ?? 4000,
            'temperature' => $options['temperature'] ?? 0.7,
        ];
    }

    /**
     * Extract content from API response
     */
    private function extractContent(string $provider, array $data): string
    {
        return match ($provider) {
            'openai' => $data['choices'][0]['message']['content'] ?? '',
            'anthropic' => $data['content'][0]['text'] ?? '',
            default => $data['choices'][0]['message']['content'] ?? '',
        };
    }

    /**
     * Normalize and validate structure
     */
    private function normalizeStructure(array $structure): array
    {
        // Ensure required fields exist
        $normalized = [
            'site_name' => $structure['site_name'] ?? 'New Website',
            'tagline' => $structure['tagline'] ?? '',
            'pages' => [],
            'menu' => [],
        ];

        // Normalize pages
        foreach ($structure['pages'] ?? [] as $page) {
            $normalized['pages'][] = [
                'title' => $page['title'] ?? 'Untitled Page',
                'uri' => '/'.Str::slug($page['title'] ?? 'page'),
                'meta_title' => $page['meta_title'] ?? $page['title'] ?? '',
                'meta_description' => $page['meta_description'] ?? '',
                'content' => $page['content'] ?? '',
                'blocks' => $page['blocks'] ?? [],
                'keywords' => $page['keywords'] ?? [],
            ];
        }

        // Normalize menu
        foreach ($structure['menu'] ?? [] as $menuItem) {
            $normalized['menu'][] = [
                'title' => $menuItem['title'] ?? 'Menu Item',
                'url' => $menuItem['url'] ?? '#',
                'children' => $menuItem['children'] ?? [],
            ];
        }

        return $normalized;
    }

    private function normalizePageContent(array $pageData): array
    {
        $blocks = $pageData['blocks'] ?? [];

        if ($blocks === [] && filled($pageData['content'] ?? null)) {
            $blocks = [['type' => 'text', 'data' => ['content' => $pageData['content']]]];
        }

        return ['version' => 1, 'blocks' => array_values($blocks)];
    }

    private function uniqueMenuSlug(string $slug): string
    {
        $base = Str::slug($slug) ?: 'main-menu';
        $candidate = $base;
        $suffix = 2;

        while (Menu::query()->where('slug', $candidate)->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }
}
