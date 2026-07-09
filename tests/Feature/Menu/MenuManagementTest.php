<?php

use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\MenuItem;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Traits\InteractsWithModels;
use Tests\Traits\InteractsWithResponses;
use Tests\Traits\InteractsWithRoles;

uses(RefreshDatabase::class, InteractsWithRoles::class, InteractsWithModels::class, InteractsWithResponses::class);

beforeEach(function () {
    $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_administrator']);
});

test('display menu items', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    [
        'category' => $category,
        'menuItem' => $menuItem
    ] = $this->createCategoryWithMenuItems(5, [], [
        'is_available' => true,
    ]);

    $response = $this
        ->getJson('/api/public/menu-items');

    $this->assertSuccessResponse($response, [
        'data' => [
            '*' => [
                'id',
                'name',
                'slug',
                'description',
                'price',
                'is_available',
                'stock_quantity',
            ],
        ],
        'message',
    ]);

    $response->assertJsonCount(5, 'data');
});

test('create menu item successfully', function () {
    $this->actingAsRole('super_administrator');

    $category = Category::factory()->create();

    $response = $this
        ->postJson('/api/admin/menu-items', [
            'category_id' => $category->id,
            'name' => ['en' => 'Burger', 'ar' => 'برجر'],
            'slug' => 'burger',
            'description' => ['en' => 'Delicious burger', 'ar' => 'برجر لذيذ'],
            'price' => 15.0,
            'is_available' => true,
            'stock_quantity' => 20,
        ]);

    $this->assertCreatedResponse($response, [
        'data' => [
            'id',
            'name',
            'slug',
            'description',
            'price',
            'is_available',
        ],
        'message',
    ]);

    $this->assertDatabaseHas('menu_items', [
        'slug' => 'burger',
        'price' => 15.0,
    ]);
});

test('update menu item successfully', function () {
    $this->actingAsRole('super_administrator');

    [
        'category' => $category,
        'menuItem' => $menuItem
    ] = $this->createCategoryWithMenuItem();

    $response = $this
        ->putJson("/api/admin/menu-items/{$menuItem->id}", [
            'name' => ['en' => 'Updated Burger', 'ar' => 'برجر محدث'],
            'price' => 18,
            'is_available' => false,
        ]);

    $this->assertResourceResponse($response, [
        'data.id' => $menuItem->id,
        'data.name' => 'Updated Burger',
        'data.price' => '18.00',
        'data.is_available' => false,
    ]);

    $menuItem->refresh();
    expect($menuItem->price)->toBe('18.00');
    expect($menuItem->is_available)->toBeFalse();
});

test('delete menu item successfully', function () {
    $this->actingAsRole('super_administrator');

    [
        'category' => $category,
        'menuItem' => $menuItem
    ] = $this->createCategoryWithMenuItem();

    $response = $this
        ->deleteJson("/api/admin/menu-items/{$menuItem->id}");

    $this->assertSuccessResponse($response);

    $this->assertSoftDeleted('menu_items', [
        'id' => $menuItem->id,
    ]);
});

test('hide unavailable menu items from non-admin users', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    [
        'category' => $category,
        'menuItems' => $menuItems,
    ] = $this->createCategoryWithCustomMenuItems([], [
        [
            'is_available' => true,
        ],
        [
            'is_available' => false,
        ],
    ]);

    $response = $this
        ->getJson('/api/public/menu-items');

    $this->assertSuccessResponse($response);
    $response->assertJsonCount(1, 'data');
});

test('admin can see unavailable menu items', function () {
    $this->actingAsRole('super_administrator');

    [
        'category' => $category,
        'menuItems' => $menuItems,
    ] = $this->createCategoryWithCustomMenuItems([], [
        [
            'is_available' => true,
        ],
        [
            'is_available' => false,
        ],
    ]);

    $response = $this
        ->getJson('/api/public/menu-items');

    $this->assertSuccessResponse($response);
    $response->assertJsonCount(2, 'data');
});

