  <?php

  use App\Http\Controllers\Api\Admin\CategoryController;
  use App\Http\Controllers\Api\Admin\DiscountController as AdminDiscountController;
  use App\Http\Controllers\Api\Admin\MenuItemController;
  use App\Http\Controllers\Api\Admin\UserController;
  use App\Http\Controllers\Api\RolesPermissions\RoleController;
  use App\Http\Controllers\Api\CustomerController;
  use App\Http\Controllers\Api\DiscountController;
  use App\Http\Controllers\Api\InventoryMovementController;
  use App\Http\Controllers\Api\OrderController;
  use App\Http\Controllers\Api\OrderStatusLogController;
  use Illuminate\Support\Facades\Route;

  /*
   * |--------------------------------------------------------------------------
   * | Roles & Permissions - Full Access
   * |--------------------------------------------------------------------------
   */
  // API: {{baseURL}}/api/admin/roles
  // Route::apiResource('roles', RoleController::class);

  /*
   * |--------------------------------------------------------------------------
   * | User - Full Access
   * |--------------------------------------------------------------------------
   */
  // API: {{baseURL}}/api/admin/users
  Route::apiResource('users', UserController::class);

  /*
   * |--------------------------------------------------------------------------
   * | Category - Full Access
   * |--------------------------------------------------------------------------
   */
  // API: {{baseURL}}/api/admin/categories
  Route::patch('categories/{id}/toggle-active', [CategoryController::class, 'toggleActive']);
  Route::apiResource('categories', CategoryController::class)->only(['store', 'update', 'destroy']);

  /*
   * |--------------------------------------------------------------------------
   * | MenuItem - Full Access
   * |--------------------------------------------------------------------------
   */
  // API: {{baseURL}}/api/admin/menu-items
  Route::patch('menu-items/{id}/toggle-available', [MenuItemController::class, 'toggleAvailable']);
  Route::apiResource('menu-items', MenuItemController::class)->only(['store', 'update', 'destroy']);

  /*
   * |--------------------------------------------------------------------------
   * | Discount - Full Access
   * |--------------------------------------------------------------------------
   */
  // API: {{baseURL}}/api/admin/discounts
  Route::apiResource('discounts', DiscountController::class)->only(['index', 'show']);
  Route::post('discounts/{id}/duplicate', [AdminDiscountController::class, 'duplicate']);
  Route::patch('discounts/{id}/toggle-active', [AdminDiscountController::class, 'toggleActive']);
  Route::apiResource('discounts', AdminDiscountController::class)->only(['store', 'update', 'destroy']);

  /*
   * |--------------------------------------------------------------------------
   * | Inventory Movement - Full Access
   * |--------------------------------------------------------------------------
   */
  // API: {{baseURL}}/api/admin/inventories
  Route::prefix('inventories')->group(function () {
    // Stock management (admin can restock, waste, adjust)
    Route::post('/restock', [InventoryMovementController::class, 'restock']);
    Route::post('/waste', [InventoryMovementController::class, 'waste']);
    Route::post('/adjustment', [InventoryMovementController::class, 'adjustment']);

    // Stock queries (specific routes must come before dynamic {id})
    Route::get('/stock-level/{menuItemId}', [InventoryMovementController::class, 'stockLevel']);
    Route::get('/check-availability/{menuItemId}', [InventoryMovementController::class, 'checkAvailability']);
    Route::get('/movements-by-date-range', [InventoryMovementController::class, 'movementsByDateRange']);
    Route::get('/low-stock-items', [InventoryMovementController::class, 'lowStockItems']);
    Route::get('/waste-report', [InventoryMovementController::class, 'wasteReport']);

    // View movements
    Route::get('/', [InventoryMovementController::class, 'index']);
    Route::get('/{id}', [InventoryMovementController::class, 'show']);
  });

  /*
   * |--------------------------------------------------------------------------
   * | Customers - Full Access
   * |--------------------------------------------------------------------------
   */
  // API: {{baseURL}}/api/admin/customers
  Route::apiResource('customers', CustomerController::class);

  // Find customer by phone
  Route::get('/customers/phone/{phone}', [CustomerController::class, 'findByPhone']);

  /*
   * |--------------------------------------------------------------------------
   * | Orders - Full Access
   * |--------------------------------------------------------------------------
   */
  // API: {{baseURL}}/api/admin/orders
  Route::apiResource('orders', OrderController::class);

  // Additional order routes
  Route::prefix('orders')->group(function () {
    Route::get('/customer/{customerId}', [OrderController::class, 'getByCustomer']);
    Route::get('/status/{status}', [OrderController::class, 'getByStatus']);
    Route::patch('/{id}/status', [OrderController::class, 'updateStatus']);
  });

  /*
   * |--------------------------------------------------------------------------
   * | Order Status Logs - Full Access
   * |--------------------------------------------------------------------------
   */
  // API: {{baseURL}}/api/admin/order-status-logs
  Route::apiResource('order-status-logs', OrderStatusLogController::class)->only(['index', 'show']);

  // Get status logs for a specific order
  Route::get('/order-status-logs/order/{orderId}', [OrderStatusLogController::class, 'getLogsByOrder']);
