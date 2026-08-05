<?php

namespace App\Models\Analytics;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FunnelStep extends Model
{
    protected $fillable = [
        'dashboard_id',
        'name',
        'step_order',
        'conversion_rate',
        'drop_off_rate',
        'event_name',
        'filter_conditions',
    ];

    protected $casts = [
        'step_order' => 'integer',
        'conversion_rate' => 'float',
        'drop_off_rate' => 'float',
        'filter_conditions' => 'array',
    ];

    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class);
    }
}
