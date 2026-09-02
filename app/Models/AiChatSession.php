<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatSession extends Model
{
    use HasFactory;

    protected $table = 'ai_chat_sessions';

    protected $fillable = [
        'session_id',
        'chatbot_id',
        'user_id',
        'user_ip',
        'user_agent',
        'page_uri',
        'page_title',
        'page_excerpt',
        'page_metadata',
        'is_closed',
    ];

    protected $casts = [
        'is_closed' => 'boolean',
        'page_metadata' => 'array',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(AiChatMessage::class);
    }

    public function chatbot(): BelongsTo
    {
        return $this->belongsTo(Chatbot::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getLatestMessageAttribute()
    {
        return $this->messages()->latest()->first();
    }

    /**
     * Получить контекст страницы для использования в промпте
     */
    public function getPageContext(): array
    {
        return [
            'uri' => $this->page_uri,
            'title' => $this->page_title,
            'excerpt' => $this->page_excerpt,
            'metadata' => $this->page_metadata ?? [],
        ];
    }
}
