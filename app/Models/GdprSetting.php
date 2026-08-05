<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GdprSetting extends Model
{
    protected $fillable = [
        'enabled',
        'banner_title',
        'banner_message',
        'accept_button_text',
        'decline_button_text',
        'policy_link',
        'cookie_duration_days',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'cookie_duration_days' => 'integer',
    ];

    public static function getActive(): self
    {
        return static::firstOrCreate([], [
            'enabled' => true,
            'banner_title' => 'Мы используем файлы cookie',
            'banner_message' => 'Этот сайт использует файлы cookie для улучшения пользовательского опыта. Продолжая использовать сайт, вы соглашаетесь с нашей политикой использования файлов cookie.',
            'accept_button_text' => 'Принять',
            'decline_button_text' => 'Отклонить',
            'cookie_duration_days' => 365,
        ]);
    }
}
