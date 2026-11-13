<?php

namespace App\Builder\Services;

use App\AI\Services\AiProviderRegistry;
use App\Builder\Config\BlockRegistry;
use App\Core\Services\SettingsService;
use App\Models\Page;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AI-агент конструктора страниц.
 *
 * Принимает инструкцию на естественном языке, текущую структуру страницы и
 * каталог доступных блоков, просит модель вернуть JSON-секции и приводит ответ
 * к контракту билдера (те же правила, что и у ручного сохранения).
 */
class BuilderAiService
{
    private const MAX_ATTEMPTS = 2;

    private const MAX_TOKENS = 6000;

    public function __construct(
        private readonly AiProviderRegistry $providers,
        private readonly PageBuilderService $builder,
        private readonly SettingsService $settings,
    ) {
    }

    /**
     * Данные для панели AI внутри конструктора.
     */
    public function assistantContext(?string $providerId = null): array
    {
        $provider = $this->providers->find($providerId);

        return [
            'enabled' => $this->isEnabled(),
            'configured' => (bool) ($provider['configured'] ?? false),
            'provider' => $provider['id'] ?? null,
            'provider_name' => $provider['name'] ?? null,
            'providers' => collect($this->providers->all())
                ->map(fn (array $item): array => [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'configured' => (bool) $item['configured'],
                ])
                ->values()
                ->all(),
            'settings_url' => '/admin/settings',
        ];
    }

    public function isEnabled(): bool
    {
        return (bool) $this->settings->get('ai.enabled', false);
    }

    /**
     * Выполнить запрос агента и вернуть валидные секции билдера.
     *
     * @return array{success: bool, message?: string, sections?: array, provider?: array, usage?: array, dropped_blocks?: array, error?: string, status?: int}
     */
    public function run(
        Page $page,
        string $instruction,
        array $currentSections = [],
        array $selection = [],
        ?string $providerId = null,
    ): array {
        if (! $this->isEnabled()) {
            return [
                'success' => false,
                'status' => 422,
                'error' => 'ai_disabled',
                'message' => 'AI-модуль отключён. Включите его в настройках AI.',
            ];
        }

        $provider = $this->providers->find($providerId);

        if (! $provider || ! ($provider['configured'] ?? false)) {
            return [
                'success' => false,
                'status' => 422,
                'error' => 'ai_provider_not_configured',
                'message' => 'AI-провайдер не настроен. Добавьте API-ключ в настройках AI.',
            ];
        }

        $instruction = trim($instruction);
        if ($instruction === '') {
            return [
                'success' => false,
                'status' => 422,
                'error' => 'ai_empty_instruction',
                'message' => 'Опишите, что нужно сделать на странице.',
            ];
        }

        $sections = $this->builder->normalizeSections($currentSections);
        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt()],
            ['role' => 'user', 'content' => $this->userPayload($page, $instruction, $sections, $selection)],
        ];

        $lastError = null;

        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            $response = $this->complete($provider, $messages);

            if (! $response['success']) {
                return [
                    'success' => false,
                    'status' => 502,
                    'error' => 'ai_request_failed',
                    'message' => $response['error'] ?? 'AI-провайдер вернул ошибку.',
                ];
            }

            $decoded = $this->decodeResponse((string) $response['content']);

            if (! $decoded['success']) {
                $lastError = $decoded['error'];
            } else {
                $candidate = $this->sanitizeSections($decoded['sections']);
                $errors = $this->builder->validateBlocks($candidate['sections']);

                if ($errors === []) {
                    return [
                        'success' => true,
                        'message' => $decoded['message'] !== ''
                            ? $decoded['message']
                            : 'AI обновил структуру страницы.',
                        'sections' => $candidate['sections'],
                        'provider' => [
                            'id' => $provider['id'],
                            'name' => $provider['name'],
                        ],
                        'usage' => $response['usage'] ?? [],
                        'dropped_blocks' => $candidate['dropped'],
                    ];
                }

                $lastError = implode('; ', $errors);
            }

            if ($attempt < self::MAX_ATTEMPTS) {
                $messages[] = ['role' => 'assistant', 'content' => (string) $response['content']];
                $messages[] = [
                    'role' => 'user',
                    'content' => 'Ответ отклонён валидатором: '.$lastError
                        .'. Верни ТОЛЬКО исправленный JSON по контракту, используя только блоки из каталога.',
                ];
            }
        }

        Log::warning('Builder AI returned invalid structure.', [
            'page_id' => $page->id,
            'error' => $lastError,
        ]);

        return [
            'success' => false,
            'status' => 422,
            'error' => 'ai_invalid_response',
            'message' => 'AI вернул структуру, которую конструктор не смог применить: '.$lastError,
        ];
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
Ты — AI-агент визуального конструктора страниц VertexCMS (аналог Breakdance).
Ты создаёшь и редактируешь структуру страницы через блоки конструктора.

