<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailQueue extends Model
{
    protected $fillable = [
        'template_key',
        'recipients',
        'variables',
        'subject_override',
        'body_override',
        'priority',
        'scheduled_for',
        'retry_count',
        'last_error',
        'status',
        'processed_at',
    ];

    protected $casts = [
        'recipients' => 'array',
        'variables' => 'array',
        'priority' => 'integer',
        'retry_count' => 'integer',
        'scheduled_for' => 'datetime',
        'processed_at' => 'datetime',
    ];
}
