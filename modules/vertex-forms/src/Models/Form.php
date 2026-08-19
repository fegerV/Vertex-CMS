<?php

namespace Vertex\Forms\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'settings',
        'sort_order',
        'is_active',
        'require_login',
        'entry_limit',
        'daily_limit',
        'available_from',
        'available_to',
        'created_by',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'require_login' => 'boolean',
        'available_from' => 'datetime',
        'available_to' => 'datetime',
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(FormVersion::class)->orderBy('version_number', 'desc');
    }
}
