  <?php

  use App\Http\Controllers\Api\Admin\CategoryController;
  use App\Http\Controllers\Api\Admin\MenuItemController;
  use App\Http\Controllers\Api\Admin\UserController;
  use App\Http\Controllers\Api\Admin\DiscountController as AdminDiscountController;
  use App\Http\Controllers\Api\DiscountController;
  use App\Http\Controllers\Api\InventoryMovementController;
  use App\Http\Controllers\Api\RolesPermissions\RoleController;
  use Illuminate\Support\Facades\Route;

  /*
  |--------------------------------------------------------------------------
  | Roles & Permissions - Full Access
  |--------------------------------------------------------------------------
  */
  // API: {{baseURL}}/api/admin/roles
  // Route::apiResource('roles', RoleController::class);

  /*
  |--------------------------------------------------------------------------
  | User - Full Access
  |--------------------------------------------------------------------------
  */
  // API: {{baseURL}}/api/admin/users
  Route::apiResource('users', UserController::class);

  /*
  |--------------------------------------------------------------------------
  | Category - Full Access
  |--------------------------------------------------------------------------
  */
  // API: {{baseURL}}/api/admin/categories
  Route::patch('categories/{id}/toggle-active', [CategoryController::class, 'toggleActive']);
  Route::apiResource('categories', CategoryController::class)->only(['store', 'update', 'destroy']);

  /*
  |--------------------------------------------------------------------------
  | MenuItem - Full Access
  |--------------------------------------------------------------------------
  */
  // API: {{baseURL}}/api/admin/menu-items
  Route::patch('menu-items/{id}/toggle-available', [MenuItemController::class, 'toggleAvailable']);
  Route::apiResource('menu-items', MenuItemController::class)->only(['store', 'update', 'destroy']);

  /*
  |--------------------------------------------------------------------------
  | Discount - Full Access
  |--------------------------------------------------------------------------
  */
  // API: {{baseURL}}/api/admin/discounts
  Route::prefix('discounts')->group(function () {
    Route::post('/{id}/duplicate', [AdminDiscountController::class, 'duplicate']);
    Route::patch('/{id}/toggle-active', [AdminDiscountController::class, 'toggleActive']);
    Route::apiResource('/', AdminDiscountController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('/', DiscountController::class)->only(['index', 'show']);
  });

  /*
  |--------------------------------------------------------------------------
  | Inventory Movement - Full Access
  |--------------------------------------------------------------------------
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
