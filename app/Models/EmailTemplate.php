<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailTemplate extends Model
{
    protected $fillable = [
        'key',
        'name',
        'subject',
        'body_html',
        'body_text',
        'default_vars',
        'category',
        'is_active',
        'is_system',
    ];

    protected $casts = [
        'default_vars' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(EmailLog::class, 'template_key', 'key');
    }
}
