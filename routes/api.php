<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PriceHistoryController;
use App\Http\Controllers\Api\AlertController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Products API
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::get('/{product}/price-history', [PriceHistoryController::class, 'index']);
});

// Alerts API
Route::prefix('alerts')->group(function () {
    Route::get('/', [AlertController::class, 'index']);
    Route::post('/', [AlertController::class, 'store']);
    Route::get('/statistics', [AlertController::class, 'statistics']);
    Route::get('/{alert}', [AlertController::class, 'show']);
    Route::put('/{alert}', [AlertController::class, 'update']);
    Route::delete('/{alert}', [AlertController::class, 'destroy']);
    Route::get('/{alert}/should-trigger', [AlertController::class, 'shouldTrigger']);
    Route::post('/{alert}/trigger', [AlertController::class, 'trigger']);
});
