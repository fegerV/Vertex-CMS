<?php

namespace App\Models\Analytics;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dashboard extends Model
{
    protected $fillable = [
        'name',
        'description',
        'layout',
        'widgets',
        'is_public',
        'user_id',
    ];

    protected $casts = [
        'widgets' => 'array',
        'layout' => 'array',
        'is_public' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function funnelSteps(): HasMany
    {
        return $this->hasMany(FunnelStep::class);
    }
}
