<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'disk',
        'folder_id',
        'filename',
        'original_filename',
        'mime_type',
        'extension',
        'size',
        'width',
        'height',
        'path',
        'url',
        'alt',
        'title',
        'caption',
        'metadata_json',
        'created_by',
    ];

    protected $casts = [
        'metadata_json' => 'array',
    ];
}

