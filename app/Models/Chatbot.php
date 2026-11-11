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
        'llm_provider',
        'llm_model',
        'temperature',
        'max_tokens',
        'system_prompt',
        'config',
        'rate_limits',
        'ui_config',
        'webhook_url',
        'webhook_events',
        'page_ids',
        'allowed_roles',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'temperature' => 'float',
        'max_tokens' => 'integer',
        'config' => 'array',
        'rate_limits' => 'array',
        'ui_config' => 'array',
        'webhook_events' => 'array',
        'page_ids' => 'array',
        'allowed_roles' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
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
        
        if (($this->config['use_page_context'] ?? false) && ($pageTitle || $pageExcerpt)) {
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
        return (bool) ($this->config['enable_web_search'] ?? false);
    }

    /**
     * Проверить доступность загрузки файлов
     */
    public function canUploadFiles(): bool
    {
        return (bool) ($this->config['enable_file_upload'] ?? false);
    }

    /**
     * Получить максимальный размер файла в байтах
     */
    public function getMaxFileSizeBytes(): int
    {
        return (int) ($this->config['max_file_size_mb'] ?? 0) * 1024 * 1024;
    }

    /**
     * Проверить, должен ли бот вызываться на текущей странице
     */
    public function shouldAppearOnPage(string $uri): bool
    {
        $patterns = $this->config['page_restrictions'] ?? [];

        if (empty($patterns)) {
            return true; // Нет ограничений, показывать везде
        }

        foreach ($patterns as $pattern) {
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
