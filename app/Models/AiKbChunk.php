<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiKbChunk extends Model
{
    use HasFactory;

    protected $table = 'ai_kb_chunks';

    protected $fillable = [
        'document_id',
        'content',
        'chunk_order',
        'embedding_vector',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function document()
    {
        return $this->belongsTo(AiKbDocument::class);
    }

    /**
     * Сериализация вектора для хранения в БД
     */
    public function setEmbeddingVectorAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['embedding_vector'] = json_encode($value);
        } else {
            $this->attributes['embedding_vector'] = $value;
        }
    }

    /**
     * Десериализация вектора
     */
    public function getEmbeddingVectorAttribute($value)
    {
        return json_decode($value, true);
    }
}
