<?php

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('create category successfully', function () {
    $this->actingAsRole('super_administrator');

    $response = $this
        ->postJson('/api/admin/categories', [
            'name' => ['en' => 'Burgers', 'ar' => 'برجر'],
            'slug' => 'burgers',
            'description' => ['en' => 'Delicious burgers', 'ar' => 'برجر لذيذ'],
            'is_active' => true,
        ]);

    $this->assertCreatedResponse($response, [
        'data' => [
            'id',
            'name',
            'slug',
            'description',
            'is_active',
        ],
        'message',
    ]);

    // Ensure the database contains a record matching the expected attributes.
    $this->assertDatabaseHas('categories', [
        'slug' => 'burgers',
        'is_active' => true,
    ]);
});

test('read category successfully', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $category = Category::factory()->create(['is_active' => true]);

    $response = $this
        ->getJson("/api/public/categories/{$category->id}");

    $this->assertResourceResponse($response, [
        'data.id' => $category->id,
        'data.name' => $category->name,
    ]);
});

test('update category successfully', function () {
    $this->actingAsRole('super_administrator');

    $category = Category::factory()->create();

    $response = $this
        ->putJson("/api/admin/categories/{$category->id}", [
            'name' => ['en' => 'Updated Category', 'ar' => 'فئة محدثة'],
            'is_active' => false,
        ]);

    $this->assertResourceResponse($response, [
        'data.id' => $category->id,
        'data.is_active' => false,
    ]);

    $category->refresh();
    expect($category->is_active)->toBeFalse();
});

test('delete category successfully', function () {
    $this->actingAsRole('super_administrator');

    $category = Category::factory()->create();

    $response = $this
        ->deleteJson("/api/admin/categories/{$category->id}");

    $this->assertSuccessResponse($response);

    $this->assertSoftDeleted('categories', [
        'id' => $category->id,
    ]);
});

test('get categories list', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Category::factory()->count(5)->create(['is_active' => true]);

    $response = $this
        ->getJson('/api/public/categories');

    $this->assertPaginatedResponse($response, 5);
});

test('hide inactive categories from non-admin users', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Category::factory()->create(['is_active' => true]);
    Category::factory()->create(['is_active' => false]);

    $response = $this
        ->getJson('/api/public/categories');

    $this->assertPaginatedResponse($response, 1);
});

test('admin can see inactive categories', function () {
    $this->actingAsRole('super_administrator');

    Category::factory()->create(['is_active' => true]);
    Category::factory()->create(['is_active' => false]);

    $response = $this
        ->getJson('/api/public/categories');

    $this->assertPaginatedResponse($response, 2);
});

test('search categories by name in English', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Category::factory()->create([
        'name' => ['en' => 'Burgers', 'ar' => 'برجر'],
        'is_active' => true,
    ]);
    Category::factory()->create([
        'name' => ['en' => 'Pizza', 'ar' => 'بيتزا'],
        'is_active' => true,
    ]);
    Category::factory()->create([
        'name' => ['en' => 'Drinks', 'ar' => 'مشروبات'],
        'is_active' => true,
    ]);

    $response = $this
        ->getJson('/api/public/categories?search=Burgers');

    $this->assertPaginatedResponse($response, 1);
});

test('search categories by name in Arabic', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    Category::factory()->create([
        'name' => ['en' => 'Burgers', 'ar' => 'برجر'],
        'is_active' => true,
    ]);
    Category::factory()->create([
        'name' => ['en' => 'Pizza', 'ar' => 'بيتزا'],
        'is_active' => true,
    ]);
    Category::factory()->create([
        'name' => ['en' => 'Drinks', 'ar' => 'مشروبات'],
        'is_active' => true,
    ]);

    $response = $this
        ->getJson('/api/public/categories?search=برجر');

    $this->assertPaginatedResponse($response, 1);
});

test('filter categories by availability', function () {
    $this->actingAsRole('super_administrator');

    Category::factory()->count(3)->create(['is_active' => true]);
    Category::factory()->count(2)->create(['is_active' => false]);

    $response = $this
        ->getJson('/api/public/categories?is_active=1');

    $this->assertPaginatedResponse($response, 3);
});

