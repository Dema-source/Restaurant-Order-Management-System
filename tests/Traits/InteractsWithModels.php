<?php

namespace Tests\Traits;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\MenuItem;
use App\Models\Order;

trait InteractsWithModels
{
    protected function createCategoryWithMenuItem(
        array $categoryOverrides = [],
        array $menuItemOverrides = []
    ): array {
        $category = Category::factory()->create($categoryOverrides);

        $menuItem = MenuItem::factory()->create(array_merge([
            'category_id' => $category->id,
            'stock_quantity' => 10,
            'is_available' => true,
        ], $menuItemOverrides));

        return [
            'category' => $category->fresh(),
            'menuItem' => $menuItem,
        ];
    }

    protected function createCategoryWithMenuItems(int $count = 1, array $categoryOverrides = [], array $menuItemOverrides = []): array
    {
        $category = Category::factory()->create($categoryOverrides);
        $menuItem = MenuItem::factory()->count($count)->create(array_merge([
            'category_id' => $category->id,
            'stock_quantity' => 10,
            'is_available' => true,
        ], $menuItemOverrides));

        // return $category->fresh();
        return [
            'category' => $category->fresh(),
            'menuItem' => $menuItem,
        ];
    }

    protected function createCategoryWithCustomMenuItems(
        array $categoryOverrides = [],
        array $menuItems = []
    ): array {
        $category = Category::factory()->create($categoryOverrides);

        $createdMenuItems = collect($menuItems)->map(function ($menuItem) use ($category) {
            return MenuItem::factory()->create(array_merge([
                'category_id' => $category->id,
                'is_available' => true,
            ], $menuItem));
        });

        return [
            'category' => $category->fresh(),
            'menuItems' => $createdMenuItems,
        ];
    }

    protected function createOrderWithItems(array $orderOverrides = [], int $itemCount = 1): Order
    {
        $category = Category::factory()->create();
        $menuItem = MenuItem::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 10,
            'is_available' => true,
        ]);

        $order = Order::factory()->create($orderOverrides);

        for ($i = 0; $i < $itemCount; $i++) {
            $order->items()->create([
                'menu_item_id' => $menuItem->id,
                'quantity' => 1,
                'unit_price' => $menuItem->price,
            ]);
        }

        return $order->fresh();
    }

    protected function createActiveDiscount(array $overrides = []): Discount
    {
        return Discount::factory()->create(array_merge([
            'is_active' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ], $overrides));
    }

    protected function createCustomerWithOrders(int $orderCount = 1): array
    {
        $customer = Customer::factory()->create();
        Order::factory()->count($orderCount)->create(['customer_id' => $customer->id]);

        return [$customer, $customer->orders];
    }
}
