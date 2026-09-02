<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AI\RagChatService;
use App\Services\AI\ModularChatbotService;
use App\Models\Chatbot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\UUID;

class AiChatApiController extends Controller
{
    private RagChatService $ragService;
    private ModularChatbotService $modularService;

    public function __construct(
        RagChatService $ragService,
        ModularChatbotService $modularService
    ) {
        $this->ragService = $ragService;
        $this->modularService = $modularService;
    }

    /**
     * Обработать сообщение пользователя и вернуть ответ AI
     * POST /api/ai/chat
     * Поддерживает как legacy режим, так и модульных ботов
     */
    public function chat(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'session_id' => 'nullable|string|max:255',
            'chatbot_id' => 'nullable|integer|exists:chatbots,id',
            'chatbot_slug' => 'nullable|string|max:100',
            'page_context' => 'nullable|array',
            'page_context.uri' => 'nullable|string|max:500',
            'page_context.title' => 'nullable|string|max:255',
            'page_context.excerpt' => 'nullable|string|max:1000',
            'user_context' => 'nullable|array',
        ]);

        // Генерируем или используем существующий session_id
        $sessionId = $validated['session_id'] ?? (string) UUID::uuid4();

        try {
            // Если указан chatbot_id или chatbot_slug - используем модульный сервис
            if (!empty($validated['chatbot_id']) || !empty($validated['chatbot_slug'])) {
                return $this->handleModularChat($sessionId, $validated);
            }

            // Legacy режим - используем старый RAG сервис
            return $this->handleLegacyChat($sessionId, $validated);

        } catch (\Exception $e) {
            \Log::error('Ошибка AI чата: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'answer' => 'Произошла ошибка при обработке вашего запроса. Пожалуйста, попробуйте позже.',
                'sources' => [],
                'confidence' => 0.0,
                'session_id' => $sessionId,
            ], 500);
        }
    }

    /**
     * Обработка через модульный сервис чат-ботов
     */
    private function handleModularChat(string $sessionId, array $validated)
    {
        // Определить бота
        $chatbot = null;
        
        if (!empty($validated['chatbot_id'])) {
            $chatbot = Chatbot::find($validated['chatbot_id']);
        } elseif (!empty($validated['chatbot_slug'])) {
            $chatbot = $this->modularService->getChatbotBySlug($validated['chatbot_slug']);
        }

        if (!$chatbot || !$chatbot->isActive()) {
            return response()->json([
                'success' => false,
                'answer' => 'Чат-бот временно недоступен.',
                'sources' => [],
                'confidence' => 0.0,
                'session_id' => $sessionId,
                'error' => 'BOT_NOT_FOUND',
            ], 404);
        }

        // Получить контекст страницы из запроса или middleware
        $pageContext = $validated['page_context'] ?? [];
        
        // Если контекст не передан, попробовать получить из middleware
        if (empty($pageContext) && request()->attributes->has('page_context')) {
            $pageContext = request()->attributes->get('page_context');
        }

        // Получить контекст пользователя
        $userContext = $validated['user_context'] ?? [
            'user_id' => auth()->id(),
        ];

        // Обработать через модульный сервис
        $response = $this->modularService->processMessage(
            $sessionId,
            $validated['message'],
            $chatbot->id,
            $pageContext,
            $userContext
        );

        return response()->json([
            'success' => $response['success'],
            'answer' => $response['answer'],
            'sources' => $response['sources'],
            'confidence' => $response['confidence'],
            'session_id' => $sessionId,
            'chatbot_id' => $chatbot->id,
            'chatbot_name' => $chatbot->name,
            'tokens_used' => $response['tokens_used'] ?? 0,
            'rule_triggered' => $response['rule_triggered'] ?? false,
            'form_schema' => $response['form_schema'] ?? null,
            'webhook_data' => $response['webhook_data'] ?? null,
        ]);
    }

    /**
     * Обработка через legacy RAG сервис
     */
    private function handleLegacyChat(string $sessionId, array $validated)
    {
        $response = $this->ragService->processQuery($sessionId, $validated['message']);

        return response()->json([
            'success' => true,
            'answer' => $response['answer'],
            'sources' => $response['sources'],
            'confidence' => $response['confidence'],
            'session_id' => $sessionId,
        ]);
    }

    /**
     * Получить историю чата
     * GET /api/ai/chat/history/{session_id}
     */
    public function history($sessionId)
    {
        $session = \App\Models\AiChatSession::where('session_id', $sessionId)
            ->with(['messages' => function($q) {
                $q->orderBy('created_at', 'asc');
            }, 'chatbot'])
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Сессия не найдена',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'session' => [
                'id' => $session->id,
                'session_id' => $session->session_id,
                'chatbot_id' => $session->chatbot_id,
                'chatbot_name' => $session->chatbot?->name,
                'page_uri' => $session->page_uri,
                'page_title' => $session->page_title,
                'created_at' => $session->created_at,
            ],
            'messages' => $session->messages->map(function($msg) {
                return [
                    'role' => $msg->role,
                    'content' => $msg->content,
                    'sources' => $msg->sources,
                    'created_at' => $msg->created_at,
                ];
            }),
        ]);
    }

    /**
     * Создать новую сессию чата
     * POST /api/ai/chat/session
     */
    public function newSession(Request $request)
    {
        $sessionId = (string) UUID::uuid4();
        
        \App\Models\AiChatSession::create([
            'session_id' => $sessionId,
            'chatbot_id' => $request->input('chatbot_id'),
            'user_id' => auth()->id(),
            'user_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'page_uri' => $request->input('page_uri'),
            'page_title' => $request->input('page_title'),
        ]);

        return response()->json([
            'success' => true,
            'session_id' => $sessionId,
        ]);
    }

    /**
     * Получить список доступных ботов для страницы
     * GET /api/ai/chat/bots
     */
    public function availableBots(Request $request)
    {
        $uri = $request->input('uri', '/');
        $bots = $this->modularService->getActiveBotsForPage($uri);

        return response()->json([
            'success' => true,
            'bots' => $bots,
        ]);
    }
}
