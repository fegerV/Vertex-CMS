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
 *
 * Поддерживаются три области правки:
 *  - page    — модель возвращает всю структуру страницы;
 *  - section — модель возвращает только выбранную секцию;
 *  - block   — модель возвращает только выбранный блок.
 * Для точечных областей ответ вливается в текущую структуру, остальные блоки и
 * секции остаются нетронутыми.
 */
class BuilderAiService
{
    private const MAX_ATTEMPTS = 2;

    private const MAX_TOKENS = 6000;

    private const SCOPES = ['page', 'section', 'block'];

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
     * @return array{success: bool, message?: string, sections?: array, provider?: array, usage?: array, dropped_blocks?: array, scope?: string, target?: array, error?: string, status?: int}
     */
    public function run(
        Page $page,
        string $instruction,
        array $currentSections = [],
        array $selection = [],
        ?string $providerId = null,
        string $scope = 'page',
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

        $scope = in_array($scope, self::SCOPES, true) ? $scope : 'page';
        $sections = $this->builder->normalizeSections($currentSections);
        $target = $this->resolveTarget($scope, $sections, $selection);

        if (! $target['ok']) {
            return [
                'success' => false,
                'status' => 422,
                'error' => 'ai_invalid_target',
                'message' => $target['error'],
            ];
        }

        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt($scope)],
            ['role' => 'user', 'content' => $this->userPayload($page, $instruction, $sections, $selection, $scope, $target)],
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

            $decoded = $this->decodeResponse((string) $response['content'], $scope);
            $dropped = [];

