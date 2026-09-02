<?php

namespace App\Services\AI;

use App\Models\Chatbot;
use App\Models\ChatbotRule;
use App\Models\AiChatSession;
use App\Models\AiChatMessage;
use App\Models\AiKbChunk;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * Модульный сервис для управления AI-чатботами
 * Поддерживает множественных ботов с индивидуальными настройками
 */
class ModularChatbotService
{
    private ContentGenerationService $generationService;
    private EmbeddingService $embeddingService;

    public function __construct(
        ContentGenerationService $generationService,
        EmbeddingService $embeddingService
    ) {
        $this->generationService = $generationService;
        $this->embeddingService = $embeddingService;
    }

    /**
     * Обработать сообщение пользователя через указанного бота
     */
    public function processMessage(
        string $sessionId,
        string $message,
        int $chatbotId,
        array $pageContext = [],
        array $userContext = []
    ): array {
        // 1. Загрузить конфигурацию бота
        $chatbot = Chatbot::find($chatbotId);
        
        if (!$chatbot || !$chatbot->isActive()) {
            return [
                'success' => false,
                'answer' => 'Чат-бот временно недоступен.',
                'sources' => [],
                'confidence' => 0.0,
                'error' => 'BOT_NOT_FOUND',
            ];
        }

        // 2. Проверить лимиты (rate limiting)
        if (!$this->checkRateLimits($sessionId, $chatbot)) {
            return [
                'success' => false,
                'answer' => 'Слишком много запросов. Пожалуйста, подождите немного.',
                'sources' => [],
                'confidence' => 0.0,
                'error' => 'RATE_LIMIT_EXCEEDED',
            ];
        }

        // 3. Создать или обновить сессию с контекстом страницы
        $session = $this->getOrCreateSession($sessionId, $chatbot, $pageContext, $userContext);

        // 4. Проверить правила бота (Rules Engine)
        $ruleResult = $this->checkRules($chatbot, $message, [
            'session_id' => $sessionId,
            'page_uri' => $pageContext['uri'] ?? null,
            'page_title' => $pageContext['title'] ?? null,
            'user_context' => $userContext,
        ]);

        // Если правило обработало сообщение и требует блокировки LLM
        if ($ruleResult['handled'] && $ruleResult['should_block_llm']) {
            // Сохранить сообщения
            $this->saveMessage($session->id, 'user', $message);
            $this->saveMessage($session->id, 'assistant', $ruleResult['response'] ?? 'Запрос обрабатывается.');

            return [
                'success' => true,
                'answer' => $ruleResult['response'],
                'sources' => [],
                'confidence' => 1.0,
                'form_schema' => $ruleResult['form_schema'] ?? null,
                'webhook_data' => $ruleResult['webhook_response'] ?? null,
                'rule_triggered' => true,
            ];
        }

        // 5. Построить промпт с контекстом страницы
        $systemPrompt = $chatbot->getSystemPromptWithPageContext(
            $pageContext['title'] ?? null,
            $pageContext['excerpt'] ?? null
        );

        // 6. Получить релевантные чанки из базы знаний (если включено)
        $relevantChunks = [];
        if ($chatbot->use_knowledge_base) {
            $relevantChunks = $this->embeddingService->findRelevantChunks(
                $message,
                $chatbot->max_context_chunks
            );
        }

        // 7. Построить контекст из чанков
        $context = $this->buildContext($relevantChunks);

        // 8. Получить историю чата
        $history = $this->getChatHistory($session->id);

        // 9. Сформировать полный промпт
        $fullPrompt = $this->buildFullPrompt($systemPrompt, $context, $message, $history);

        // 10. Вызвать LLM
        try {
            $llmResponse = $this->callLLM($chatbot, $fullPrompt, $history, $message);

            // 11. Сохранить сообщения
            $this->saveMessage($session->id, 'user', $message);
            $this->saveMessage(
                $session->id,
                'assistant',
                $llmResponse['content'],
                $relevantChunks,
                $llmResponse['tokens'],
                $llmResponse['confidence']
            );

            // 12. Подготовить источники
            $sources = $this->formatSources($relevantChunks);

            return [
                'success' => true,
                'answer' => $llmResponse['content'],
                'sources' => $sources,
                'confidence' => $llmResponse['confidence'],
                'tokens_used' => $llmResponse['tokens'],
                'rule_triggered' => false,
            ];

        } catch (\Exception $e) {
            Log::error('ModularChatbot error: ' . $e->getMessage());

            return [
                'success' => false,
                'answer' => 'Произошла ошибка при обработке вашего запроса. Пожалуйста, попробуйте позже.',
                'sources' => [],
                'confidence' => 0.0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Проверить правила для бота
     */
    private function checkRules(Chatbot $chatbot, string $message, array $context = []): array
    {
        $rules = $chatbot->rules()->where('is_active', true)->get();

        foreach ($rules as $rule) {
            /** @var ChatbotRule $rule */
            if ($rule->matchesConditions($message, $context)) {
                return $rule->executeActions($message, $context);
            }

            if ($rule->stop_on_match) {
                break;
            }
        }

        return ['handled' => false, 'response' => null, 'data' => []];
    }

    /**
     * Создать или получить сессию с контекстом страницы
     */
    private function getOrCreateSession(
        string $sessionId,
        Chatbot $chatbot,
        array $pageContext = [],
        array $userContext = []
    ): AiChatSession {
        $session = AiChatSession::firstOrCreate(
            ['session_id' => $sessionId],
            [
                'chatbot_id' => $chatbot->id,
                'user_ip' => request()->ip() ?? null,
                'user_agent' => request()->userAgent() ?? null,
                'page_uri' => $pageContext['uri'] ?? null,
                'page_title' => $pageContext['title'] ?? null,
                'page_excerpt' => $pageContext['excerpt'] ?? null,
                'page_metadata' => $pageContext['metadata'] ?? null,
                'user_id' => $userContext['user_id'] ?? null,
            ]
        );

        // Обновить контекст страницы если он изменился
        if ($session->page_uri !== ($pageContext['uri'] ?? null)) {
            $session->update([
                'page_uri' => $pageContext['uri'] ?? null,
                'page_title' => $pageContext['title'] ?? null,
                'page_excerpt' => $pageContext['excerpt'] ?? null,
                'page_metadata' => $pageContext['metadata'] ?? null,
            ]);
        }

        return $session;
    }

    /**
     * Проверить лимиты сообщений
     */
    private function checkRateLimits(string $sessionId, Chatbot $chatbot): bool
    {
        $now = now();
        $minuteAgo = $now->copy()->subMinute();
        $hourAgo = $now->copy()->subHour();

        $session = AiChatSession::where('session_id', $sessionId)->first();
        
        if (!$session) {
            return true;
        }

        // Лимит в минуту
        $messagesLastMinute = AiChatMessage::where('session_id', $session->id)
            ->where('created_at', '>=', $minuteAgo)
            ->count();

        if ($messagesLastMinute >= $chatbot->rate_limit_per_minute) {
            return false;
        }

        // Лимит в час
        $messagesLastHour = AiChatMessage::where('session_id', $session->id)
            ->where('created_at', '>=', $hourAgo)
            ->count();

        if ($messagesLastHour >= $chatbot->rate_limit_per_hour) {
            return false;
        }

        return true;
    }

    /**
     * Построить контекст из чанков
     */
    private function buildContext(array $chunks): string
    {
        if (empty($chunks)) {
            return '';
        }

        $contextParts = [];

        foreach ($chunks as $index => $chunk) {
            $contextParts[] = "[Источник " . ($index + 1) . "]: {$chunk['content']}";
        }

        return implode("\n\n", $contextParts);
    }

    /**
     * Построить полный промпт для LLM
     */
    private function buildFullPrompt(
        string $systemPrompt,
        string $context,
        string $message,
        array $history
    ): string {
        $prompt = $systemPrompt;

        if (!empty($context)) {
            $prompt .= "\n\n[КОНТЕКСТ ИЗ БАЗЫ ЗНАНИЙ]\n{$context}\n[/КОНТЕКСТ ИЗ БАЗЫ ЗНАНИЙ]";
        }

        return $prompt;
    }

    /**
     * Вызов LLM с настройками бота
     */
    private function callLLM(Chatbot $chatbot, string $prompt, array $history, string $currentMessage): array
    {
        $messages = array_merge(
            [['role' => 'system', 'content' => $prompt]],
            $history,
            [['role' => 'user', 'content' => $currentMessage]]
        );

        // Получить API ключ из конфигурации провайдера
        $apiKey = $this->getProviderApiKey($chatbot->provider);
        $apiUrl = $this->getProviderApiUrl($chatbot->provider);

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type' => 'application/json',
        ])->post($apiUrl, [
            'model' => $chatbot->model,
            'messages' => $messages,
            'max_tokens' => $chatbot->max_tokens_per_message,
            'temperature' => 0.7,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'content' => $data['choices'][0]['message']['content'],
                'tokens' => $data['usage']['total_tokens'] ?? 0,
                'confidence' => 0.9,
            ];
        }

        throw new \RuntimeException('LLM API error: ' . $response->status());
    }

    /**
     * Получить API ключ для провайдера
     */
    private function getProviderApiKey(string $provider): string
    {
        return match ($provider) {
            'openai' => config('services.openai.api_key', env('OPENAI_API_KEY', '')),
            'anthropic' => config('services.anthropic.api_key', env('ANTHROPIC_API_KEY', '')),
            default => config('services.openai.api_key', env('OPENAI_API_KEY', '')),
        };
    }

    /**
     * Получить URL API для провайдера
     */
    private function getProviderApiUrl(string $provider): string
    {
        return match ($provider) {
            'openai' => 'https://api.openai.com/v1/chat/completions',
            'anthropic' => 'https://api.anthropic.com/v1/messages',
            'ollama' => config('services.ollama.url', 'http://localhost:11434/api/chat'),
            default => 'https://api.openai.com/v1/chat/completions',
        };
    }

    /**
     * Форматировать источники для ответа
     */
    private function formatSources(array $chunks): array
    {
        return array_map(function($chunk) {
            return [
                'chunk_id' => $chunk['chunk']->id,
                'title' => $chunk['chunk']->document->title,
                'content_preview' => substr($chunk['content'], 0, 150) . '...',
                'similarity' => round($chunk['similarity'] * 100, 1),
            ];
        }, $chunks);
    }

    /**
     * Получить историю чата
     */
    private function getChatHistory(int $sessionId): array
    {
        $messages = AiChatMessage::where('session_id', $sessionId)
            ->where('role', '!=', 'system')
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        return $messages->map(function($msg) {
            return [
                'role' => $msg->role,
                'content' => $msg->content,
            ];
        })->toArray();
    }

    /**
     * Сохранить сообщение в БД
     */
    private function saveMessage(
        int $sessionId,
        string $role,
        string $content,
        array $sources = [],
        int $tokens = 0,
        float $confidence = 0.0
    ): void {
        $sourceData = array_map(function($chunk) {
            return ['chunk_id' => $chunk['chunk']->id];
        }, $sources);

        AiChatMessage::create([
            'session_id' => $sessionId,
            'role' => $role,
            'content' => $content,
            'sources' => $sourceData,
            'tokens_used' => $tokens,
            'confidence_score' => $confidence,
        ]);
    }

    /**
     * Получить активного бота по slug
     */
    public function getChatbotBySlug(string $slug): ?Chatbot
    {
        return Chatbot::where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Получить всех активных ботов для страницы
     */
    public function getActiveBotsForPage(string $uri): array
    {
        $bots = Chatbot::where('is_active', true)->get();

        return $bots->filter(function($bot) use ($uri) {
            return $bot->shouldAppearOnPage($uri);
        })->values()->toArray();
    }
}
