<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PriceHistoryController;
use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
    });
});

// Products API
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::get('/{product}/price-history', [PriceHistoryController::class, 'index']);
});

// Alerts API
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('alerts')->group(function () {
        Route::get('/', [AlertController::class, 'index']);
        Route::post('/', [AlertController::class, 'store']);
        Route::get('/statistics', [AlertController::class, 'statistics']);

        Route::middleware('alert.owner')->group(function () {
            Route::get('/{alert}', [AlertController::class, 'show']);
            Route::put('/{alert}', [AlertController::class, 'update']);
            Route::delete('/{alert}', [AlertController::class, 'destroy']);
            Route::get('/{alert}/should-trigger', [AlertController::class, 'shouldTrigger']);
            Route::post('/{alert}/trigger', [AlertController::class, 'trigger']);
        });
    });
});
