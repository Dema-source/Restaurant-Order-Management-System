<?php

namespace Database\Seeders;

use App\Models\InventoryMovement;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeder for populating the inventory_movements table with initial data.
 *
 * This seeder creates realistic inventory movements including restocks,
 * waste, adjustments, and order-related movements. It demonstrates various
 * inventory scenarios and provides test data for the inventory management system.
 *
 * Usage:
 *   php artisan db:seed --class=InventoryMovementSeeder
 *
 * Note: Run MenuItemSeeder and UserSeeder before this seeder.
 *
 * @author Your Name
 * @since 1.0.0
 */
class InventoryMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This method creates inventory movements for menu items with realistic
     * data including different movement types (in/out), reasons (restock, waste,
     * adjustment, order), and timestamps.
     *
     * @return void
     */
    public function run(): void
    {
        // Ensure menu items and users exist
        $menuItems = MenuItem::all();
        $users = User::all();

        if ($menuItems->isEmpty()) {
            $this->command->warn('No menu items found. Please run MenuItemSeeder first.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        $adminUser = $users->where('email', 'admin@example.com')->first() ?? $users->first();
        $cashierUser = $users->where('email', 'cashier@example.com')->first() ?? $users->last();

        // Create inventory movements
        $movements = $this->getInventoryMovementsData($menuItems, $adminUser, $cashierUser);

        foreach ($movements as $movementData) {
            InventoryMovement::create($movementData);
        }

        $this->command->info('Inventory movements seeded successfully.');
    }

    /**
     * Get inventory movements data array.
     *
     * This method returns a comprehensive array of inventory movement data
     * including restocks, waste, adjustments, and order movements with realistic
     * quantities and timestamps.
     *
     * @param \Illuminate\Database\Eloquent\Collection $menuItems The menu items collection
     * @param User $adminUser The admin user
     * @param User $cashierUser The cashier user
     * @return array<int, array<string, mixed>> The inventory movements data
     */
    protected function getInventoryMovementsData($menuItems, $adminUser, $cashierUser): array
    {
        $movements = [];
        $now = Carbon::now();

        // Helper to get menu item by slug
        $getItem = function ($slug) use ($menuItems) {
            return $menuItems->where('slug', $slug)->first();
        };

        // Helper to create movement
        $createMovement = function ($menuItem, $type, $quantity, $reason, $notes, $user, $daysAgo = 0) use (&$movements, $now) {
            $movements[] = [
                'menu_item_id' => $menuItem->id,
                'order_id' => null,
                'type' => $type,
                'quantity' => $quantity,
                'reason' => $reason,
                'notes' => $notes,
                'created_by' => $user->id,
                'created_at' => $now->copy()->subDays($daysAgo),
            ];
        };

        // ==================== RESTOCK MOVEMENTS ====================

        // Initial restock for popular items (30 days ago)
        $createMovement($getItem('hummus'), 'in', 100, 'restock', 'Initial stock', $adminUser, 30);
        $createMovement($getItem('falafel-plate'), 'in', 150, 'restock', 'Initial stock', $adminUser, 30);
        $createMovement($getItem('grilled-chicken'), 'in', 80, 'restock', 'Initial stock', $adminUser, 30);
        $createMovement($getItem('lamb-kebab'), 'in', 60, 'restock', 'Initial stock', $adminUser, 30);
        $createMovement($getItem('fresh-lemonade'), 'in', 200, 'restock', 'Initial stock', $adminUser, 30);
        $createMovement($getItem('chicken-shawarma'), 'in', 120, 'restock', 'Initial stock', $adminUser, 30);

        // Weekly restock (21 days ago)
        $createMovement($getItem('hummus'), 'in', 50, 'restock', 'Weekly restock', $adminUser, 21);
        $createMovement($getItem('falafel-plate'), 'in', 80, 'restock', 'Weekly restock', $adminUser, 21);
        $createMovement($getItem('grilled-chicken'), 'in', 40, 'restock', 'Weekly restock', $adminUser, 21);
        $createMovement($getItem('fresh-lemonade'), 'in', 100, 'restock', 'Weekly restock', $adminUser, 21);

        // Recent restock (7 days ago)
        $createMovement($getItem('hummus'), 'in', 60, 'restock', 'Weekly restock', $cashierUser, 7);
        $createMovement($getItem('falafel-plate'), 'in', 90, 'restock', 'Weekly restock', $cashierUser, 7);
        $createMovement($getItem('grilled-chicken'), 'in', 50, 'restock', 'Weekly restock', $cashierUser, 7);
        $createMovement($getItem('lamb-kebab'), 'in', 30, 'restock', 'Weekly restock', $cashierUser, 7);
        $createMovement($getItem('fresh-lemonade'), 'in', 150, 'restock', 'Weekly restock', $cashierUser, 7);
        $createMovement($getItem('chicken-shawarma'), 'in', 70, 'restock', 'Weekly restock', $cashierUser, 7);
        $createMovement($getItem('turkish-coffee'), 'in', 100, 'restock', 'Weekly restock', $cashierUser, 7);

        // ==================== WASTE MOVEMENTS ====================

        // Expired items (25 days ago)
        $createMovement($getItem('hummus'), 'out', 15, 'waste', 'Expired batch', $adminUser, 25);
        $createMovement($getItem('fresh-lemonade'), 'out', 20, 'waste', 'Expired batch', $adminUser, 25);

        // Spoiled items (18 days ago)
        $createMovement($getItem('falafel-plate'), 'out', 10, 'waste', 'Spoiled during storage', $adminUser, 18);

        // Preparation waste (14 days ago)
        $createMovement($getItem('grilled-chicken'), 'out', 5, 'waste', 'Preparation error', $cashierUser, 14);

        // Recent waste (5 days ago)
        $createMovement($getItem('fresh-lemonade'), 'out', 15, 'waste', 'Spilled during service', $cashierUser, 5);
        $createMovement($getItem('chicken-shawarma'), 'out', 8, 'waste', 'Overcooked', $cashierUser, 5);

        // ==================== ADJUSTMENT MOVEMENTS ====================

        // Inventory correction (20 days ago)
        $createMovement($getItem('hummus'), 'in', 5, 'adjustment', 'Physical count correction', $adminUser, 20);

        // Inventory correction (15 days ago)
        $createMovement($getItem('falafel-plate'), 'out', 3, 'adjustment', 'Physical count discrepancy', $adminUser, 15);

        // Recent adjustment (3 days ago)
        $createMovement($getItem('grilled-chicken'), 'in', 2, 'adjustment', 'Found missing items', $cashierUser, 3);
        $createMovement($getItem('fresh-lemonade'), 'out', 5, 'adjustment', 'Recorded waste not documented', $cashierUser, 3);

        // ==================== ORDER MOVEMENTS ====================

        // Simulated order movements (various dates)
        // These represent stock reserved/used for orders
        $orderDates = [28, 27, 26, 25, 24, 23, 22, 21, 20, 19, 18, 17, 16, 15, 14, 13, 12, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1];

        foreach ($orderDates as $day) {
            // Random order quantities
            $createMovement($getItem('hummus'), 'out', rand(3, 8), 'order', 'Order #'.rand(1000, 9999), $cashierUser, $day);
            $createMovement($getItem('falafel-plate'), 'out', rand(5, 12), 'order', 'Order #'.rand(1000, 9999), $cashierUser, $day);
            $createMovement($getItem('grilled-chicken'), 'out', rand(2, 6), 'order', 'Order #'.rand(1000, 9999), $cashierUser, $day);
            $createMovement($getItem('fresh-lemonade'), 'out', rand(8, 15), 'order', 'Order #'.rand(1000, 9999), $cashierUser, $day);
            $createMovement($getItem('chicken-shawarma'), 'out', rand(4, 10), 'order', 'Order #'.rand(1000, 9999), $cashierUser, $day);
        }

        // Additional popular items orders
        foreach ([28, 21, 14, 7, 3, 1] as $day) {
            $createMovement($getItem('lamb-kebab'), 'out', rand(2, 5), 'order', 'Order #'.rand(1000, 9999), $cashierUser, $day);
            $createMovement($getItem('turkish-coffee'), 'out', rand(10, 20), 'order', 'Order #'.rand(1000, 9999), $cashierUser, $day);
        }

        // ==================== LOW STOCK SCENARIOS ====================

        // Items with low stock for testing low stock alerts
        $createMovement($getItem('lentil-soup'), 'in', 20, 'restock', 'Initial stock', $adminUser, 30);
        $createMovement($getItem('lentil-soup'), 'out', 15, 'order', 'Order #'.rand(1000, 9999), $cashierUser, 10);

        $createMovement($getItem('chicken-soup'), 'in', 15, 'restock', 'Initial stock', $adminUser, 30);
        $createMovement($getItem('chicken-soup'), 'out', 12, 'order', 'Order #'.rand(1000, 9999), $cashierUser, 10);

        return $movements;
    }
}
