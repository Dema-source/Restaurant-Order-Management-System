<?php

use App\Models\Category;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\User;

test('percentage discount calculates correctly', function () {
    $discount = $this->createActiveDiscount([
        'discount_type' => 'percentage',
        'discount_value' => 20,
    ]);

    $subtotal = 100.0;
    $discountAmount = $discount->calculateDiscountAmount($subtotal);

    expect($discountAmount)->toBe(20.0);
});

test('fixed discount calculates correctly', function () {
    $discount = $this->createActiveDiscount([
        'discount_type' => 'fixed',
        'discount_value' => 15,
    ]);

    $subtotal = 100.0;
    $discountAmount = $discount->calculateDiscountAmount($subtotal);

    expect($discountAmount)->toBe(15.0);
});

test('expired discount is not valid', function () {
    $discount = $this->createActiveDiscount([
        'start_date' => now()->subDays(10),
        'end_date' => now()->subDays(1),
    ]);

    expect($discount->isValid())->toBeFalse();
});

test('future discount is not valid', function () {
    $discount = $this->createActiveDiscount([
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(10),
    ]);

    expect($discount->isValid())->toBeFalse();
});

test('active discount within date range is valid', function () {
    $discount = $this->createActiveDiscount([
        'start_date' => now()->subDays(1),
        'end_date' => now()->addDays(1),
    ]);

    expect($discount->isValid())->toBeTrue();
});

test('inactive discount is not valid', function () {
    $discount = $this->createInactiveDiscount([
        'start_date' => now()->subDays(1),
        'end_date' => now()->addDays(1),
    ]);

    expect($discount->isValid())->toBeFalse();
});

test('discount with wrong weekday is not valid', function () {
    $discount = $this->createActiveDiscount([
        'weekday' => 'Monday',
    ]);

    // Set current day to Tuesday
    $this->travelTo(now()->next('Monday')->addDay());

    expect($discount->isValid())->toBeFalse();

    $this->travelBack();
});

test('discount with correct weekday is valid', function () {
    $discount = $this->createActiveDiscount([
        'weekday' => now()->format('l'),
    ]);

    expect($discount->isValid())->toBeTrue();
});

test('discount is eligible for sufficient order amount', function () {
    $discount = $this->createActiveDiscount([
        'minimum_order_amount' => 50.0,
    ]);

    expect($discount->isEligible(100.0))->toBeTrue();
});

test('discount is not eligible for insufficient order amount', function () {
    $discount = $this->createActiveDiscount([
        'minimum_order_amount' => 50.0,
    ]);

    expect($discount->isEligible(30.0))->toBeFalse();
});

test('find best discount selects highest value', function () {
    $this->createActiveDiscount([
        'name' => 'Small Discount',
        'discount_type' => 'percentage',
        'discount_value' => 10,
    ]);
    $this->createActiveDiscount([
        'name' => 'Large Discount',
        'discount_type' => 'percentage',
        'discount_value' => 25,
        'minimum_order_amount' => null,
    ]);

    $service = app(\App\Services\DiscountService::class);
    $bestDiscount = $service->findBestDiscount(100.0);

    expect($bestDiscount['discount_amount'])->toBe(25.0);
});

test('find best discount ignores expired discounts', function () {
    $expired = $this->createActiveDiscount([
        'name' => 'Expired Discount',
        'discount_type' => 'percentage',
        'discount_value' => 50,
        'start_date' => now()->subDays(10),
        'end_date' => now()->subDays(1),
    ]);

    $active = $this->createActiveDiscount([
        'name' => 'Active Discount',
        'discount_type' => 'percentage',
        'discount_value' => 10,
        'start_date' => now()->subDays(1),
        'end_date' => now()->addDays(1),
    ]);

    $service = app(\App\Services\DiscountService::class);
    $bestDiscount = $service->findBestDiscount(100.0);

    expect($bestDiscount)->not->toBeNull();
    expect((float) $bestDiscount['discount_amount'])->toBe(10.0);
});

test('find best discount ignores future discounts', function () {
    $this->createActiveDiscount([
        'name' => 'Future Discount',
        'discount_type' => 'percentage',
        'discount_value' => 50,
        'start_date' => now()->addDay(),
        'end_date' => now()->addDays(10),
        'minimum_order_amount' => null,
    ]);

    $this->createActiveDiscount([
        'name' => 'Active Discount',
        'discount_type' => 'percentage',
        'discount_value' => 10,
        'minimum_order_amount' => null,
    ]);

    $service = app(\App\Services\DiscountService::class);
    $bestDiscount = $service->findBestDiscount(100.0);

    expect((float) $bestDiscount['discount_amount'])->toBe(10.0);
});

test('create discount successfully', function () {
    $this->actingAsRole('super_administrator');

    $response = $this
        ->postJson('/api/admin/discounts', [
            'name' => 'Test Discount',
            'discount_type' => 'percentage',
            'discount_value' => 15,
            'minimum_order_amount' => 50.0,
            'is_active' => true,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(30)->format('Y-m-d'),
        ]);

    $this->assertCreatedResponse($response, [
        'data' => [
            'id',
            'name',
            'discount_type',
            'discount_value',
            'minimum_order_amount',
            'is_active',
            'start_date',
            'end_date',
        ],
        'message',
    ]);

    $this->assertDatabaseHas('discounts', [
        'name' => 'Test Discount',
        'discount_type' => 'percentage',
        'discount_value' => 15,
    ]);
});

test('update discount successfully', function () {
    $this->actingAsRole('super_administrator');

    $discount = Discount::factory()->create();

    $response = $this
        ->putJson("/api/admin/discounts/{$discount->id}", [
            'name' => 'Updated Discount',
            'discount_value' => 20,
        ]);

    $this->assertSuccessResponse($response);

    $discount->refresh();
    expect($discount->name)->toBe('Updated Discount');
    expect((float) $discount->discount_value)->toBe(20.0);
});

