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
     * - Expired discounts
     * - Future discounts (not started yet)
     * - Discounts without date restrictions
     *
     * @return void
     */
    public function run(): void
    {
        // Active percentage discounts
        Discount::factory()->percentage()->active()->valid()->create([
            'name' => 'Weekend Special',
            'code' => 'WEEKEND15',
            'value' => 15,
            'description' => '15% discount on weekends',
        ]);

        Discount::factory()->percentage()->active()->valid()->create([
            'name' => 'Lunch Time Deal',
            'code' => 'LUNCH20',
            'value' => 20,
            'description' => '20% off during lunch hours (11am-3pm)',
        ]);

        // Active fixed amount discounts
        Discount::factory()->fixed()->active()->valid()->create([
            'name' => 'Happy Hour',
            'code' => 'HAPPY10',
            'value' => 10,
            'description' => '$10 off orders over $50 during happy hour',
        ]);

        Discount::factory()->fixed()->active()->valid()->create([
            'name' => 'Family Deal',
            'code' => 'FAMILY15',
            'value' => 15,
            'description' => '$15 off family meals',
        ]);

        // Discounts without date restrictions (always valid if active)
        Discount::factory()->percentage()->active()->create([
            'name' => 'Loyalty Reward',
            'code' => 'LOYALTY10',
            'value' => 10,
            'start_date' => null,
            'end_date' => null,
            'description' => '10% discount for loyal customers',
        ]);

        Discount::factory()->fixed()->active()->create([
            'name' => 'New Customer Bonus',
            'code' => 'NEWCUST5',
            'value' => 5,
            'start_date' => null,
            'end_date' => null,
            'description' => '$5 off for first-time customers',
        ]);

        // Expired discounts (for testing global scope)
        Discount::factory()->percentage()->expired()->create([
            'name' => 'Winter Sale',
            'code' => 'WINTER30',
            'value' => 30,
            'description' => 'Winter season discount (expired)',
        ]);

        Discount::factory()->fixed()->expired()->create([
            'name' => 'Ramadan Special',
            'code' => 'RAMADAN20',
            'value' => 20,
            'description' => 'Ramadan special offer (expired)',
        ]);

        // Future discounts (not started yet)
        Discount::factory()->percentage()->future()->create([
            'name' => 'Black Friday',
            'code' => 'BLACK50',
            'value' => 50,
            'description' => 'Black Friday mega sale (upcoming)',
        ]);

        Discount::factory()->fixed()->future()->create([
            'name' => 'New Year Celebration',
            'code' => 'NEWYEAR25',
            'value' => 25,
            'description' => 'New Year special offer (upcoming)',
        ]);

        // Inactive discounts (for testing)
        Discount::factory()->percentage()->inactive()->create([
            'name' => 'Test Discount',
            'code' => 'TEST10',
            'value' => 10,
            'description' => 'Test discount (inactive)',
        ]);

        $this->command->info('Discounts seeded successfully.');
    }
}
