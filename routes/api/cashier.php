  <?php

  use App\Http\Controllers\Api\DiscountController;
  use Illuminate\Support\Facades\Route;

  /*
    |--------------------------------------------------------------------------
    | Discount
    |--------------------------------------------------------------------------
    */
  // API: {{baseURL}}/api/cashier/discounts
  Route::apiResource('discounts', DiscountController::class)->only(['index', 'show']);
