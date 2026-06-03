  <?php

  use App\Http\Controllers\Api\Admin\CategoryController;
  use App\Http\Controllers\Api\Admin\MenuItemController;
  use App\Http\Controllers\Api\Admin\UserController;
  use App\Http\Controllers\Api\Admin\DiscountController as AdminDiscountController;
  use App\Http\Controllers\Api\DiscountController;
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
  Route::post('discounts/{id}/duplicate', [AdminDiscountController::class, 'duplicate']);
  Route::patch('discounts/{id}/toggle-active', [AdminDiscountController::class, 'toggleActive']);
  Route::apiResource('discounts', AdminDiscountController::class)->only(['store', 'update', 'destroy']);
  Route::apiResource('discounts', DiscountController::class)->only(['index', 'show']);
