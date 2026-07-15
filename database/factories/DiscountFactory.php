<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for generating fake Discount model data.
 *
 * This factory creates realistic discount data for testing and seeding,
 * including percentage and fixed discount types with proper validation.
 */
class DiscountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\Discount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed> The default model attributes
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['percentage', 'fixed']);
        
        // Generate appropriate value based on type
        $value = $type === 'percentage' 
            ? fake()->numberBetween(5, 50) // Percentage: 5-50%
            : fake()->numberBetween(5, 100); // Fixed: 5-100 currency units

        return [
            'name' => fake()->unique()->words(2, true),
            // 'name' => fake()->randomElement([
            //     'Summer Sale',
            //     'Winter Discount',
            //     'Weekend Special',
            //     'Happy Hour',
            //     'Lunch Special',
            //     'Dinner Deal',
            //     'Early Bird',
            //     'Loyalty Reward',
            //     'New Customer Bonus',
            //     'Holiday Offer',
            // ]),
            'discount_type' => $type,
            'discount_value' => $value,
            'minimum_order_amount' => fake()->optional(0.5)->numberBetween(20, 200),
            'weekday' => fake()->optional(0.3)->randomElement(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']),
            'start_date' => fake()->optional(0.7)->date,
            'end_date' => function (array $attributes) {
                // If start_date exists, end_date should be after it
                return isset($attributes['start_date']) && $attributes['start_date']
                    ? fake()->dateTimeBetween($attributes['start_date'], '+30 days')->format('Y-m-d')
                    : fake()->optional(0.5)->date;
            },
            'is_active' => fake()->boolean(80), // 80% chance of being active
        ];
    }

    /**
     * State for percentage discounts.
     *
     * @return static
     */
    public function percentage(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_type' => 'percentage',
            'discount_value' => fake()->numberBetween(5, 50),
        ]);
    }

    /**
     * State for fixed amount discounts.
     *
     * @return static
     */
    public function fixed(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_type' => 'fixed',
            'discount_value' => fake()->numberBetween(5, 100),
        ]);
    }

    /**
     * State for active discounts.
     *
     * @return static
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * State for inactive discounts.
     *
     * @return static
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * State for currently valid discounts (within date range).
     *
     * @return static
     */
    public function valid(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'end_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'is_active' => true,
        ]);
    }

    /**
     * State for expired discounts.
     *
     * @return static
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => fake()->dateTimeBetween('-60 days', '-30 days')->format('Y-m-d'),
            'end_date' => fake()->dateTimeBetween('-30 days', '-1 days')->format('Y-m-d'),
        ]);
    }

    /**
     * State for future discounts (not started yet).
     *
     * @return static
     */
    public function future(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => fake()->dateTimeBetween('+1 days', '+30 days')->format('Y-m-d'),
            'end_date' => fake()->dateTimeBetween('+31 days', '+60 days')->format('Y-m-d'),
        ]);
    }
}
