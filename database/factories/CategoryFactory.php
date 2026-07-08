<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => [
                'en' => fake()->word(),
                'ar' => fake()->word(),
            ],
            'slug' => fake()->unique()->slug(),
            'description' => [
                'en' => fake()->sentence(),
                'ar' => fake()->sentence(),
            ],
            'is_active' => true,
        ];
    }
}
