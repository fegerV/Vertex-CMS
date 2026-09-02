<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chatbot extends Model
{
    use HasFactory;

    protected $table = 'chatbots';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'provider',
        'model',
        'provider_config',
        'system_prompt',
        'use_page_context',
        'use_knowledge_base',
        'knowledge_bases',
        'max_context_chunks',
        'max_tokens_per_message',
        'rate_limit_per_minute',
        'rate_limit_per_hour',
        'max_session_messages',
        'ui_config',
        'is_active',
        'page_restrictions',
        'role_restrictions',
        'enable_web_search',
        'enable_image_generation',
        'enable_voice_input',
        'enable_file_upload',
        'max_file_size_mb',
        'webhook_url',
        'webhook_triggers',
    ];

    protected $casts = [
        'provider_config' => 'array',
        'knowledge_bases' => 'array',
        'ui_config' => 'array',
        'page_restrictions' => 'array',
        'role_restrictions' => 'array',
        'webhook_triggers' => 'array',
        'use_page_context' => 'boolean',
        'use_knowledge_base' => 'boolean',
        'is_active' => 'boolean',
        'enable_web_search' => 'boolean',
        'enable_image_generation' => 'boolean',
        'enable_voice_input' => 'boolean',
        'enable_file_upload' => 'boolean',
    ];

    /**
     * Получить правила для этого бота
     */
    public function rules(): HasMany
    {
        return $this->hasMany(ChatbotRule::class)->orderBy('priority', 'desc');
    }

    /**
     * Получить сессии чата
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(AiChatSession::class);
    }

    /**
     * Проверить, активен ли бот
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Получить системный промпт с контекстом страницы
     */
    public function getSystemPromptWithPageContext(?string $pageTitle = null, ?string $pageExcerpt = null): string
    {
        $basePrompt = $this->system_prompt ?? 'Ты полезный ассистент. Отвечай кратко и по делу.';
        
        if ($this->use_page_context && ($pageTitle || $pageExcerpt)) {
            $contextParts = [];
            
            if ($pageTitle) {
                $contextParts[] = "Текущая страница: {$pageTitle}";
            }
            
            if ($pageExcerpt) {
                $contextParts[] = "Описание страницы: {$pageExcerpt}";
            }
            
            $contextBlock = implode("\n", $contextParts);
            
            $basePrompt .= "\n\n[КОНТЕКСТ СТРАНИЦЫ]\n{$contextBlock}\n[/КОНТЕКСТ СТРАНИЦЫ]\n\n" .
                           "Используй этот контекст для более релевантных ответов. Если вопрос пользователя связан с содержимым страницы, отвечай на его основе.";
        }
        
        return $basePrompt;
    }

    /**
     * Проверить доступность веб-поиска
     */
    public function canWebSearch(): bool
    {
        return $this->enable_web_search;
    }

    /**
     * Проверить доступность загрузки файлов
     */
    public function canUploadFiles(): bool
    {
        return $this->enable_file_upload;
    }

    /**
     * Получить максимальный размер файла в байтах
     */
    public function getMaxFileSizeBytes(): int
    {
        return $this->max_file_size_mb * 1024 * 1024;
    }

    /**
     * Проверить, должен ли бот вызываться на текущей странице
     */
    public function shouldAppearOnPage(string $uri): bool
    {
        if (empty($this->page_restrictions)) {
            return true; // Нет ограничений, показывать везде
        }

        foreach ($this->page_restrictions as $pattern) {
            if ($this->matchesPattern($uri, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Проверить соответствие URI паттерну
     */
    private function matchesPattern(string $uri, string $pattern): bool
    {
        // Поддержка wildcard паттернов
        if (str_contains($pattern, '*')) {
            $regex = str_replace(['*', '/'], ['.*', '\/'], $pattern);
            return preg_match('/^' . $regex . '$/i', $uri) === 1;
        }

        // Точное совпадение
        return rtrim($uri, '/') === rtrim($pattern, '/');
    }
}
