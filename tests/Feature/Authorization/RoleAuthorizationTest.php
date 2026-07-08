<?php

use App\Models\Category;
use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\InteractsWithModels;
use Tests\Traits\InteractsWithResponses;
use Tests\Traits\InteractsWithRoles;

uses(RefreshDatabase::class, InteractsWithRoles::class, InteractsWithResponses::class, InteractsWithModels::class);

beforeEach(function () {
    // Create roles
    $superAdminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_administrator']);
    $cashierRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Cashier']);
    $kitchenRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Kitchen_staff']);
});

// User
test('admin can manage users', function () {
    $this->actingAsRole('super_administrator');

    $response = $this
        ->getJson('/api/admin/users');

    $this->assertSuccessResponse($response, [
        'data' => [
            '*' => [
                'id',
                'name',
                'email',
            ],
        ],
    ]);
});

test('cashier cannot manage users', function () {
    $this->actingAsRole('Cashier');

    $response = $this
        ->getJson('/api/admin/users');

    $this->assertForbiddenResponse($response);
});

test('kitchen cannot manage users', function () {
    $this->actingAsRole('Kitchen_staff');

    $response = $this
        ->getJson('/api/admin/users');

    $this->assertForbiddenResponse($response);
});

// Order
test('cashier can create order', function () {
    $this->actingAsRole('Cashier');

    $customer = Customer::factory()->create();
    [
        'category' => $category,
        'menuItem' => $menuItem
    ] = $this->createCategoryWithMenuItems(1);

    $response = $this
        ->postJson('/api/cashier/orders', [
            'customer' => [
                'name' => 'Test Customer',
                'phone' => '1234567890',
            ],
            'items' => [
                [
                    'menu_item_id' => $menuItem[0]->id,
                    'quantity' => 2,
                ],
            ],
            'delivery_address' => 'Test Address',
        ]);

    $this->assertCreatedResponse($response, [
        'data' => [
            'id',
            'order_number',
            'customer',
            'items',
            'subtotal',
            'total_amount',
            'status',
        ],
        'message',
    ]);
});

test('kitchen cannot create order', function () {
    $this->actingAsRole('Kitchen_staff');

    $customer = Customer::factory()->create();
    [
        'category' => $category,
        'menuItem' => $menuItem
    ] = $this->createCategoryWithMenuItems(1);

    $response = $this
        ->postJson('/api/cashier/orders', [
            'customer' => [
                'name' => 'Test Customer',
                'phone' => '1234567890',
            ],
            'items' => [
                [
                    'menu_item_id' => $menuItem[0]->id,
                    'quantity' => 2,
                ],
            ],
        ]);

    $this->assertForbiddenResponse($response);
});

test('kitchen cannot delete order', function () {
    $this->actingAsRole('Kitchen_staff');

    $order = Order::factory()->create();

    $response = $this
        ->deleteJson("/api/admin/orders/{$order->id}");

    $this->assertForbiddenResponse($response);
});

test('admin can delete order', function () {
    $this->actingAsRole('super_administrator');

    $order = Order::factory()->create();

    $response = $this
        ->deleteJson("/api/admin/orders/{$order->id}");

    $this->assertSuccessResponse($response);
});

test('cashier cannot delete order', function () {
    $this->actingAsRole('Cashier');

    $order = Order::factory()->create();

    $response = $this
        ->deleteJson("/api/admin/orders/{$order->id}");

    $this->assertForbiddenResponse($response);
});

test('kitchen can view orders', function () {
    $this->actingAsRole('Kitchen_staff');

    $response = $this
        ->getJson('/api/kitchen/orders');

    $this->assertSuccessResponse($response);
});

test('cashier can view orders', function () {
    $this->actingAsRole('Cashier');

    $response = $this
        ->getJson('/api/cashier/orders');

    $this->assertSuccessResponse($response);
});

test('admin can update order status', function () {
    $this->actingAsRole('super_administrator');

    $order = Order::factory()->create(['status' => 'new']);

    $response = $this
        ->patchJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'preparing',
        ]);

     $this->assertSuccessResponse($response);
});

