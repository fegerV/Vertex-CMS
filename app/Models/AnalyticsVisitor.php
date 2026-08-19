<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsVisitor extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'aggregate_id',
        'visitor_hash',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function aggregate(): BelongsTo
    {
        return $this->belongsTo(AnalyticsAggregate::class, 'aggregate_id');
    }
}
