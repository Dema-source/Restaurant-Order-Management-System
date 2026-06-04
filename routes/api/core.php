<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

// Authentication - Public routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

// Protected routes (require authentication)
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Authentication - Protected routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
    });
    
    // Users
    Route::prefix('user')->group(function () {
        Route::get('me', [UserController::class, 'me']);
        Route::post('change-password', [UserController::class, 'changePassword']);
        Route::post('change-password/{id}', [UserController::class, 'changePassword'])->middleware('role:super_administrator');
    });

    // Roles 
    Route::prefix('roles')->name('roles.')->group(function () {
        // Implement RoleController endpoints
        // Route::apiResource('/', RoleController::class);
    });
});
