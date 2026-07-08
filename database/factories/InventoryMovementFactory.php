<?php

namespace Database\Factories;

use App\Models\InventoryMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryMovementFactory extends Factory
{
    protected $model = InventoryMovement::class;

    public function definition(): array
    {
        return [
            'menu_item_id' => \App\Models\MenuItem::factory(),
            'order_id' => fake()->optional()->randomElement([\App\Models\Order::factory(), null]),
            'type' => fake()->randomElement(['in', 'out']),
            'reason' => fake()->randomElement(['restock', 'waste', 'adjustment', 'order']),
            'quantity' => fake()->numberBetween(1, 50),
            'notes' => fake()->optional()->sentence(),
            'created_by' => \App\Models\User::factory(),
            'created_at' => now(),
        ];
    }
}