test('search menu items by name in English', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    [
        'category' => $category,
        'menuItems' => $menuItems,
    ] = $this->createCategoryWithCustomMenuItems([], [
        [
            'name' => ['en' => 'Cheese Burger', 'ar' => 'برجر جبن'],
            'is_available' => true,
        ],
        [
            'name' => ['en' => 'Chicken Burger', 'ar' => 'برجر دجاج'],
            'is_available' => true,
        ],
        [
            'name' => ['en' => 'Pizza', 'ar' => 'بيتزا'],
            'is_available' => true,
        ],
    ]);

    $response = $this
        ->getJson('/api/public/menu-items?search=Burger');

    $this->assertSuccessResponse($response);
    $response->assertJsonCount(2, 'data');
});

test('search menu items by name in Arabic', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    [
        'category' => $category,
        'menuItems' => $menuItems,
    ] = $this->createCategoryWithCustomMenuItems([], [
        [
            'name' => ['en' => 'Cheese Burger', 'ar' => 'برجر جبن'],
            'is_available' => true,
        ],
        [
            'name' => ['en' => 'Chicken Burger', 'ar' => 'برجر دجاج'],
            'is_available' => true,
        ],
        [
            'name' => ['en' => 'Pizza', 'ar' => 'بيتزا'],
            'is_available' => true,
        ],
    ]);

    $response = $this
        ->getJson('/api/public/menu-items?search=برجر');

    $this->assertSuccessResponse($response);
    $response->assertJsonCount(2, 'data');
});

test('filter menu items by category', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    [
        'category' => $category1,
        'menuItem' => $menuItem1
    ] = $this->createCategoryWithMenuItem([], [
        'is_available' => true,
    ]);

    [
        'category' => $category2,
        'menuItem' => $menuItem2
    ] = $this->createCategoryWithMenuItem([], [
        'is_available' => true,
    ]);

    $response = $this
        ->getJson("/api/public/menu-items?category_id={$category1->id}");

    $this->assertSuccessResponse($response);
    $response->assertJsonCount(2, 'data');
});

test('filter menu items by availability', function () {
    $this->actingAsRole('super_administrator');

    [
        'category' => $category,
        'menuItems' => $menuItems
    ] = $this->createCategoryWithCustomMenuItems([], [
        [
            'is_available' => true,
        ],
        [
            'is_available' => true,
        ],
        [
            'is_available' => true,
        ],
        [
            'is_available' => false,
        ],
        [
            'is_available' => false,
        ],
    ]);

    $response = $this
        ->getJson('/api/public/menu-items?is_available=1');

    $this->assertSuccessResponse($response);
    $response->assertJsonCount(3, 'data');
});

test('filter menu items by date range', function () {
    $this->actingAsRole('super_administrator');

    [
        'category' => $category,
        'menuItems' => $menuItems
    ] = $this->createCategoryWithCustomMenuItems([], [
        [
            'created_at' => now()->subDays(2),
        ],
        [
            'created_at' => now()->subDays(1),
        ],
        [
            'created_at' => now()->addDays(1),
        ],
    ]);

    $response = $this
        ->getJson('/api/public/menu-items?from=' . now()->subDays(3)->format('Y-m-d') . '&to=' . now()->format('Y-m-d'));

    $this->assertSuccessResponse($response);
    $response->assertJsonCount(3, 'data');
});

test('menu item validation requires category_id', function () {
    $this->actingAsRole('super_administrator');

    $response = $this
        ->postJson('/api/admin/menu-items', [
            'name' => ['en' => 'Test Item'],
            'price' => 10.0,
        ]);

    $this->assertValidationErrorResponse($response, ['category_id']);
});

test('menu item validation requires name', function () {
    $this->actingAsRole('super_administrator');

    $category = Category::factory()->create();

    $response = $this
        ->postJson('/api/admin/menu-items', [
            'category_id' => $category->id,
            'price' => 10.0,
        ]);

    $this->assertValidationErrorResponse($response, ['name']);
});

