<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageRevision extends Model
{
    public $timestamps = false;

    protected $fillable = ['page_id', 'user_id', 'title', 'content_json', 'seo_json', 'created_at'];

    protected $casts = [
        'content_json' => 'array',
        'seo_json' => 'array',
        'created_at' => 'datetime',
    ];
}

