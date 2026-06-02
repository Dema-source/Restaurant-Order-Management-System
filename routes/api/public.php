    <?php

    use App\Http\Controllers\Api\CategoryController;
    use Illuminate\Support\Facades\Route;

    /*
    |--------------------------------------------------------------------------
    | Category 
    |--------------------------------------------------------------------------
    */
    // API: {{baseURL}}/api/public/categories
    Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
