<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'ip',
        'user_agent',
        'metadata_json',
        'created_at',
    ];

    protected $casts = [
        'metadata_json' => 'array',
        'created_at' => 'datetime',
    ];
}

