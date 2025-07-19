<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

require 'api_v1.php';

// Test endpoints
Route::get('/test/dbs', [App\Http\Controllers\TestController::class, 'testDbs']);
Route::get('/test/rabbitmq', [App\Http\Controllers\TestController::class, 'testRabbitMQ']);
Route::get('/test/kafka', [App\Http\Controllers\TestController::class, 'testKafka']);
Route::get('/test/ms', [App\Http\Controllers\TestController::class, 'testMicroserviceConnection']);
