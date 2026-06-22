<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_name',
        'instagram',
        'order_number',
        'status',
        'subtotal',
        'commission_fee',
        'admin_fee',
        'total',
        'dp_total',
        'shipping_cost',
        'grand_total',
        'shipping_courier',
        'shipping_service',
        'tracking_number',
        'shipping_address',
        'city',
        'province',
        'postal_code',
        'district',
        'suburb',
        'return_date',
        'payment_status',
        'payment_method',
        'notes',
        'address',
        'phone',
        'refund_bank_name',
        'refund_bank_account',
        'refund_bank_holder',
        'paid_at',
        'processed_at',
        'shipped_at',
        'confirmed_at',
        'completed_at',
        'cancelled_at',
        'returned_at',
        'dp_refunded',
        'dp_deducted',
        'dp_status',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'commission_fee' => 'decimal:2',
            'admin_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'dp_total' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'dp_refunded' => 'decimal:2',
            'dp_deducted' => 'decimal:2',
            'paid_at' => 'datetime',
            'processed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'returned_at' => 'datetime',
            'return_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'BG';
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(6));

        return "{$prefix}-{$date}-{$random}";
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending_payment');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending_payment';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isDpPending(): bool
    {
        return $this->dp_status === 'pending';
    }

    public function isDpTransferred(): bool
    {
        return $this->dp_status === 'transferred';
    }

    public function isDpCompleted(): bool
    {
        return $this->dp_status === 'completed';
    }
}
