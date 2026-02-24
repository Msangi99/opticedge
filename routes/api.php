<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StockController as ApiStockController;
use App\Http\Controllers\Api\PurchaseController as ApiPurchaseController;
use App\Http\Controllers\Api\ProductListController;
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user()->only(['id', 'name', 'email', 'role']);
    });

    // Admin: stocks (with limit), create stock, add product to product_list
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('stocks', [ApiStockController::class, 'index']);
        Route::post('stocks', [ApiStockController::class, 'store']);
        Route::get('stocks/under-limit', [ApiStockController::class, 'stocksUnderLimit']);
        Route::get('stocks/{id}/models', [ApiStockController::class, 'modelsForStock']);
        Route::get('purchases/for-add-product', [ApiPurchaseController::class, 'forAddProduct']);
        Route::get('categories', [ApiCategoryController::class, 'index']);
        Route::post('product-list', [ProductListController::class, 'store']);
    });

    // Agent: get device by IMEI, record sale (deduct from product_list)
    Route::middleware('agent')->prefix('agent')->group(function () {
        Route::get('product-list/by-imei/{imei}', [ProductListController::class, 'showByImei']);
        Route::post('sell', [ProductListController::class, 'sell']);
    });
});