            if (! $decoded['success']) {
                $lastError = $decoded['error'];
            } else {
                $applied = $this->applyScope($scope, $sections, $target, $decoded);

                if ($applied['success']) {
                    $errors = $this->builder->validateBlocks($applied['sections']);
                    $dropped = $applied['dropped'];

                    if ($errors === []) {
                        return [
                            'success' => true,
                            'message' => $decoded['message'] !== ''
                                ? $decoded['message']
                                : 'AI обновил структуру страницы.',
                            'sections' => $applied['sections'],
                            'scope' => $scope,
                            'target' => [
                                'section_index' => $target['section_index'],
                                'block_index' => $target['block_index'],
                                'label' => $target['label'],
                            ],
                            'provider' => [
                                'id' => $provider['id'],
                                'name' => $provider['name'],
                            ],
                            'usage' => $response['usage'] ?? [],
                            'dropped_blocks' => $dropped,
                        ];
                    }

                    $lastError = implode('; ', $errors);
                } else {
                    $lastError = $applied['error'];
                }
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
            'scope' => $scope,
            'error' => $lastError,
        ]);

        return [
            'success' => false,
            'status' => 422,
            'error' => 'ai_invalid_response',
            'message' => 'AI вернул структуру, которую конструктор не смог применить: '.$lastError,
        ];
    }

    private function systemPrompt(string $scope): string
    {
        $contract = match ($scope) {
            'section' => <<<'JSON'
{
  "message": "короткое описание изменений на языке пользователя",
  "section": {
    "settings": {"background_color": "#ffffff", "padding_top": 64, "padding_bottom": 64},
    "blocks": [
      {"type": "heading", "settings": {"text": "Заголовок", "level": "h2"}}
    ]
  }
}
JSON,
            'block' => <<<'JSON'
{
  "message": "короткое описание изменений на языке пользователя",
  "block": {
    "type": "heading",
    "settings": {"text": "Новый заголовок", "level": "h2"}
  }
}
JSON,
            default => <<<'JSON'
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
JSON,
        };

        $scopeRule = match ($scope) {
            'section' => 'Ты правишь ТОЛЬКО одну секцию: верни её целиком в поле section. Остальные секции страницы не возвращай — они останутся без изменений.',
            'block' => 'Ты правишь ТОЛЬКО один блок: верни его в поле block. Секции и соседние блоки не возвращай — они останутся без изменений.',
            default => 'Верни ВСЮ новую структуру страницы в поле sections (полный массив), а не патч.',
        };

        return <<<PROMPT
Ты — AI-агент визуального конструктора страниц VertexCMS (аналог Breakdance).
Ты создаёшь и редактируешь структуру страницы через блоки конструктора.

ФОРМАТ ОТВЕТА — строго один JSON-объект, без markdown и пояснений:
{$contract}

ОБЛАСТЬ ПРАВКИ:
{$scopeRule}

ПРАВИЛА:
1. Используй ТОЛЬКО типы блоков из каталога и только поля, перечисленные для этого блока.
2. Не меняй то, о чём не просили: правь только содержимое целевой области.
3. Сохраняй разумный порядок блоков: заголовок -> текст -> медиа -> CTA -> FAQ.
4. Тексты пиши качественные, конкретные, без Lorem ipsum, на языке инструкции пользователя.
5. Не выдумывай медиа-id и внешние URL картинок; оставляй поля media_id/url пустыми, если пользователь не дал ссылку.
6. Для блоков-повторителей (repeater) items — массив объектов с полями, указанными в каталоге.
7. Не добавляй HTML-теги в текстовые поля, если поле не является html.
8. Если инструкция не про страницу — верни текущее содержимое без изменений и объясни в message.
PROMPT;
    }

    private function userPayload(
        Page $page,
        string $instruction,
        array $sections,
        array $selection,
        string $scope,
        array $target,
    ): string {
        $payload = [
            'page' => [
                'title' => $page->title,
                'uri' => $page->uri,
                'slug' => $page->slug,
            ],
            'instruction' => $instruction,
            'scope' => $scope,
        ];

        if ($scope === 'block') {
            $payload['target_block'] = $sections[$target['section_index']]['blocks'][$target['block_index']];
            $payload['target_path'] = $target['label'];
        } elseif ($scope === 'section') {
            $payload['target_section'] = $sections[$target['section_index']];
            $payload['target_path'] = $target['label'];
        }

        $payload['current_sections'] = $sections;
        $payload['selected'] = $selection;
        $payload['block_catalog'] = $this->blockCatalog();

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
     * Проверяет, что выбранная область существует в текущей структуре.
     *
     * @return array{ok: bool, section_index: ?int, block_index: ?int, label: string, error?: string}
     */
    private function resolveTarget(string $scope, array $sections, array $selection): array
    {
        if ($scope === 'page') {
            return [
                'ok' => true,
                'section_index' => null,
                'block_index' => null,
                'label' => 'Вся страница',
            ];
        }

        $sectionIndex = $selection['section_index'] ?? null;

        if (! is_int($sectionIndex) && ! (is_string($sectionIndex) && ctype_digit($sectionIndex))) {
            return [
                'ok' => false,
                'section_index' => null,
                'block_index' => null,
                'label' => '',
                'error' => 'Сначала выберите секцию на холсте.',
            ];
        }

        $sectionIndex = (int) $sectionIndex;

        if (! isset($sections[$sectionIndex])) {
            return [
                'ok' => false,
                'section_index' => null,
                'block_index' => null,
                'label' => '',
                'error' => 'Выбранная секция не найдена на странице.',
            ];
        }

        if ($scope === 'section') {
            return [
                'ok' => true,
                'section_index' => $sectionIndex,
                'block_index' => null,
                'label' => 'Секция '.($sectionIndex + 1),
            ];
        }

        $blockIndex = $selection['block_index'] ?? null;

        if (! is_int($blockIndex) && ! (is_string($blockIndex) && ctype_digit($blockIndex))) {
            return [
                'ok' => false,
                'section_index' => null,
                'block_index' => null,
                'label' => '',
                'error' => 'Сначала выберите блок на холсте.',
            ];
        }

        $blockIndex = (int) $blockIndex;

        if (! isset($sections[$sectionIndex]['blocks'][$blockIndex])) {
            return [
                'ok' => false,
                'section_index' => null,
                'block_index' => null,
                'label' => '',
                'error' => 'Выбранный блок не найден на странице.',
            ];
        }

        return [
            'ok' => true,
            'section_index' => $sectionIndex,
            'block_index' => $blockIndex,
            'label' => 'Секция '.($sectionIndex + 1).', блок '.($blockIndex + 1),
        ];
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
     * @return array{success: bool, message?: string, sections?: array, section?: array, block?: array, blocks?: array, error?: string}
     */
    private function decodeResponse(string $content, string $scope): array
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
            if ($scope === 'block') {
                return [
                    'success' => true,
                    'message' => '',
                    'block' => is_array($decoded[0] ?? null) ? $decoded[0] : null,
                ];
            }

            return [
                'success' => true,
                'message' => '',
                'sections' => $decoded,
            ];
        }

        $message = trim((string) ($decoded['message'] ?? ''));

        if ($scope === 'block') {
            $block = $decoded['block'] ?? (is_array($decoded['blocks'][0] ?? null) ? $decoded['blocks'][0] : null);

            if (! is_array($block)) {
                return ['success' => false, 'error' => 'В ответе нет блока (block)'];
            }

            return ['success' => true, 'message' => $message, 'block' => $block];
        }

        if ($scope === 'section') {
            $section = $decoded['section'] ?? null;

            if ($section === null && is_array($decoded['blocks'] ?? null)) {
                $section = [
                    'settings' => is_array($decoded['settings'] ?? null) ? $decoded['settings'] : [],
                    'blocks' => $decoded['blocks'],
                ];
            }

            if (! is_array($section)) {
                return ['success' => false, 'error' => 'В ответе нет секции (section)'];
            }

            return ['success' => true, 'message' => $message, 'section' => $section];
        }

        $sections = $decoded['sections'] ?? null;

        if (! is_array($sections)) {
            return ['success' => false, 'error' => 'В ответе нет массива sections'];
        }

        return ['success' => true, 'message' => $message, 'sections' => $sections];
    }

    /**
     * Вливает ответ модели в текущую структуру согласно области правки.
     *
     * @return array{success: bool, sections?: array, dropped?: array, error?: string}
     */
    private function applyScope(string $scope, array $sections, array $target, array $decoded): array
    {
        if ($scope === 'page') {
            $sanitized = $this->sanitizeSections($decoded['sections'] ?? []);

            return [
                'success' => true,
                'sections' => $sanitized['sections'],
                'dropped' => $sanitized['dropped'],
            ];
        }

        if ($scope === 'block') {
            $block = is_array($decoded['block'] ?? null) ? $decoded['block'] : null;

            if ($block === null) {
                return ['success' => false, 'error' => 'Блок не найден в ответе'];
            }

            $type = (string) ($block['type'] ?? '');

            if ($type === '' || BlockRegistry::get($type) === null) {
                return ['success' => false, 'error' => 'Неизвестный тип блока: '.$type];
            }

            $sectionIndex = (int) $target['section_index'];
            $blockIndex = (int) $target['block_index'];
            $original = $sections[$sectionIndex]['blocks'][$blockIndex];

            $sections[$sectionIndex]['blocks'][$blockIndex] = [
                'id' => (string) ($original['id'] ?? ''),
                'type' => $type,
                'settings' => array_merge(
                    (array) ($original['settings'] ?? []),
                    is_array($block['settings'] ?? null) ? $block['settings'] : [],
                ),
            ];

            return ['success' => true, 'sections' => array_values($sections), 'dropped' => []];
        }

        $rawSection = is_array($decoded['section'] ?? null) ? $decoded['section'] : null;

        if ($rawSection === null) {
            return ['success' => false, 'error' => 'Секция не найдена в ответе'];
        }

        $sanitized = $this->sanitizeSections([$rawSection]);
        $replacement = $sanitized['sections'][0] ?? null;

        if ($replacement === null) {
            return ['success' => false, 'error' => 'Не удалось разобрать секцию'];
        }

        $sectionIndex = (int) $target['section_index'];
        $original = $sections[$sectionIndex];

        $replacement['id'] = (string) ($original['id'] ?? $replacement['id']);
        $replacement['settings'] = array_merge(
            (array) ($original['settings'] ?? []),
            (array) ($replacement['settings'] ?? []),
        );

        $replacement['blocks'] = array_values(array_map(
            function (array $block, int $index) use ($original): array {
                if (($original['blocks'][$index]['type'] ?? null) === ($block['type'] ?? null)) {
                    $block['id'] = (string) ($original['blocks'][$index]['id'] ?? $block['id']);
                }

                return $block;
            },
            $replacement['blocks'] ?? [],
            array_keys($replacement['blocks'] ?? []),
        ));

        $sections[$sectionIndex] = $replacement;

        return ['success' => true, 'sections' => array_values($sections), 'dropped' => $sanitized['dropped']];
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
