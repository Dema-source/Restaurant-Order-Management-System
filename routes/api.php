<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Core Module: Auth, Roles
require __DIR__ . '/api/core.php';

Route::middleware('auth:sanctum')->group(function () {

    // Super Administrator routes - full access
    Route::prefix('admin')->middleware('role:super_administrator')->group(function () {
        require __DIR__ . '/api/super_administrator.php';
    });

    // kitchen Staff - Limited access
    Route::prefix('kitchen')->middleware(['role:Kitchen_staff'])->group(function () {
        require __DIR__ . '/api/kitchen_staff.php';
    });

    // Cashier - Limited access
    Route::prefix('cashier')->middleware(['role:Cashier'])->group(function () {
        require __DIR__ . '/api/cashier.php';
    });

    // Public: For Authenticated users
    Route::prefix('public')->group(function () {
        require __DIR__ . '/api/public.php';
    });
});
