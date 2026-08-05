<?php

namespace App\Ecommerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $table = 'ecommerce_orders';

    protected $fillable = [
        'user_id',
        'session_id',
        'status',
        'customer_email',
        'customer_name',
        'shipping_address_json',
        'billing_address_json',
        'subtotal',
        'tax',
        'discount',
        'total',
        'payment_status',
        'payment_method',
        'payment_transaction_id',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'shipping_address_json' => 'array',
        'billing_address_json' => 'array',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
