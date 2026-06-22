<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true) . ' ' . fake()->colorName();

        return [
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . uniqid(),
            'description' => fake()->paragraph(),
            'price_per_day' => fake()->numberBetween(50000, 500000),
            'stock' => fake()->numberBetween(1, 10),
            'size' => fake()->randomElement(['S', 'M', 'L', 'XL', 'XXL']),
            'color' => fake()->colorName(),
            'material' => fake()->randomElement(['Silk', 'Cotton', 'Polyester', 'Linen', 'Satin']),
            'condition' => fake()->randomElement(['new', 'good', 'fair']),
            'status' => 'active',
            'is_featured' => fake()->boolean(20),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }
}
