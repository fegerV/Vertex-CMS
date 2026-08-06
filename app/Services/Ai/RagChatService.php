<?php

namespace App\Services\Ai;

use App\Models\AiKbChunk;
use App\Models\AiChatSession;
use App\Models\AiChatMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * RAG (Retrieval-Augmented Generation) сервис
 * Отвечает за генерацию ответов на основе найденных знаний
 */
class RagChatService
{
    private EmbeddingService $embeddingService;
    private string $apiKey;
    private string $chatApiUrl;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
        $this->apiKey = config('services.openai.api_key', env('OPENAI_API_KEY', ''));
        $this->chatApiUrl = 'https://api.openai.com/v1/chat/completions';
    }

    /**
     * Обработать вопрос пользователя и вернуть ответ с источниками
     */
    public function processQuery(string $sessionId, string $userMessage): array
    {
        // 1. Найти релевантные чанки из базы знаний
        $relevantChunks = $this->embeddingService->findRelevantChunks($userMessage, 5);

        if (empty($relevantChunks)) {
            return [
                'answer' => "Извините, я не нашел информации по вашему вопросу в базе знаний. Попробуйте переформулировать вопрос или обратитесь к оператору.",
                'sources' => [],
                'confidence' => 0.0,
            ];
        }

        // 2. Подготовить контекст из найденных чанков
        $context = $this->buildContext($relevantChunks);

        // 3. Сформировать промпт для LLM
        $prompt = $this->buildPrompt($userMessage, $context);

        // 4. Получить историю чата для контекста диалога
        $history = $this->getChatHistory($sessionId);

        // 5. Запрос к LLM
        $llmResponse = $this->callLLM($prompt, $history, $userMessage);

        // 6. Сохранить сообщения в БД
        $this->saveMessage($sessionId, 'user', $userMessage);
        $this->saveMessage($sessionId, 'assistant', $llmResponse['content'], $relevantChunks, $llmResponse['tokens'], $llmResponse['confidence']);

        // 7. Подготовить источники для отображения
        $sources = array_map(function($chunk) {
            return [
                'chunk_id' => $chunk['chunk']->id,
                'title' => $chunk['chunk']->document->title,
                'content_preview' => substr($chunk['content'], 0, 150) . '...',
                'similarity' => round($chunk['similarity'] * 100, 1),
            ];
        }, $relevantChunks);

        return [
            'answer' => $llmResponse['content'],
            'sources' => $sources,
            'confidence' => $llmResponse['confidence'],
        ];
    }

    /**
     * Построить контекст из найденных чанков
     */
    private function buildContext(array $chunks): string
    {
        $contextParts = [];

        foreach ($chunks as $index => $chunk) {
            $contextParts[] = "[Источник " . ($index + 1) . "]: {$chunk['content']}";
        }

        return implode("\n\n", $contextParts);
    }

    /**
     * Сформировать промпт для LLM
     */
    private function buildPrompt(string $question, string $context): string
    {
        return <<<PROMPT
Ты — умный помощник службы поддержки компании. Твоя задача — отвечать на вопросы клиентов ТОЛЬКО на основе предоставленного контекста из базы знаний.

ПРАВИЛА:
1. Используй только информацию из раздела "Контекст" ниже.
2. Если ответа нет в контексте, честно скажи: "Извините, у меня нет информации об этом в базе знаний."
3. Не выдумывай факты и не используй свои знания.
4. Отвечай вежливо, профессионально и понятно.
5. В конце ответа можешь указать номера источников в формате [1], [2] и т.д.

КОНТЕКСТ ИЗ БАЗЫ ЗНАНИЙ:
{$context}

ВОПРОС ПОЛЬЗОВАТЕЛЯ:
{$question}

ОТВЕТ:
PROMPT;
    }

    /**
     * Получить историю чата
     */
    private function getChatHistory(string $sessionId): array
    {
        $session = AiChatSession::where('session_id', $sessionId)->first();
        
        if (!$session) {
            return [];
        }

        $messages = $session->messages()
            ->where('role', '!=', 'system')
            ->orderBy('created_at', 'asc')
            ->limit(10) // Последние 10 сообщений
            ->get();

        return $messages->map(function($msg) {
            return [
                'role' => $msg->role,
                'content' => $msg->content,
            ];
        })->toArray();
    }

    /**
     * Вызов LLM (OpenAI GPT или совместимый)
     */
    private function callLLM(string $prompt, array $history, string $currentMessage): array
    {
        if (empty($this->apiKey)) {
            Log::warning('OpenAI API key не настроен. Возвращается демо-ответ.');
            return $this->getMockResponse($prompt);
        }

        try {
            $messages = array_merge(
                [['role' => 'system', 'content' => $prompt]],
                $history,
                [['role' => 'user', 'content' => $currentMessage]]
            );

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->post($this->chatApiUrl, [
                'model' => 'gpt-3.5-turbo', // Или gpt-4
                'messages' => $messages,
                'max_tokens' => 500,
                'temperature' => 0.3, // Низкая температура для более точных ответов
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'content' => $data['choices'][0]['message']['content'],
                    'tokens' => $data['usage']['total_tokens'] ?? 0,
                    'confidence' => 0.9, // В реальном проекте можно анализировать uncertainty модели
                ];
            }

            Log::error('Ошибка LLM API: ' . $response->body());
            return $this->getMockResponse($prompt);

        } catch (\Exception $e) {
            Log::error('Исключение при вызове LLM: ' . $e->getMessage());
            return $this->getMockResponse($prompt);
        }
    }

    /**
     * Демо-ответ если API недоступен
     */
    private function getMockResponse(string $prompt): array
    {
        return [
            'content' => "Спасибо за ваш вопрос! На основе нашей базы знаний: информация найдена. Пожалуйста, ознакомьтесь с источниками ниже для получения подробной информации.",
            'tokens' => 50,
            'confidence' => 0.5,
        ];
    }

    /**
     * Сохранить сообщение в БД
     */
    private function saveMessage(
        string $sessionId, 
        string $role, 
        string $content, 
        array $sources = [], 
        int $tokens = 0, 
        float $confidence = 0.0
    ): void {
        // Получаем или создаем сессию
        $session = AiChatSession::firstOrCreate(
            ['session_id' => $sessionId],
            ['user_ip' => request()->ip()]
        );

        $sourceData = array_map(function($chunk) {
            return ['chunk_id' => $chunk['chunk']->id];
        }, $sources);

        AiChatMessage::create([
            'session_id' => $session->id,
            'role' => $role,
            'content' => $content,
            'sources' => $sourceData,
            'tokens_used' => $tokens,
            'confidence_score' => $confidence,
        ]);
    }
}
