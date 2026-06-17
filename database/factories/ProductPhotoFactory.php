<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductPhotoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'photo_path' => 'products/' . fake()->uuid() . '.jpg',
            'alt_text' => fake()->sentence(3),
            'is_primary' => false,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }

    public function primary(): static
    {
        return $this->state(fn () => ['is_primary' => true]);
    }
}
