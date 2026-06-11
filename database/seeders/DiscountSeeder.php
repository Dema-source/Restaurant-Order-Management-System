<?php

namespace Database\Seeders;

use App\Models\Discount;
use Illuminate\Database\Seeder;

/**
 * Discount seeder for populating the discounts table.
 *
 * This seeder creates realistic discount data for testing and development,
 * including various discount types (percentage and fixed) with different
 * validity periods and statuses.
 */
class DiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This method creates a variety of discounts including:
     * - Active percentage discounts
     * - Active fixed amount discounts
     * - Weekday discounts
     * - Subtotal-based discounts
     * - Expired discounts
     * - Future discounts (not started yet)
     * - Discounts without date restrictions
     *
     * @return void
     */
    public function run(): void
    {
        // Active percentage discounts
        // Discount::factory()->percentage()->active()->valid()->create([
        //     'name' => 'Weekend Special',
        //     'discount_type' => 'percentage',
        //     'discount_value' => 15,
        //     'weekday' => 'Saturday',
        // ]);

        // Discount::factory()->percentage()->active()->valid()->create([
        //     'name' => 'Sunday Deal',
        //     'discount_type' => 'percentage',
        //     'discount_value' => 20,
        //     'weekday' => 'Sunday',
        // ]);

        // Active fixed amount discounts
        Discount::factory()->fixed()->active()->valid()->create([
            'name' => 'Happy Hour',
            'discount_type' => 'fixed',
            'discount_value' => 10,
            'minimum_order_amount' => 50,
        ]);

        // Discount::factory()->fixed()->active()->valid()->create([
        //     'name' => 'Family Deal',
        //     'discount_type' => 'fixed',
        //     'discount_value' => 15,
        //     'minimum_order_amount' => 100,
        // ]);

        // // Weekday discounts
        // Discount::factory()->percentage()->active()->valid()->create([
        //     'name' => 'Monday Madness',
        //     'discount_type' => 'percentage',
        //     'discount_value' => 25,
        //     'weekday' => 'Monday',
        // ]);

        // Discount::factory()->percentage()->active()->valid()->create([
        //     'name' => 'Wednesday Special',
        //     'discount_type' => 'percentage',
        //     'discount_value' => 10,
        //     'weekday' => 'Wednesday',
        // ]);

        // // Discounts without date restrictions (always valid if active)
        // Discount::factory()->percentage()->active()->create([
        //     'name' => 'Loyalty Reward',
        //     'discount_type' => 'percentage',
        //     'discount_value' => 10,
        //     'minimum_order_amount' => 30,
        //     'start_date' => null,
        //     'end_date' => null,
        // ]);

        // Discount::factory()->fixed()->active()->create([
        //     'name' => 'New Customer Bonus',
        //     'discount_type' => 'fixed',
        //     'discount_value' => 5,
        //     'minimum_order_amount' => 20,
        //     'start_date' => null,
        //     'end_date' => null,
        // ]);

        // // Expired discounts (for testing global scope)
        // Discount::factory()->percentage()->expired()->create([
        //     'name' => 'Winter Sale',
        //     'discount_type' => 'percentage',
        //     'discount_value' => 30,
        // ]);

        // Discount::factory()->fixed()->expired()->create([
        //     'name' => 'Ramadan Special',
        //     'discount_type' => 'fixed',
        //     'discount_value' => 20,
        // ]);

        // // Future discounts (not started yet)
        // Discount::factory()->percentage()->future()->create([
        //     'name' => 'Black Friday',
        //     'discount_type' => 'percentage',
        //     'discount_value' => 50,
        // ]);

        // Discount::factory()->fixed()->future()->create([
        //     'name' => 'New Year Celebration',
        //     'discount_type' => 'fixed',
        //     'discount_value' => 25,
        // ]);

        // // Inactive discounts (for testing)
        // Discount::factory()->percentage()->inactive()->create([
        //     'name' => 'Test Discount',
        //     'discount_type' => 'percentage',
        //     'discount_value' => 10,
        // ]);

        $this->command->info('Discounts seeded successfully.');
    }
}
