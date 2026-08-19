<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model
{
    protected $fillable = [
        'webhook_id',
        'event',
        'payload',
        'status_code',
        'response',
        'success',
        'attempt',
        'delivered_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'status_code' => 'integer',
        'success' => 'boolean',
        'attempt' => 'integer',
        'delivered_at' => 'datetime',
    ];

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'success' => true,
            'delivered_at' => now(),
        ]);
    }

    public function markAsFailed(int $statusCode = null, string $response = null): void
    {
        $this->update([
            'success' => false,
            'status_code' => $statusCode,
            'response' => $response,
        ]);
    }
}
