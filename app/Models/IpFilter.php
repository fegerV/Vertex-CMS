<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpFilter extends Model
{
    protected $fillable = [
        'ip_address',
        'type',
        'reason',
        'active',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return $this->active && !$this->isExpired();
    }

    public function scopeBlacklist($query)
    {
        return $query->where('type', 'blacklist');
    }

    public function scopeWhitelist($query)
    {
        return $query->where('type', 'whitelist');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }
}