test('menu item validation requires price', function () {
    $this->actingAsRole('super_administrator');

    $category = Category::factory()->create();

    $response = $this
        ->postJson('/api/admin/menu-items', [
            'category_id' => $category->id,
            'name' => ['en' => 'Test Item'],
        ]);

    $this->assertValidationErrorResponse($response, ['price']);
});

test('menu item validation requires valid price', function () {
    $this->actingAsRole('super_administrator');

    $category = Category::factory()->create();

    $response = $this
        ->postJson('/api/admin/menu-items', [
            'category_id' => $category->id,
            'name' => ['en' => 'Test Item'],
            'price' => -10.0,
        ]);

    $this->assertValidationErrorResponse($response, ['price']);
});

test('menu item slug must be unique', function () {
    $this->actingAsRole('super_administrator');

    [
        'category' => $category,
        'menuItem' => $menuItem
    ] =
        $this->createCategoryWithMenuItem([], [
            'slug' => 'burger',
        ]);

    $response = $this
        ->postJson('/api/admin/menu-items', [
            'category_id' => $category->id,
            'name' => ['en' => 'Test Item'],
            'slug' => 'burger',
            'price' => 10.0,
            'stock_quantity' => 10,
        ]);

    $this->assertValidationErrorResponse($response, ['slug']);
});

test('get single menu item', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    [
        'category' => $category,
        'menuItem' => $menuItem
    ] = $this->createCategoryWithMenuItem([], [
        'is_available' => true,
    ]);

    $response = $this
        ->getJson("/api/public/menu-items/{$menuItem->id}");

    $this->assertResourceResponse($response, [
        'data.id' => $menuItem->id,
        'data.name' => $menuItem->name,
    ]);
});

test('menu item belongs to category', function () {
    [
        'category' => $category,
        'menuItem' => $menuItem
    ] = $this->createCategoryWithMenuItem();
    expect($menuItem->category->id)->toBe($category->id);
});

test('menu item has many order items', function () {
    [
        'category' => $category,
        'menuItem' => $menuItem
    ] = $this->createCategoryWithMenuItem();
    OrderItem::factory()->count(3)->create([
        'menu_item_id' => $menuItem->id,
    ]);

    expect($menuItem->orderItems()->count())->toBe(3);
});

test('menu item has many inventory movements', function () {
    [
        'category' => $category,
        'menuItem' => $menuItem
    ] = $this->createCategoryWithMenuItem();
    InventoryMovement::factory()->count(3)->create([
        'menu_item_id' => $menuItem->id,
    ]);

    expect($menuItem->movements()->count())->toBe(3);
});

test('scope available returns only available items', function () {
    [
        'category' => $category,
        'menuItems' => $menuItems
    ] = $this->createCategoryWithCustomMenuItems([], [
        [
            'is_available' => true
        ],
        [
            'is_available' => false
        ]
    ]);

    $availableItems = MenuItem::available()->get();

    expect($availableItems)->toHaveCount(1);
});

test('menu item with zero stock is still available if is_available is true', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    ['category' => $category,
            'menuItems' => $menuItems] = $this->createCategoryWithCustomMenuItems([], [
        [
            'is_available' => true,
            'stock_quantity' => 0
        ]
    ]);

    $response = $this
        ->getJson('/api/public/menu-items');

    $this->assertSuccessResponse($response);
    $response->assertJsonCount(1, 'data');
});

test('menu item translations work correctly', function () {
    [
        'category' => $category,
        'menuItem' => $menuItem
    ] =
        $this->createCategoryWithMenuItem([],
            [
                'name' => ['en' => 'Burger', 'ar' => 'برجر'],
                'description' => ['en' => 'Tasty burger', 'ar' => 'برجر شهي'],
            ]);

    expect($menuItem->getTranslation('name', 'en'))->toBe('Burger');
    expect($menuItem->getTranslation('name', 'ar'))->toBe('برجر');
});
