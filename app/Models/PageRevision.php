<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageRevision extends Model
{
    public $timestamps = false;

    protected $fillable = ['page_id', 'user_id', 'title', 'content_json', 'custom_fields_json', 'seo_json', 'created_at', 'action'];

    protected $casts = [
        'content_json' => 'array',
        'custom_fields_json' => 'array',
        'seo_json' => 'array',
        'created_at' => 'datetime',
        'action' => 'string',
    ];
}