test('delete discount successfully', function () {
    $this->actingAsRole('super_administrator');

    $discount = Discount::factory()->create();

    $response = $this
        ->deleteJson("/api/admin/discounts/{$discount->id}");

    $this->assertSuccessResponse($response);

    $this->assertSoftDeleted('discounts', [
        'id' => $discount->id,
    ]);
});

test('get discounts list', function () {
    $this->actingAsRole('super_administrator');

    Discount::factory()->count(3)->create();

    $response = $this
        ->getJson('/api/admin/discounts');

    $this->assertPaginatedResponse($response, 3);
});

test('filter discounts by type', function () {
    $this->actingAsRole('super_administrator');

    Discount::factory()->create(['discount_type' => 'percentage']);
    Discount::factory()->create(['discount_type' => 'percentage']);
    Discount::factory()->create(['discount_type' => 'fixed']);

    $response = $this
        ->getJson('/api/admin/discounts?discount_type=percentage');

    $this->assertPaginatedResponse($response, 2);
});

test('filter discounts by weekday', function () {
    $this->actingAsRole('super_administrator');

    Discount::factory()->create(['weekday' => 'Monday']);
    Discount::factory()->create(['weekday' => 'Monday']);
    Discount::factory()->create(['weekday' => 'Tuesday']);

    $response = $this
        ->getJson('/api/admin/discounts?weekday=Monday');

    $this->assertPaginatedResponse($response, 2);
});

test('search discounts by name', function () {
    $this->actingAsRole('super_administrator');

    Discount::factory()->create(['name' => 'Summer Sale']);
    Discount::factory()->create(['name' => 'Summer Special']);
    Discount::factory()->create(['name' => 'Winter Sale']);

    $response = $this
        ->getJson('/api/admin/discounts?search=Summer');

    $this->assertPaginatedResponse($response, 2);
});

test('filter discounts by date range', function () {
    $this->actingAsRole('super_administrator');

    Discount::factory()->create(['created_at' => now()->subDays(2)]);
    Discount::factory()->create(['created_at' => now()->subDays(1)]);
    Discount::factory()->create(['created_at' => now()->addDays(1)]);

    $response = $this
        ->getJson('/api/admin/discounts?from=' . now()->subDays(3)->format('Y-m-d') . '&to=' . now()->format('Y-m-d'));

    $this->assertPaginatedResponse($response, 3);
});

test('discount validation requires name', function () {
    $this->actingAsRole('super_administrator');

    $response = $this->postJson('/api/admin/discounts', [
        'discount_type' => 'percentage',
        'discount_value' => 10,
    ]);

    $this->assertValidationErrorResponse($response, ['name']);
});

test('discount validation requires discount_type', function () {
    $this->actingAsRole('super_administrator');

    $response = $this->postJson('/api/admin/discounts', [
        'name' => 'Test Discount',
        'discount_value' => 10,
    ]);

    $this->assertValidationErrorResponse($response, ['discount_type']);
});

test('discount validation requires discount_value', function () {
    $this->actingAsRole('super_administrator');

    $response = $this->postJson('/api/admin/discounts', [
        'name' => 'Test Discount',
        'discount_type' => 'percentage',
    ]);

    $this->assertValidationErrorResponse($response, ['discount_value']);
});

test('discount validation requires valid discount_type', function () {
    $this->actingAsRole('super_administrator');

    $response = $this->postJson('/api/admin/discounts', [
        'name' => 'Test Discount',
        'discount_type' => 'invalid',
        'discount_value' => 10,
    ]);

    $this->assertValidationErrorResponse($response, ['discount_type']);
});

test('discount without minimum order amount is eligible for any amount', function () {
    $discount = $this->createActiveDiscount([
        'minimum_order_amount' => null,
    ]);

    expect($discount->isEligible(5.0))->toBeTrue();
});

test('discount with null dates is always valid if active', function () {
    $discount = $this->createActiveDiscount([
        'start_date' => null,
        'end_date' => null,
    ]);

    expect($discount->isValid())->toBeTrue();
});

test('scope current returns only current discounts', function () {
    $this->createActiveDiscount([
        'start_date' => now()->subDay(),
        'end_date' => now()->addDay(),
    ]);

    $this->createActiveDiscount([
        'start_date' => now()->subDay(),
        'end_date' => now()->subHours(1),
    ]);

    $this->createActiveDiscount([
        'start_date' => now()->addDay(),
        'end_date' => now()->addDays(2),
    ]);

    $currentDiscounts = Discount::current()->get();

    expect($currentDiscounts)->toHaveCount(1);
});

test('scope active returns only active discounts', function () {
    Discount::factory()->create(['is_active' => true]);
    Discount::factory()->create(['is_active' => true]);
    Discount::factory()->create(['is_active' => false]);

    $activeDiscounts = Discount::active()->get();

    expect($activeDiscounts)->toHaveCount(2);
});

test('percentage discount cannot exceed 100', function () {
    $this->actingAsRole('super_administrator');

    $response = $this->postJson('/api/admin/discounts', [
        'name' => 'Invalid Discount',
        'discount_type' => 'percentage',
        'discount_value' => 150,
        'is_active' => true,
    ]);

    $this->assertValidationErrorResponse($response, ['discount_value']);
});

test('fixed discount cannot be negative', function () {
    $this->actingAsRole('super_administrator');

    $response = $this->postJson('/api/admin/discounts', [
        'name' => 'Invalid Discount',
        'discount_type' => 'fixed',
        'discount_value' => -10,
        'is_active' => true,
    ]);

    $this->assertValidationErrorResponse($response, ['discount_value']);
});
