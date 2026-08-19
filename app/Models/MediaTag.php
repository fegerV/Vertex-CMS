<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaTag extends Model
{
    protected $table = 'media_tags';

    protected $fillable = [
        'name',
        'slug',
    ];

    public function media(): BelongsTo
    {
        return $this->belongsToMany(Media::class, 'media_taggable', 'media_tag_id', 'media_id');
    }
}
