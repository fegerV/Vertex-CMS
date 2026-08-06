<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiKbDocument extends Model
{
    use HasFactory;

    protected $table = 'ai_kb_documents';

    protected $fillable = [
        'title',
        'category_id',
        'file_path',
        'content',
        'source_type',
        'mime_type',
        'word_count',
        'is_processed',
    ];

    protected $casts = [
        'is_processed' => 'boolean',
        'metadata' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(AiKbCategory::class);
    }

    public function chunks()
    {
        return $this->hasMany(AiKbChunk::class);
    }

    public function getWordCountAttribute($value)
    {
        if ($value === 0 && $this->content) {
            return str_word_count($this->content);
        }
        return $value;
    }
}
