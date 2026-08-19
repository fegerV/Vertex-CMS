<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SeoRedirect extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_url',
        'to_url',
        'type',
        'is_active',
        'note',
    ];

    protected $casts = [
        'type' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Связь с логами
     */
    public function logs()
    {
        return $this->hasMany(RedirectLog::class, 'url', 'from_url');
    }

    /**
     *_scopeActive - только активные редиректы
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Проверка совпадения URL
     */
    public static function findRedirect($url)
    {
        return self::active()
            ->where('from_url', $url)
            ->first();
    }
}
