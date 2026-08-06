<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiChatMessage extends Model
{
    use HasFactory;

    protected $table = 'ai_chat_messages';

    protected $fillable = [
        'session_id',
        'role',
        'content',
        'sources',
        'tokens_used',
        'confidence_score',
    ];

    protected $casts = [
        'sources' => 'array',
        'confidence_score' => 'float',
    ];

    public function session()
    {
        return $this->belongsTo(AiChatSession::class);
    }

    /**
     * Получить источники (чанки) которые использовались для ответа
     */
    public function getSourceChunksAttribute()
    {
        if (!$this->sources) {
            return [];
        }
        
        $chunkIds = array_column($this->sources, 'chunk_id');
        return \App\Models\AiKbChunk::whereIn('id', $chunkIds)->get();
    }
}
