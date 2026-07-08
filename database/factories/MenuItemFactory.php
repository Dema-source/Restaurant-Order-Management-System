<?php

namespace Database\Factories;

use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    public function definition(): array
    {
        return [
            'category_id' => \App\Models\Category::factory(),
            'name' => [
                'en' => fake()->word(),
                'ar' => fake()->word(),
            ],
            'slug' => fake()->unique()->slug(),
            'description' => [
                'en' => fake()->sentence(),
                'ar' => fake()->sentence(),
            ],
            'price' => fake()->randomFloat(2, 5, 100),
            'image' => fake()->imageUrl(),
            'is_available' => true,
            'stock_quantity' => fake()->numberBetween(0, 100),
        ];
    }
}
