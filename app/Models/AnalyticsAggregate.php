<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalyticsAggregate extends Model
{
    protected $fillable = [
        'aggregate_key',
        'visit_date',
        'kind',
        'page_id',
        'term_id',
        'path',
        'title',
        'visits',
        'visitors',
        'last_visited_at',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'last_visited_at' => 'datetime',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function uniqueVisitors(): HasMany
    {
        return $this->hasMany(AnalyticsVisitor::class, 'aggregate_id');
    }
}
