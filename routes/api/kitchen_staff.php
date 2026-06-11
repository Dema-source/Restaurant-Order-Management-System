  <?php

  use App\Http\Controllers\Api\OrderController;
  use Illuminate\Support\Facades\Route;

  /*
   * |--------------------------------------------------------------------------
   * | Orders
   * |--------------------------------------------------------------------------
   */
  // API: {{baseURL}}/api/cashier/orders
  Route::apiResource('orders', OrderController::class)->only(['index', 'show']);

  // Additional order routes
  Route::prefix('orders')->group(function () {
    Route::get('/status/{status}', [OrderController::class, 'getByStatus']);
    Route::patch('/{id}/status', [OrderController::class, 'updateStatus']);
  });
