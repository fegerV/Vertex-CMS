<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Taxonomy extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'entity_type',
        'hierarchical',
        'settings_json',
    ];

    protected $casts = [
        'hierarchical' => 'boolean',
        'settings_json' => 'array',
    ];

    public function terms(): HasMany
    {
        return $this->hasMany(Term::class)->orderBy('sort_order')->orderBy('name');
    }
}
