<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'version',
        'type',
        'status',
        'installed_at',
        'enabled_at',
        'settings_json',
    ];

    protected $casts = [
        'installed_at' => 'datetime',
        'enabled_at' => 'datetime',
        'settings_json' => 'array',
    ];
}

