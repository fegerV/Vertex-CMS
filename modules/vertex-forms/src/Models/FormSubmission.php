<?php

namespace Vertex\Forms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class FormSubmission extends Model
{
    protected $fillable = [
        'form_id',
        'submission_id',
        'ip_address',
        'user_agent',
        'user_id',
        'status',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
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
        return $this->hasMany(FormSubmissionValue::class);
    }

    public function getValue(string $fieldName): ?string
    {
        $value = $this->values()
            ->whereHas('field', fn($q) => $q->where('name', $fieldName))
            ->first();

        return $value?->value;
    }
}
