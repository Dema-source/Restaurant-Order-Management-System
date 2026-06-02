  <?php

  use App\Http\Controllers\Api\Admin\CategoryController;
  use App\Http\Controllers\Api\Admin\UserController;
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
  Route::apiResource('categories',CategoryController::class)->only(['store', 'update','destroy']);
