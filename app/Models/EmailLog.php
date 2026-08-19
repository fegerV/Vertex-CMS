<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'template_key',
        'recipient_email',
        'recipient_name',
        'subject',
        'body_text',
        'headers',
        'attachments',
        'template_vars',
        'status',
        'error_message',
        'message_id',
        'retry_count',
        'sent_at',
        'failed_at',
    ];

    protected $casts = [
        'headers' => 'array',
        'attachments' => 'array',
        'template_vars' => 'array',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];
}
