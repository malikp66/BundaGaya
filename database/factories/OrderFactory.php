<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(100000, 5000000);
        $commissionFee = $subtotal * 0.1;

        return [
            'user_id' => User::factory(),
            'customer_name' => fake()->name(),
            'order_number' => 'BG-' . now()->format('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6)),
            'status' => 'pending_payment',
            'subtotal' => $subtotal,
            'commission_fee' => $commissionFee,
            'total' => $subtotal,
            'payment_status' => 'pending',
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'notes' => fake()->optional()->sentence(),
            'return_date' => now()->addDays(3)->format('Y-m-d'),
            'refund_bank_name' => 'BCA',
            'refund_bank_account' => fake()->numerify('#############'),
            'refund_bank_holder' => fake()->name(),
            'dp_status' => 'pending',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => 'paid',
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'payment_status' => 'paid',
            'paid_at' => now()->subDays(5),
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }
}
