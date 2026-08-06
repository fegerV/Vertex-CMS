<?php

namespace App\Http\Controllers\Admin\Seo;

use App\Http\Controllers\Controller;
use App\Models\AiKbCategory;
use App\Models\AiKbDocument;
use App\Models\AiKbChunk;
use App\Models\AiChatSession;
use App\Models\AiChatMessage;
use App\Services\Ai\DocumentProcessorService;
use App\Services\Ai\EmbeddingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AiKnowledgeBaseController extends Controller
{
    private DocumentProcessorService $documentProcessor;
    private EmbeddingService $embeddingService;

    public function __construct(
        DocumentProcessorService $documentProcessor,
        EmbeddingService $embeddingService
    ) {
        $this->documentProcessor = $documentProcessor;
        $this->embeddingService = $embeddingService;
    }

    /**
     * Главная страница базы знаний
     */
    public function index()
    {
        $stats = [
            'categories' => AiKbCategory::count(),
            'documents' => AiKbDocument::count(),
            'chunks' => AiKbChunk::count(),
            'processed' => AiKbDocument::where('is_processed', true)->count(),
            'chat_sessions' => AiChatSession::count(),
            'total_messages' => AiChatMessage::count(),
        ];

        $documents = AiKbDocument::with('category')
            ->latest()
            ->paginate(15);

        return view('admin.seo.ai-kb.index', compact('stats', 'documents'));
    }

    /**
     * Страница категорий
     */
    public function categories()
    {
        $categories = AiKbCategory::with('children', 'parent')->get();
        return view('admin.seo.ai-kb.categories', compact('categories'));
    }

    /**
     * Создать категорию
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:ai_kb_categories,id',
        ]);

        AiKbCategory::create($validated);

        return redirect()->back()->with('success', 'Категория создана');
    }

    /**
     * Страница создания/редактирования документа
     */
    public function editDocument($id = null)
    {
        if ($id) {
            $document = AiKbDocument::with('chunks')->findOrFail($id);
            $mode = 'edit';
        } else {
            $document = new AiKbDocument();
            $mode = 'create';
        }

        $categories = AiKbCategory::where('is_active', true)->get();

        return view('admin.seo.ai-kb.document-form', compact('document', 'categories', 'mode'));
    }

    /**
     * Сохранить документ
     */
    public function saveDocument(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|exists:ai_kb_categories,id',
            'content' => 'required|string',
            'file_path' => 'nullable|string',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('ai-kb-documents', 'public');
            
            // Извлечь текст из файла
            try {
                $extractedText = $this->documentProcessor->extractTextFromFile(storage_path('app/public/' . $path));
                $validated['content'] = $extractedText;
                $validated['file_path'] = $path;
                $validated['mime_type'] = $file->getMimeType();
            } catch (\Exception $e) {
                Log::error('Ошибка извлечения текста: ' . $e->getMessage());
                return redirect()->back()->withErrors(['file' => 'Не удалось извлечь текст из файла']);
            }
        }

        if ($request->has('id')) {
            $document = AiKbDocument::findOrFail($request->id);
            $document->update($validated);
        } else {
            $document = AiKbDocument::create($validated);
        }

        // Обработать документ (разбить на чанки)
        $this->documentProcessor->processDocument($document);

        // Сгенерировать эмбеддинги
        $this->embeddingService->processAllChunks();

        return redirect()->route('admin.seo.ai-kb.index')->with('success', 'Документ сохранен и обработан');
    }

    /**
     * Удалить документ
     */
    public function deleteDocument($id)
    {
        $document = AiKbDocument::findOrFail($id);
        $document->delete();

        return redirect()->back()->with('success', 'Документ удален');
    }

    /**
     * Пересоздать чанки для документа
     */
    public function reprocessDocument($id)
    {
        $document = AiKbDocument::findOrFail($id);
        $count = $this->documentProcessor->processDocument($document);
        $this->embeddingService->processAllChunks();

        return redirect()->back()->with('success', "Документ переобработан. Создано чанков: {$count}");
    }

    /**
     * История чатов
     */
    public function chatHistory()
    {
        $sessions = AiChatSession::with(['messages' => function($q) {
            $q->latest()->limit(1);
        }])
        ->latest()
        ->paginate(20);

        return view('admin.seo.ai-kb.chat-history', compact('sessions'));
    }

    /**
     * Просмотр конкретного чата
     */
    public function viewChat($id)
    {
        $session = AiChatSession::with(['messages' => function($q) {
            $q->orderBy('created_at', 'asc');
        }])->findOrFail($id);

        return view('admin.seo.ai-kb.chat-view', compact('session'));
    }

    /**
     * Настройки RAG
     */
    public function settings()
    {
        $currentSettings = [
            'openai_api_key' => env('OPENAI_API_KEY', ''),
            'supabase_url' => env('SUPABASE_URL', ''),
            'supabase_key' => env('SUPABASE_KEY', ''),
            'mask_openai_key' => $this->maskApiKey(env('OPENAI_API_KEY', '')),
        ];
        
        return view('admin.seo.ai-kb.settings', compact('currentSettings'));
    }

    /**
     * Маскировка API ключа для отображения
     */
    private function maskApiKey(?string $key): string
    {
        if (empty($key)) {
            return 'Не настроен';
        }
        
        $length = strlen($key);
        if ($length <= 8) {
            return str_repeat('*', $length);
        }
        
        return substr($key, 0, 4) . str_repeat('*', $length - 8) . substr($key, -4);
    }

    /**
     * Сохранить настройки
     */
    public function saveSettings(Request $request)
    {
        $validated = $request->validate([
            'openai_api_key' => 'nullable|string',
            'supabase_url' => 'nullable|url',
            'supabase_key' => 'nullable|string',
            'embedding_model' => 'nullable|string',
            'chat_model' => 'nullable|string',
            'temperature' => 'nullable|numeric|min:0|max:1',
            'widget_title' => 'nullable|string|max:255',
            'widget_welcome' => 'nullable|string',
            'widget_color' => 'nullable|string',
            'widget_position' => 'nullable|in:left,right',
            'widget_enabled' => 'boolean',
            'max_chunks' => 'nullable|integer|min:1|max:20',
            'min_similarity' => 'nullable|integer|min:0|max:100',
            'chunk_size' => 'nullable|integer|min:100|max:2000',
        ]);

        // Сохранение настроек в .env файл или базу данных
        $this->saveEnvSetting('OPENAI_API_KEY', $validated['openai_api_key'] ?? '');
        $this->saveEnvSetting('SUPABASE_URL', $validated['supabase_url'] ?? '');
        $this->saveEnvSetting('SUPABASE_KEY', $validated['supabase_key'] ?? '');
        
        // Очистка кэша конфигурации
        \Artisan::call('config:clear');
        
        return redirect()->back()->with('success', 'Настройки сохранены. Необходимо перезагрузить страницу для применения изменений.');
    }

    /**
     * Сохранение переменной окружения
     */
    private function saveEnvSetting(string $key, string $value): void
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);
        
        // Экранирование специальных символов в значении
        $escapedValue = str_replace(['\\'], ['\\\\'], $value);
        
        if (preg_match("/^{$key}=.*/m", $envContent, $matches)) {
            $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$escapedValue}", $envContent);
        } else {
            $envContent .= "\n{$key}={$escapedValue}";
        }
        
        file_put_contents($envPath, $envContent);
    }
}
