<?php

namespace Vertex\Forms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormAnalytic extends Model
{
    protected $fillable = [
        'form_id',
        'date',
        'views',
        'unique_visitors',
        'submissions',
        'avg_time_seconds',
        'top_fields',
    ];

    protected $casts = [
        'date' => 'date',
        'top_fields' => 'array',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