test('filter categories by date range', function () {
    $this->actingAsRole('super_administrator');

    Category::factory()->create(['created_at' => now()->subDays(2)]);
    Category::factory()->create(['created_at' => now()->subDays(1)]);
    Category::factory()->create(['created_at' => now()->addDays(1)]);

    $response = $this
        ->getJson('/api/public/categories?from=' . now()->subDays(3)->format('Y-m-d') . '&to=' . now()->format('Y-m-d'));

    $this->assertPaginatedResponse($response, 3);
});

test('category validation requires name', function () {
    $this->actingAsRole('super_administrator');

    $response = $this
        ->postJson('/api/admin/categories', [
            'slug' => 'test-category',
        ]);

    $this->assertValidationErrorResponse($response, ['name']);
});

test('category validation requires slug', function () {
    $this->actingAsRole('super_administrator');

    $response = $this
        ->postJson('/api/admin/categories', [
            'name' => ['en' => 'Test Category'],
        ]);

    $this->assertValidationErrorResponse($response, ['slug']);
});

test('category slug must be unique', function () {
    $this->actingAsRole('super_administrator');

    Category::factory()->create(['slug' => 'burgers']);

    $response = $this
        ->postJson('/api/admin/categories', [
            'name' => ['en' => 'Test Category'],
            'slug' => 'burgers',
        ]);

    $this->assertValidationErrorResponse($response, ['slug']);
});

test('category has many menu items', function () {
    [
        'category' => $category,
        'menuItem' => $menuItem
    ] = $this->createCategoryWithMenuItems(3);

    expect($category->menu_items()->count())->toBe(3);
});

test('scope active returns only active categories', function () {
    Category::factory()->count(3)->create(['is_active' => true]);
    Category::factory()->count(2)->create(['is_active' => false]);

    $activeCategories = Category::active()->get();

    expect($activeCategories)->toHaveCount(3);
});

test('category translations work correctly', function () {
    $category = Category::factory()->create([
        'name' => ['en' => 'Burgers', 'ar' => 'برجر'],
        'description' => ['en' => 'Tasty burgers', 'ar' => 'برجر شهي'],
    ]);

    expect($category->getTranslation('name', 'en'))->toBe('Burgers');
    expect($category->getTranslation('name', 'ar'))->toBe('برجر');
});

test('delete category with menu items uses soft delete', function () {
    $this->actingAsRole('super_administrator');

    [
        'category' => $category,
        'menuItem' => $menuItem
    ] = $this->createCategoryWithMenuItems();

    $response = $this
        ->deleteJson("/api/admin/categories/{$category->id}");

    $this->assertSuccessResponse($response);

    $this->assertSoftDeleted('categories', [
        'id' => $category->id,
    ]);

    // Menu items should still exist
    $this->assertDatabaseHas('menu_items', [
        'category_id' => $category->id,
    ]);
});

test('get category with menu items', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    [
        'category' => $category,
        'menuItem' => $menuItem
    ] = $this->createCategoryWithMenuItems(
        3,
        $categoryOverrides = ['is_active' => true],
        $menuItemOverrides = ['is_available' => true]
    );

    $response = $this
        ->getJson("/api/public/categories/{$category->id}");

    $this->assertSuccessResponse($response);
});

test('category without description is valid', function () {
    $this->actingAsRole('super_administrator');

    $response = $this
        ->postJson('/api/admin/categories', [
            'name' => ['en' => 'Test Category'],
            'slug' => 'test-category',
            'is_active' => true,
        ]);

    // $response->assertStatus(201);
    $this->assertCreatedResponse($response, [
        'data' => [
            'id',
            'name',
            'slug',
            'description',
            'is_active',
        ],
        'message',
    ]);
});

test('update category without changing name', function () {
    $this->actingAsRole('super_administrator');

    $category = Category::factory()->create();

    $response = $this
        ->putJson("/api/admin/categories/{$category->id}", [
            'is_active' => false,
        ]);

    // $response->assertStatus(200);
    $this->assertSuccessResponse($response);

    $category->refresh();
    expect($category->is_active)->toBeFalse();
});
