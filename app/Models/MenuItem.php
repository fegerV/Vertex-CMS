<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'url',
        'target',
        'type',
        'entity_type',
        'entity_id',
        'sort_order',
        'settings_json',
    ];

    protected $casts = [
        'settings_json' => 'array',
    ];
}

