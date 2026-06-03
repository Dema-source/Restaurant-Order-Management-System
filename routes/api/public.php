    <?php

    use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MenuItemController;
use Illuminate\Support\Facades\Route;

    /*
    |--------------------------------------------------------------------------
    | Category 
    |--------------------------------------------------------------------------
    */
    // API: {{baseURL}}/api/public/categories
    Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);

    /*
    |--------------------------------------------------------------------------
    | MenuItem
    |--------------------------------------------------------------------------
    */
    // API: {{baseURL}}/api/public/menu-items
    Route::apiResource('menu-items', MenuItemController::class)->only(['index', 'show']);
