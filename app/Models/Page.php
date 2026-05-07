<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'title',
        'slug',
        'uri',
        'status',
        'template',
        'content_json',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'content_json' => 'array',
        'published_at' => 'datetime',
    ];
}

