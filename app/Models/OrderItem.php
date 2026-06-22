<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'consignor_id',
        'quantity',
        'start_date',
        'end_date',
        'days',
        'price_per_day',
        'subtotal',
        'commission_rate',
        'commission_fee',
        'net_amount',
        'dp_amount',
        'dp_percentage',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'price_per_day' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'commission_fee' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'dp_amount' => 'decimal:2',
            'dp_percentage' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function consignor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consignor_id');
    }

    public function transaction(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Transaction::class);
    }

    public static function calculateSubtotal($pricePerDay, $days, $quantity = 1): float
    {
        return $pricePerDay * $days * $quantity;
    }

    public static function calculateCommission($subtotal, $commissionRate): float
    {
        return $subtotal * ($commissionRate / 100);
    }

    public static function calculateNetAmount($subtotal, $commissionFee): float
    {
        return $subtotal - $commissionFee;
    }
}
