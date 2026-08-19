<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiChatSession extends Model
{
    use HasFactory;

    protected $table = 'ai_chat_sessions';

    protected $fillable = [
        'session_id',
        'user_ip',
        'user_agent',
        'is_closed',
    ];

    protected $casts = [
        'is_closed' => 'boolean',
    ];

    public function messages()
    {
        return $this->hasMany(AiChatMessage::class);
    }

    public function getLatestMessageAttribute()
    {
        return $this->messages()->latest()->first();
    }
}
