<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'alternate_phone' => fake()->optional()->phoneNumber(),
            'address' => fake()->address(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
