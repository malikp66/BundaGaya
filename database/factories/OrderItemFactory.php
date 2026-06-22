<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $pricePerDay = fake()->numberBetween(50000, 500000);
        $days = fake()->numberBetween(1, 7);
        $quantity = 1;
        $subtotal = $pricePerDay * $days * $quantity;
        $commissionRate = 10.00;
        $commissionFee = $subtotal * ($commissionRate / 100);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'start_date' => fake()->dateTimeBetween('+1 week', '+1 month')->format('Y-m-d'),
            'end_date' => fake()->dateTimeBetween('+1 week +1 day', '+1 month +7 days')->format('Y-m-d'),
            'days' => $days,
            'price_per_day' => $pricePerDay,
            'subtotal' => $subtotal,
            'commission_rate' => $commissionRate,
            'commission_fee' => $commissionFee,
            'net_amount' => $subtotal - $commissionFee,
            'status' => 'pending',
        ];
    }
}
