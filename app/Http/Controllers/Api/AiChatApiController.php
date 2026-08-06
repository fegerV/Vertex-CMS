<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Ai\RagChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\UUID;

class AiChatApiController extends Controller
{
    private RagChatService $ragService;

    public function __construct(RagChatService $ragService)
    {
        $this->ragService = $ragService;
    }

    /**
     * Обработать сообщение пользователя и вернуть ответ AI
     * POST /api/ai/chat
     */
    public function chat(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'session_id' => 'nullable|string|max:255',
        ]);

        // Генерируем или используем существующий session_id
        $sessionId = $validated['session_id'] ?? (string) UUID::uuid4();

        try {
            $response = $this->ragService->processQuery($sessionId, $validated['message']);

            return response()->json([
                'success' => true,
                'answer' => $response['answer'],
                'sources' => $response['sources'],
                'confidence' => $response['confidence'],
                'session_id' => $sessionId,
            ]);

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
     * Получить историю чата
     * GET /api/ai/chat/history/{session_id}
     */
    public function history($sessionId)
    {
        $session = \App\Models\AiChatSession::where('session_id', $sessionId)
            ->with(['messages' => function($q) {
                $q->orderBy('created_at', 'asc');
            }])
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
    public function newSession()
    {
        $sessionId = (string) UUID::uuid4();
        
        \App\Models\AiChatSession::create([
            'session_id' => $sessionId,
            'user_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'session_id' => $sessionId,
        ]);
    }
}
