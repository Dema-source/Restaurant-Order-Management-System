<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Discount;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeder for populating the orders and order_items tables with initial data.
 *
 * This seeder creates realistic orders with order items, linking them to
 * customers, menu items, and discounts. It demonstrates various order statuses
 * and provides test data for the order management system.
 *
 * Usage:
 *   php artisan db:seed --class=OrderSeeder
 *
 * Note: Run CustomerSeeder, MenuItemSeeder, DiscountSeeder, and UserSeeder before this seeder.
 *
 */
class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This method creates orders with order items for customers using menu items.
     * It ensures that customers, menu items, users, and discounts exist before creating orders.
     *
     * @return void
     */
    public function run(): void
    {
        $customers = Customer::all();
        $menuItems = MenuItem::all();
        $users = User::all();
        $discounts = Discount::all();

        if ($customers->isEmpty()) {
            $this->command->warn('No customers found. Please run CustomerSeeder first.');
            return;
        }

        if ($menuItems->isEmpty()) {
            $this->command->warn('No menu items found. Please run MenuItemSeeder first.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        $cashierUser = $users->where('email', 'cashier@example.com')->first() ?? $users->first();
        $adminUser = $users->where('email', 'admin@example.com')->first() ?? $users->last();

        // Create orders
        $orders = $this->getOrdersData($customers, $menuItems, $discounts, $cashierUser, $adminUser);

        foreach ($orders as $orderData) {
            $order = Order::create($orderData['order']);

            // Create order items
            foreach ($orderData['items'] as $itemData) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $itemData['menu_item_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'subtotal' => $itemData['subtotal'],
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            // Attach discounts if any
            if (!empty($orderData['discounts'])) {
                foreach ($orderData['discounts'] as $discountData) {
                    $order->discounts()->attach($discountData['discount_id'], [
                        'applied_value' => $discountData['applied_value'],
                        'created_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('Orders seeded successfully.');
    }

    /**
     * Get the orders data array.
     *
     * This method returns a comprehensive array of order data including
     * order items and discounts with realistic information.
     *
     * @param \Illuminate\Database\Eloquent\Collection $customers The customers collection
     * @param \Illuminate\Database\Eloquent\Collection $menuItems The menu items collection
     * @param \Illuminate\Database\Eloquent\Collection $discounts The discounts collection
     * @param User $cashierUser The cashier user
     * @param User $adminUser The admin user
     * @return array<int, array<string, mixed>> The orders data
     */
    protected function getOrdersData($customers, $menuItems, $discounts, $cashierUser, $adminUser): array
    {
        $orders = [];
        $now = Carbon::now();

        // Helper to get menu item by slug
        $getItem = function ($slug) use ($menuItems) {
            return $menuItems->where('slug', $slug)->first();
        };

        // Helper to get customer by name
        $getCustomer = function ($name) use ($customers) {
            return $customers->where('name', $name)->first();
        };

        // Helper to get discount by code
        $getDiscount = function ($code) use ($discounts) {
            return $discounts->where('code', $code)->first();
        };

        // Helper to create order
        $createOrder = function ($customer, $status, $subtotal, $discountAmount, $total, $user, $daysAgo, $deliveryAddress = 'Pickup at restaurant', $notes = null) use (&$orders, $now) {
            if (!$customer) {
                return null; // Skip if customer not found
            }
            $orderNumber = 'ORD-' . str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);
            $orders[] = [
                'order' => [
                    'order_number' => $orderNumber,
                    'customer_id' => $customer->id,
                    'created_by' => $user->id,
                    'status' => $status,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'total_amount' => $total,
                    'delivery_address' => $deliveryAddress,
                    'notes' => $notes,
                    'ordered_at' => $now->copy()->subDays($daysAgo),
                    'delivered_at' => $status === 'delivered' ? $now->copy()->subDays($daysAgo)->addHours(rand(1, 3)) : null,
                ],
                'items' => [],
                'discounts' => [],
            ];
            return count($orders) - 1; // Return index
        };

        // Helper to add item to order
        $addItem = function ($orderIndex, $menuItem, $quantity, $notes = null) use (&$orders) {
            if ($orderIndex === null || !$menuItem) {
                return; // Skip if order not created or menu item not found
            }
            $unitPrice = $menuItem->price;
            $subtotal = $unitPrice * $quantity;
            $orders[$orderIndex]['items'][] = [
                'menu_item_id' => $menuItem->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'notes' => $notes,
            ];
        };

        // Helper to add discount to order
        $addDiscount = function ($orderIndex, $discount, $appliedValue) use (&$orders) {
            if ($orderIndex === null || !$discount) {
                return; // Skip if order not created or discount not found
            }
            $orders[$orderIndex]['discounts'][] = [
                'discount_id' => $discount->id,
                'applied_value' => $appliedValue,
            ];
        };

        // ==================== RECENT DELIVERED ORDERS (1-3 days ago) ====================

        // Order 1: Ahmed Al-Farsi - Delivered
        $idx = $createOrder($getCustomer('Ahmed Al-Farsi'), 'out_for_delivery', 27.00, 0, 27.00, $cashierUser, 1, 'Damascus, Mezzeh, Street 15, Building 42');
        $addItem($idx, $getItem('grilled-chicken'), 1);
        $addItem($idx, $getItem('hummus'), 1);
        $addItem($idx, $getItem('fresh-lemonade'), 1);

        // Order 2: Fatima Al-Hassan - Delivered with discount
        $idx = $createOrder($getCustomer('Fatima Al-Hassan'), 'out_for_delivery', 45.00, 4.50, 40.50, $cashierUser, 2, 'Damascus, Malki, Al-Rawda Street', 'VIP customer');
        $addItem($idx, $getItem('mixed-grill-platter'), 1);
        $addItem($idx, $getItem('baklava'), 2);
        $addItem($idx, $getItem('turkish-coffee'), 2);
        $addDiscount($idx, $getDiscount('VIP10'), 4.50);

        // Order 3: Omar Khalil - Delivered
        $idx = $createOrder($getCustomer('Omar Khalil'), 'out_for_delivery', 18.00, 0, 18.00, $cashierUser, 3, 'Pickup at restaurant', 'No nuts please');
        $addItem($idx, $getItem('chicken-shawarma'), 1);
        $addItem($idx, $getItem('greek-salad'), 1);

        // ==================== READY ORDERS (today) ====================

        // Order 4: Layla Mahmoud - Ready
        $idx = $createOrder($getCustomer('Layla Mahmoud'), 'ready', 22.00, 0, 22.00, $cashierUser, 0, 'Damascus, Shaalan, Apartment 12');
        $addItem($idx, $getItem('lamb-kebab'), 1);
        $addItem($idx, $getItem('tabbouleh'), 1);
        $addItem($idx, $getItem('fresh-lemonade'), 1);

        // Order 5: Kareem Al-Sayed - Ready
        $idx = $createOrder($getCustomer('Kareem Al-Sayed'), 'ready', 35.00, 0, 35.00, $cashierUser, 0, 'Damascus, Muhajireen, Building 88', 'Corporate order');
        $addItem($idx, $getItem('mixed-grill-platter'), 1);
        $addItem($idx, $getItem('kunafa'), 1);
        $addItem($idx, $getItem('turkish-coffee'), 2);

        // ==================== PREPARING ORDERS ====================

        // Order 6: Nour Al-Din - Preparing
        $idx = $createOrder($getCustomer('Nour Al-Din'), 'preparing', 16.00, 0, 16.00, $cashierUser, 0, 'Damascus, Bab Touma, Old City');
        $addItem($idx, $getItem('shish-tawook'), 1);
        $addItem($idx, $getItem('stuffed-grape-leaves'), 1);
        $addItem($idx, $getItem('turkish-coffee'), 1);

        // Order 7: Samira Youssef - Preparing
        $idx = $createOrder($getCustomer('Samira Youssef'), 'preparing', 17.50, 0, 17.50, $cashierUser, 0, 'Damascus, Rukn Ed-Din, Street 3', 'Vegetarian');
        $addItem($idx, $getItem('falafel-plate'), 1);
        $addItem($idx, $getItem('tabbouleh'), 1);
        $addItem($idx, $getItem('lentil-soup'), 1);

        // ==================== PENDING ORDERS ====================

        // Order 8: Hassan Al-Rashid - New
        $idx = $createOrder($getCustomer('Hassan Al-Rashid'), 'new', 28.00, 0, 28.00, $cashierUser, 0, 'Damascus, Kafr Souseh, Complex A', 'Office lunch');
        $addItem($idx, $getItem('grilled-chicken'), 1);
        $addItem($idx, $getItem('chicken-shawarma'), 1);
        $addItem($idx, $getItem('fresh-lemonade'), 2);

        // Order 9: Amira Kaddour - New with discount
        $idx = $createOrder($getCustomer('Amira Kaddour'), 'new', 42.00, 8.40, 33.60, $cashierUser, 0, 'Damascus, Qassaa, Villa 15', 'Special event');
        $addItem($idx, $getItem('mixed-grill-platter'), 1);
        $addItem($idx, $getItem('kunafa'), 2);
        $addItem($idx, $getItem('fresh-lemonade'), 2);
        $addDiscount($idx, $getDiscount('SUMMER25'), 8.40);

        // Order 10: Youssef Al-Masri - New
        $idx = $createOrder($getCustomer('Youssef Al-Masri'), 'new', 14.00, 0, 14.00, $cashierUser, 0, 'Damascus, Jaramana, Main Road', 'Late night');
        $addItem($idx, $getItem('beef-shawarma'), 1);
        $addItem($idx, $getItem('turkish-coffee'), 2);

        // ==================== CANCELLED ORDERS (5-10 days ago) ====================

        // Order 11: Rania Fattal - Cancelled
        $idx = $createOrder($getCustomer('Rania Fattal'), 'cancelled', 25.00, 0, 25.00, $adminUser, 5, 'Damascus, Dummar, District 4', 'Customer cancelled');
        $addItem($idx, $getItem('grilled-chicken'), 1);
        $addItem($idx, $getItem('hummus'), 1);
        $addItem($idx, $getItem('baklava'), 1);

        // Order 12: Bilal Al-Ahmad - Cancelled
        $idx = $createOrder($getCustomer('Bilal Al-Ahmad'), 'cancelled', 19.00, 0, 19.00, $adminUser, 8, 'Damascus, Barzeh, Sector 2', 'Out of stock');
        $addItem($idx, $getItem('adana-kebab'), 1);
        $addItem($idx, $getItem('chicken-soup'), 1);

        // ==================== HISTORICAL ORDERS (10-30 days ago) ====================

        // Order 13: Hana Al-Saleh - Delivered
        $idx = $createOrder($getCustomer('Hana Al-Saleh'), 'out_for_delivery', 13.50, 0, 13.50, $cashierUser, 10, 'Damascus, Al-Muhajireen, Flat 8', 'Low sodium');
        $addItem($idx, $getItem('chicken-soup'), 1);
        $addItem($idx, $getItem('greek-salad'), 1);
        $addItem($idx, $getItem('fresh-lemonade'), 1);

        // Order 14: Jamal Al-Najjar - Delivered (bulk order)
        $idx = $createOrder($getCustomer('Jamal Al-Najjar'), 'out_for_delivery', 120.00, 12.00, 108.00, $cashierUser, 15, 'Damascus, Al-Qaboun, Industrial Zone', 'Bulk order for workers');
        $addItem($idx, $getItem('mixed-grill-platter'), 3);
        $addItem($idx, $getItem('chicken-shawarma'), 5);
        $addItem($idx, $getItem('falafel-plate'), 5);
        $addItem($idx, $getItem('fresh-lemonade'), 10);
        $addDiscount($idx, $getDiscount('BULK10'), 12.00);

        // Order 15: Sawsan Al-Turk - Delivered
        $idx = $createOrder($getCustomer('Sawsan Al-Turk'), 'out_for_delivery', 15.00, 0, 15.00, $cashierUser, 20, 'Damascus, Yarmouk, Camp Street', 'Breakfast');
        $addItem($idx, $getItem('falafel-plate'), 1);
        $addItem($idx, $getItem('hummus'), 1);
        $addItem($idx, $getItem('turkish-coffee'), 2);

        // Order 16: Ahmed Al-Farsi - Delivered (repeat customer)
        $idx = $createOrder($getCustomer('Ahmed Al-Farsi'), 'out_for_delivery', 30.00, 3.00, 27.00, $cashierUser, 25, 'Damascus, Mezzeh, Street 15, Building 42');
        $addItem($idx, $getItem('lamb-kebab'), 1);
        $addItem($idx, $getItem('tabbouleh'), 1);
        $addItem($idx, $getItem('kunafa'), 1);
        $addDiscount($idx, $getDiscount('LOYAL5'), 3.00);

        // Order 17: Fatima Al-Hassan - Delivered (VIP)
        $idx = $createOrder($getCustomer('Fatima Al-Hassan'), 'out_for_delivery', 50.00, 5.00, 45.00, $cashierUser, 28, 'Damascus, Malki, Al-Rawda Street', 'VIP customer');
        $addItem($idx, $getItem('mixed-grill-platter'), 1);
        $addItem($idx, $getItem('grilled-chicken'), 1);
        $addItem($idx, $getItem('baklava'), 2);
        $addItem($idx, $getItem('turkish-coffee'), 2);
        $addDiscount($idx, $getDiscount('VIP10'), 5.00);

        return $orders;
    }
}
