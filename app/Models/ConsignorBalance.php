<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsignorBalance extends Model
{
    protected $fillable = [
        'user_id',
        'balance',
        'total_earned',
        'total_withdrawn',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'total_earned' => 'decimal:2',
            'total_withdrawn' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function credit(float $amount): void
    {
        $this->increment('balance', $amount);
        $this->increment('total_earned', $amount);
    }

    public function debit(float $amount): void
    {
        $this->decrement('balance', $amount);
        $this->increment('total_withdrawn', $amount);
    }
}