test('kitchen can update order status to preparing', function () {
    $this->actingAsRole('Kitchen_staff');

    $order = Order::factory()->create(['status' => 'new']);

    $response = $this
        ->patchJson("/api/kitchen/orders/{$order->id}/status", [
            'status' => 'preparing',
        ]);

     $this->assertSuccessResponse($response);
});

test('cashier cannot update order status to preparing', function () {
    $this->actingAsRole('Cashier');

    $order = Order::factory()->create(['status' => 'new']);

    $response = $this
        ->patchJson("/api/cashier/orders/{$order->id}/status", [
            'status' => 'preparing',
        ]);

    $this->assertValidationErrorResponse($response, ['status']);
});

// Inventory
test('admin can manage inventory movements', function () {
    $this->actingAsRole('super_administrator');

        [
        'category' => $category,
        'menuItem' => $menuItem
    ] = $this->createCategoryWithMenuItems(1);

    $response = $this
        ->postJson('/api/admin/inventories/restock', [
            'menu_item_id' => $menuItem[0]->id,
            'type' => 'in',
            'reason' => 'restock',
            'quantity' => 10,
        ]);

    $this->assertCreatedResponse($response);
});

test('cashier can manage inventory movements', function () {
    $this->actingAsRole('Cashier');

        [
        'category' => $category,
        'menuItem' => $menuItem
    ] = $this->createCategoryWithMenuItems(1);

    $response = $this
        ->postJson('/api/cashier/inventories/restock', [
            'menu_item_id' => $menuItem[0]->id,
            'type' => 'in',
            'reason' => 'restock',
            'quantity' => 10,
        ]);

    $this->assertCreatedResponse($response);
});

// Discount
test('admin can manage discounts', function () {
    $this->actingAsRole('super_administrator');

    $response = $this
        ->postJson('/api/admin/discounts', [
            'name' => 'Test Discount',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'is_active' => true,
        ]);

    $this->assertCreatedResponse($response);
});

test('cashier cannot manage discounts', function () {
    $this->actingAsRole('Cashier');

    $response = $this
        ->postJson('/api/admin/discounts', [
            'name' => 'Test Discount',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'is_active' => true,
        ]);

    $this->assertForbiddenResponse($response);
});

test('kitchen cannot manage discounts', function () {
    $this->actingAsRole('Kitchen_staff');

    $response = $this
        ->postJson('/api/admin/discounts', [
            'name' => 'Test Discount',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'is_active' => true,
        ]);

    $this->assertForbiddenResponse($response);
});

// Menu Item
test('admin can manage menu items', function () {
    $this->actingAsRole('super_administrator');

    $category = Category::factory()->create();

    $response = $this
        ->postJson('/api/admin/menu-items', [
            'category_id' => $category->id,
            'name' => ['en' => 'Test Item', 'ar' => 'عنصر اختبار'],
            'slug' => 'test-item',
            'price' => 10.0,
            'is_available' => true,
            'stock_quantity' => 20,
        ]);

    $this->assertCreatedResponse($response);
});

test('cashier cannot manage menu items', function () {
    $this->actingAsRole('Cashier');

    $category = Category::factory()->create();

    $response = $this
        ->postJson('/api/admin/menu-items', [
            'category_id' => $category->id,
            'name' => ['en' => 'Test Item', 'ar' => 'عنصر اختبار'],
            'price' => 10.0,
            'is_available' => true,
            'stock_quantity' => 20,
        ]);

    $this->assertForbiddenResponse($response);
});

test('kitchen cannot manage menu items', function () {
    $this->actingAsRole('Kitchen_staff');

    $category = Category::factory()->create();

    $response = $this
        ->postJson('/api/admin/menu-items', [
            'category_id' => $category->id,
            'name' => ['en' => 'Test Item', 'ar' => 'عنصر اختبار'],
            'price' => 10.0,
            'is_available' => true,
            'stock_quantity' => 20,
        ]);

    $this->assertForbiddenResponse($response);
});
