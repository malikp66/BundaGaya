<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'payment_number' => 'PAY-' . now()->format('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(8)),
            'amount' => fake()->numberBetween(100000, 5000000),
            'method' => fake()->randomElement(['bank_transfer', 'e_wallet', 'credit_card']),
            'gateway' => 'midtrans',
            'status' => 'pending',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => 'paid',
            'paid_at' => now(),
            'transaction_id' => fake()->uuid(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => 'expired',
            'expired_at' => now(),
        ]);
    }
}