ФОРМАТ ОТВЕТА — строго один JSON-объект, без markdown и пояснений:
{
  "message": "короткое описание изменений на языке пользователя (1-2 предложения)",
  "sections": [
    {
      "id": "уникальная строка",
      "settings": {"background_color": "#ffffff", "padding_top": 64, "padding_bottom": 64},
      "blocks": [
        {"type": "heading", "settings": {"text": "Заголовок", "level": "h1"}}
      ]
    }
  ]
}

ПРАВИЛА:
1. Используй ТОЛЬКО типы блоков из каталога и только поля, перечисленные для этого блока.
2. Если пользователь просит изменить/добавить/удалить часть страницы — верни ВСЮ новую структуру секций (полный массив), а не патч.
3. Сохраняй разумный порядок: hero/heading -> текст -> медиа -> CTA -> FAQ.
4. Тексты пиши качественные, конкретные, без Lorem ipsum, на языке инструкции пользователя.
5. Не выдумывай медиа-id и внешние URL картинок; оставляй поля media_id/url пустыми, если пользователь не дал ссылку.
6. Для блоков-повторителей (repeater) items — массив объектов с полями, указанными в каталоге.
7. Не добавляй HTML-теги в текстовые поля, если поле не является html.
8. Если инструкция не про страницу — верни текущую структуру без изменений и объясни в message.
PROMPT;
    }

    private function userPayload(Page $page, string $instruction, array $sections, array $selection): string
    {
        $payload = [
            'page' => [
                'title' => $page->title,
                'uri' => $page->uri,
                'slug' => $page->slug,
            ],
            'instruction' => $instruction,
            'current_sections' => $sections,
            'selected' => $selection,
            'block_catalog' => $this->blockCatalog(),
        ];

        $language = (string) $this->settings->get('ai.content_language', '');
        if ($language !== '') {
            $payload['content_language'] = $language;
        }

        $brandVoice = (string) $this->settings->get('ai.brand_voice', '');
        if ($brandVoice !== '') {
            $payload['brand_voice'] = $brandVoice;
        }

        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?: '{}';
    }

    /**
     * Компактный каталог блоков: тип -> поля, чтобы модель не выдумывала ключи.
     */
    private function blockCatalog(): array
    {
        $catalog = [];

        foreach (BlockRegistry::all() as $type => $block) {
            $fields = [];

            foreach ((array) ($block['fields'] ?? []) as $key => $field) {
                $fields[(string) $key] = $this->fieldDefinition((array) $field);
            }

            $catalog[(string) $type] = [
                'name' => (string) ($block['name'] ?? $type),
                'fields' => $fields === [] ? new \stdClass() : $fields,
            ];
        }

        return $catalog;
    }

    private function fieldDefinition(array $field): array
    {
        $type = (string) ($field['type'] ?? 'text');
        $definition = ['type' => $type];

        if ($type === 'select' && is_array($field['options'] ?? null)) {
            $definition['options'] = array_map('strval', array_keys($field['options']));
        }

        if (! empty($field['required'])) {
            $definition['required'] = true;
        }

        if ($type === 'repeater' && is_array($field['fields'] ?? null)) {
            $definition['item_fields'] = collect($field['fields'])
                ->mapWithKeys(fn (array $nested): array => [
                    (string) ($nested['key'] ?? 'item') => (string) ($nested['type'] ?? 'text'),
                ])
                ->all();
        }

        return $definition;
    }

    /**
     * @return array{success: bool, message?: string, sections?: array, error?: string}
     */
    private function decodeResponse(string $content): array
    {
        $content = trim($content);
        $content = preg_replace('/^```(?:json)?\s*|\s*```$/u', '', $content) ?? $content;

        $start = strpos($content, '{');
        $arrayStart = strpos($content, '[');

        if ($arrayStart !== false && ($start === false || $arrayStart < $start)) {
            $end = strrpos($content, ']');
            $json = $end !== false ? substr($content, $arrayStart, $end - $arrayStart + 1) : null;
        } else {
            $end = strrpos($content, '}');
            $json = $start !== false && $end !== false
                ? substr($content, $start, $end - $start + 1)
                : null;
        }

        if ($json === null) {
            return ['success' => false, 'error' => 'JSON не найден в ответе модели'];
        }

        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            return ['success' => false, 'error' => 'JSON не разобран: '.json_last_error_msg()];
        }

        if (array_is_list($decoded)) {
            return [
                'success' => true,
                'message' => '',
                'sections' => $decoded,
            ];
        }

        $sections = $decoded['sections'] ?? null;

        if (! is_array($sections)) {
            return ['success' => false, 'error' => 'В ответе нет массива sections'];
        }

        return [
            'success' => true,
            'message' => trim((string) ($decoded['message'] ?? '')),
            'sections' => $sections,
        ];
    }

    /**
     * Убирает блоки, которых нет в реестре, и нормализует структуру.
     *
     * @return array{sections: array, dropped: array}
     */
    private function sanitizeSections(array $sections): array
    {
        $normalized = $this->builder->normalizeSections($sections);
        $dropped = [];

        foreach ($normalized as $sectionIndex => $section) {
            $kept = [];

            foreach ($section['blocks'] ?? [] as $block) {
                $type = (string) ($block['type'] ?? '');

                if ($type === '' || BlockRegistry::get($type) === null) {
                    $dropped[] = $type !== '' ? $type : 'unknown';

                    continue;
                }

                $kept[] = $block;
            }

            $normalized[$sectionIndex]['blocks'] = $kept;
        }

        return [
            'sections' => array_values($normalized),
            'dropped' => array_values(array_unique($dropped)),
        ];
    }

    /**
     * @return array{success: bool, content?: string, usage?: array, error?: string}
     */
    private function complete(array $provider, array $messages): array
    {
        $providerId = (string) ($provider['id'] ?? '');

        $apiKey = match ($providerId) {
            'openai' => $this->settings->get('ai.openai_api_key'),
            'anthropic' => $this->settings->get('ai.anthropic_api_key'),
            'custom' => $this->settings->get('ai.custom_api_key'),
            default => null,
        };

        $endpoint = match ($providerId) {
            'openai' => 'https://api.openai.com/v1/chat/completions',
            'anthropic' => 'https://api.anthropic.com/v1/messages',
            'custom' => $this->settings->get('ai.custom_api_base'),
            default => null,
        };

        if (! $apiKey || ! $endpoint) {
            return ['success' => false, 'error' => 'AI-провайдер не настроен'];
        }

        $model = (string) $this->settings->get('ai.default_model', '');

        try {
            if ($providerId === 'anthropic') {
                $system = '';
                $chatMessages = [];

                foreach ($messages as $message) {
                    if (($message['role'] ?? '') === 'system') {
                        $system = (string) $message['content'];

                        continue;
                    }

                    $chatMessages[] = $message;
                }

                $payload = array_filter([
                    'model' => $model !== '' ? $model : 'claude-sonnet-4-20250514',
                    'max_tokens' => self::MAX_TOKENS,
                    'temperature' => 0.4,
                    'system' => $system,
                    'messages' => $chatMessages,
                ], fn ($value) => $value !== '' && $value !== null);
                $response = Http::timeout(90)->withHeaders([
                    'x-api-key' => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'Content-Type' => 'application/json',
                ])->post($endpoint, $payload);
            } else {
                $payload = [
                    'model' => $model !== '' ? $model : 'gpt-4o-mini',
                    'messages' => $messages,
                    'max_tokens' => self::MAX_TOKENS,
                    'temperature' => 0.4,
                ];

                if ($providerId === 'openai') {
                    $payload['response_format'] = ['type' => 'json_object'];
                }

                $response = Http::timeout(90)->withHeaders([
                    'Authorization' => 'Bearer '.$apiKey,
                    'Content-Type' => 'application/json',
                ])->post($endpoint, $payload);
            }

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'error' => (string) ($response->json()['error']['message'] ?? 'HTTP '.$response->status()),
                ];
            }

            $data = $response->json();
            $content = match ($providerId) {
                'anthropic' => (string) ($data['content'][0]['text'] ?? ''),
                default => (string) ($data['choices'][0]['message']['content'] ?? ''),
            };

            if (trim($content) === '') {
                return ['success' => false, 'error' => 'Пустой ответ модели'];
            }

            return [
                'success' => true,
                'content' => $content,
                'usage' => is_array($data['usage'] ?? null) ? $data['usage'] : [],
            ];
        } catch (\Throwable $e) {
            Log::warning('Builder AI request failed.', [
                'provider' => $providerId,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
