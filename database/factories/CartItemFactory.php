<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartItemFactory extends Factory
{
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('+1 week', '+1 month');
        $endDate = (clone $startDate)->modify('+2 days');
        $days = 3;
        $pricePerDay = fake()->numberBetween(50000, 500000);
        $quantity = fake()->numberBetween(1, 3);

        return [
            'cart_id' => Cart::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'days' => $days,
            'price_per_day' => $pricePerDay,
            'subtotal' => $pricePerDay * $days * $quantity,
        ];
    }
}
