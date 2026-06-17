<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    public function definition(): array
    {
        $amount = fake()->numberBetween(100000, 5000000);
        $commissionRate = 10.00;
        $commissionFee = $amount * ($commissionRate / 100);

        return [
            'transaction_id' => 'TRX-' . now()->format('YmdHis') . '-' . strtoupper(\Illuminate\Support\Str::random(4)),
            'order_id' => Order::factory(),
            'order_item_id' => OrderItem::factory(),
            'shop_id' => Shop::factory(),
            'amount' => $amount,
            'commission_rate' => $commissionRate,
            'commission_fee' => $commissionFee,
            'net_amount' => $amount - $commissionFee,
            'status' => 'pending',
        ];
    }

    public function settled(): static
    {
        return $this->state(fn () => [
            'status' => 'settled',
            'settled_at' => now(),
        ]);
    }
}
