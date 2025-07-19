<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Test endpoints
Route::get('/test/dbs', [App\Http\Controllers\TestController::class, 'testDbs']);
Route::get('/test/rabbitmq', [App\Http\Controllers\TestController::class, 'testRabbitMQ']);
Route::get('/test/kafka', [App\Http\Controllers\TestController::class, 'testKafka']);
Route::get('/test/ms', [App\Http\Controllers\TestController::class, 'testMicroserviceConnection']);

require 'api_v1.php';

// Public authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

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
