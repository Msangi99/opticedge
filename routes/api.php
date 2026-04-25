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
use App\Http\Controllers\Api\BarcodeDecodeController;
use App\Http\Controllers\Api\SettingController as ApiSettingController;
use App\Http\Controllers\Api\AgentCreditApiController;
use App\Http\Controllers\Api\AgentProductTransferApiController;
use App\Http\Controllers\Api\AdminAgentProductTransferApiController;
use App\Http\Controllers\Api\AdminBranchTransferApiController;
use App\Http\Controllers\Api\AgentCatalogController;
use App\Http\Controllers\Api\AgentCustomerNeedController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user()->only(['id', 'name', 'email', 'role']);
    });

    // Admin: stocks (with limit), create stock, add product to product_list
    Route::middleware(['admin', 'subadmin.ability'])->prefix('admin')->group(function () {
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
        Route::post('product-list/batch', [ProductListController::class, 'batchStore']);
        Route::post('barcodes/decode-image', [BarcodeDecodeController::class, 'decodeImage']);
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

        Route::get('agent-transfers', [AdminAgentProductTransferApiController::class, 'index']);
        Route::get('agent-transfers/{agent_product_transfer}', [AdminAgentProductTransferApiController::class, 'show']);
        Route::post('agent-transfers/{agent_product_transfer}/approve', [AdminAgentProductTransferApiController::class, 'approve']);
        Route::post('agent-transfers/{agent_product_transfer}/reject', [AdminAgentProductTransferApiController::class, 'reject']);

        Route::get('branch-transfer/items', [AdminBranchTransferApiController::class, 'items']);
        Route::post('branch-transfer', [AdminBranchTransferApiController::class, 'store']);
        Route::get('branch-transfer/logs', [AdminBranchTransferApiController::class, 'logs']);
    });

    // Agent: dashboard, available products (unsold only), get device by IMEI, record sale
    Route::middleware('agent')->prefix('agent')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Api\AgentDashboardController::class, 'index']);
        Route::get('dashboard/inventory', [\App\Http\Controllers\Api\AgentDashboardController::class, 'inventory']);
        Route::get('product-list/available', [ProductListController::class, 'available']);
        Route::get('product-list/by-imei/{imei}', [ProductListController::class, 'showByImei']);
        Route::get('payment-options', [ApiPaymentOptionController::class, 'indexVisible']);
        Route::get('sale-config', [ApiPaymentOptionController::class, 'agentSaleConfig']);
        Route::post('sell', [ProductListController::class, 'sell']);
        Route::post('sell-credit', [ProductListController::class, 'sellCredit']);
        Route::get('catalog/categories', [AgentCatalogController::class, 'categories']);
        Route::get('catalog/categories/{category}/products', [AgentCatalogController::class, 'productsByCategory']);
        Route::get('branches', [ApiBranchController::class, 'index']);
        Route::post('customer-needs', [AgentCustomerNeedController::class, 'store']);
        Route::get('credits', [AgentCreditApiController::class, 'index']);
        Route::post('credits/{id}/pay', [AgentCreditApiController::class, 'payInstallment']);
        Route::get('credits/{id}/invoice', [AgentCreditApiController::class, 'downloadInvoice']);
        Route::get('sales/{id}/invoice', [\App\Http\Controllers\Api\AgentDashboardController::class, 'downloadSaleInvoice']);

        Route::get('transfer-recipients', [AgentProductTransferApiController::class, 'transferRecipients']);
        Route::get('transferable-imeis', [AgentProductTransferApiController::class, 'transferableImeis']);
        Route::get('transfers', [AgentProductTransferApiController::class, 'index']);
        Route::post('transfers', [AgentProductTransferApiController::class, 'store']);
        Route::post('transfers/{agent_product_transfer}/cancel', [AgentProductTransferApiController::class, 'cancel']);
    });
});
