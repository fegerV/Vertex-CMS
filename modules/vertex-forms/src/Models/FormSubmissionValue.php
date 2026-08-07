<?php

namespace Vertex\Forms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmissionValue extends Model
{
    protected $fillable = [
        'submission_id',
        'field_id',
        'value',
    ];

    protected $casts = [
        'value' => 'array', // auto json encode/decode for arrays (checkboxes, files)
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(FormSubmission::class, 'submission_id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(FormField::class, 'field_id');
    }
}
