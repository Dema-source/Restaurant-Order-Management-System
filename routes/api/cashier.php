  <?php

  use App\Http\Controllers\Api\CustomerController;
  use App\Http\Controllers\Api\DiscountController;
  use App\Http\Controllers\Api\InventoryMovementController;
  use App\Http\Controllers\Api\OrderController;
  use Illuminate\Support\Facades\Route;

  /*
    |--------------------------------------------------------------------------
    | Discount
    |--------------------------------------------------------------------------
    */
  // API: {{baseURL}}/api/cashier/discounts
  Route::apiResource('discounts', DiscountController::class)->only(['index', 'show']);

  /*
    |--------------------------------------------------------------------------
    | Inventory Movement
    |--------------------------------------------------------------------------
    */
  // API: {{baseURL}}/api/cashier/inventories
  Route::prefix('inventories')->group(function () {
    // View movements
    Route::get('/', [InventoryMovementController::class, 'index']);

    // Stock management (cashier can restock, waste, adjust)
    Route::post('/restock', [InventoryMovementController::class, 'restock']);
    Route::post('/waste', [InventoryMovementController::class, 'waste']);
    Route::post('/adjustment', [InventoryMovementController::class, 'adjustment']);

    // Stock queries (specific routes must come before dynamic {id})
    Route::get('/stock-level/{menuItemId}', [InventoryMovementController::class, 'stockLevel']);
    Route::get('/check-availability/{menuItemId}', [InventoryMovementController::class, 'checkAvailability']);
    Route::get('/movements-by-date-range', [InventoryMovementController::class, 'movementsByDateRange']);
    Route::get('/low-stock-items', [InventoryMovementController::class, 'lowStockItems']);
    Route::get('/waste-report', [InventoryMovementController::class, 'wasteReport']);

    // Dynamic route must come last
    Route::get('/{id}', [InventoryMovementController::class, 'show']);
  });

  /*
  |--------------------------------------------------------------------------
  | Orders
  |--------------------------------------------------------------------------
  */
  // API: {{baseURL}}/api/cashier/orders
  Route::apiResource('orders', OrderController::class)->except(['destroy']);

  // Additional order routes
  Route::prefix('orders')->group(function () {
    Route::get('/customer/{customerId}', [OrderController::class, 'getByCustomer']);
    Route::get('/status/{status}', [OrderController::class, 'getByStatus']);
    Route::patch('/{id}/status', [OrderController::class, 'updateStatus']);
  });

  /*
  |--------------------------------------------------------------------------
  | Customers
  |--------------------------------------------------------------------------
  */
  // API: {{baseURL}}/api/cashier/customers
  Route::apiResource('customers', CustomerController::class)->only(['index', 'show', 'store', 'update']);

  // Find customer by phone
  Route::get('/customers/phone/{phone}', [CustomerController::class, 'findByPhone']);
