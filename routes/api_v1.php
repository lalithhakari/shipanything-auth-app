<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;

Route::prefix('v1/auth')->name('api.v1.')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::get('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');

    // Internal route for NGINX auth_request (no middleware needed)
    Route::post('/validate-token', [AuthController::class, 'validateToken']);
});

// Protected routes (require authentication)
Route::middleware('auth:api')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/refresh', [AuthController::class, 'refreshToken']);
    });
});
