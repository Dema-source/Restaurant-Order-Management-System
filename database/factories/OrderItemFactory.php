<?php

namespace Database\Factories;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'order_id' => \App\Models\Order::factory(),
            'menu_item_id' => \App\Models\MenuItem::factory(),
            'quantity' => fake()->numberBetween(1, 10),
            'unit_price' => fake()->randomFloat(2, 5, 100),
            'subtotal' => fake()->randomFloat(2, 10, 500),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
