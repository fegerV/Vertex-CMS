<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RedirectLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'referer',
        'status_code',
        'user_agent',
        'ip_address',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Связь с редиректом
     */
    public function redirect()
    {
        return $this->hasOne(SeoRedirect::class, 'from_url', 'url');
    }

    /**
     * Scope для 404 ошибок
     */
    public function scopeNotFound($query)
    {
        return $query->where('status_code', 404);
    }

    /**
     * Scope за период
     */
    public function scopeLastDays($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
