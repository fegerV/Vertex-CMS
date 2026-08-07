<?php

namespace Vertex\Forms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormWebhookDelivery extends Model
{
    protected $fillable = [
        'form_id', 'submission_id', 'name', 'url', 'secret', 'headers', 'payload',
        'status', 'attempts', 'status_code', 'response', 'delivered_at', 'failed_at',
    ];

    protected $casts = [
        'secret' => 'encrypted',
        'headers' => 'array',
        'payload' => 'array',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(FormSubmission::class);
    }
}
