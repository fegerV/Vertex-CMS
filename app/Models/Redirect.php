<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $fillable = ['from_url', 'to_url', 'status_code', 'enabled', 'hits'];

    protected $casts = [
        'enabled' => 'boolean',
    ];
}

