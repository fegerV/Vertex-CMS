<?php

namespace Vertex\Forms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormField extends Model
{
    protected $fillable = [
        'form_id',
        'name',
        'label',
        'type',
        'sort_order',
        'options',
        'required',
        'visible',
        'default_value',
        'placeholder',
        'help_text',
        'css_class',
    ];

    protected $casts = [
        'options' => 'array',
        'required' => 'boolean',
        'visible' => 'boolean',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function submissionsValues(): HasMany
    {
        return $this->hasMany(FormSubmissionValue::class, 'field_id');
    }
}
