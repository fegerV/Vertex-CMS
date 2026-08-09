<?php

namespace Vertex\Forms\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormSubmission extends Model
{
    protected $fillable = [
        'form_id',
        'submission_id',
        'idempotency_key',
        'resume_token_hash',
        'resume_expires_at',
        'ip_address',
        'user_agent',
        'user_id',
        'status',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'resume_expires_at' => 'datetime',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(FormSubmissionValue::class, 'submission_id');
    }

    public function getValue(string $fieldName): mixed
    {
        $value = $this->values()
            ->whereHas('field', fn ($q) => $q->where('name', $fieldName))
            ->first();

        return $value?->value;
    }
}
