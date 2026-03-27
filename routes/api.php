<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StockController as ApiStockController;
use App\Http\Controllers\Api\PurchaseController as ApiPurchaseController;
use App\Http\Controllers\Api\ProductListController;
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExpenseController as ApiExpenseController;
use App\Http\Controllers\Api\PaymentOptionController as ApiPaymentOptionController;
use App\Http\Controllers\Api\AgentSaleController as ApiAgentSaleController;
use App\Http\Controllers\Api\OrderController as ApiOrderController;
use App\Http\Controllers\Api\UserController as ApiUserController;
use App\Http\Controllers\Api\DistributionSaleController as ApiDistributionSaleController;
use App\Http\Controllers\Api\PendingSaleController as ApiPendingSaleController;
use App\Http\Controllers\Api\BranchController as ApiBranchController;
use App\Http\Controllers\Api\ReportController as ApiReportController;
use App\Http\Controllers\Api\SettingController as ApiSettingController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user()->only(['id', 'name', 'email', 'role']);
    });

    // Admin: stocks (with limit), create stock, add product to product_list
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index']);
        Route::get('stocks', [ApiStockController::class, 'index']);
        Route::post('stocks', [ApiStockController::class, 'store']);
        Route::get('stocks/under-limit', [ApiStockController::class, 'stocksUnderLimit']);
        Route::get('stocks/{id}/models', [ApiStockController::class, 'modelsForStock']);
        Route::get('branches', [ApiBranchController::class, 'index']);
        Route::get('purchases', [ApiPurchaseController::class, 'index']);
        Route::get('purchases/for-add-product', [ApiPurchaseController::class, 'forAddProduct']);
        Route::get('purchases/{id}/items', [ApiPurchaseController::class, 'items']);
        Route::get('categories', [ApiCategoryController::class, 'index']);
        Route::post('product-list', [ProductListController::class, 'store']);
        Route::get('expenses', [ApiExpenseController::class, 'index']);
        Route::get('payment-options', [ApiPaymentOptionController::class, 'index']);
        Route::get('agent-sales', [ApiAgentSaleController::class, 'index']);
        Route::get('orders', [ApiOrderController::class, 'index']);
        Route::get('users', [ApiUserController::class, 'index']); // ?role=customer|dealer|agent
        Route::get('distribution-sales', [ApiDistributionSaleController::class, 'index']);
        Route::get('pending-sales', [ApiPendingSaleController::class, 'index']);
        Route::get('reports', [ApiReportController::class, 'index']);
        Route::get('settings', [ApiSettingController::class, 'index']);
        Route::put('settings', [ApiSettingController::class, 'update']);
    });

    // Agent: dashboard, available products (unsold only), get device by IMEI, record sale
    Route::middleware('agent')->prefix('agent')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Api\AgentDashboardController::class, 'index']);
        Route::get('product-list/available', [ProductListController::class, 'available']);
        Route::get('product-list/by-imei/{imei}', [ProductListController::class, 'showByImei']);
        Route::post('sell', [ProductListController::class, 'sell']);
    });
});
