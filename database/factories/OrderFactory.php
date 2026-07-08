<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'order_number' => 'ORD-' . fake()->unique()->numberBetween(10000, 99999),
            'customer_id' => \App\Models\Customer::factory(),
            'discount_id' => null,
            'created_by' => \App\Models\User::factory(),
            'status' => fake()->randomElement(['new', 'preparing', 'ready', 'delivered', 'cancelled']),
            'subtotal' => fake()->randomFloat(2, 10, 500),
            'discount_amount' => fake()->randomFloat(2, 0, 50),
            'total_amount' => fake()->randomFloat(2, 10, 500),
            'delivery_address' => fake()->address(),
            'notes' => fake()->optional()->sentence(),
            'ordered_at' => now(),
            'delivered_at' => fake()->optional()->dateTime(),
        ];
    }
}
