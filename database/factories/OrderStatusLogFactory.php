<?php

namespace Database\Factories;

use App\Models\OrderStatusLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderStatusLogFactory extends Factory
{
    protected $model = OrderStatusLog::class;

    public function definition(): array
    {
        return [
            'order_id' => \App\Models\Order::factory(),
            'old_status' => fake()->optional()->randomElement(['new', 'preparing', 'ready', 'completed']),
            'new_status' => fake()->randomElement(['new', 'preparing', 'ready', 'completed', 'cancelled']),
            'changed_by' => \App\Models\User::factory(),
            'notes' => fake()->optional()->sentence(),
            'created_at' => now(),
        ];
    }
}
