<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\SelcomWebhookController;
use Livewire\Volt\Volt;

// API routes (for Flutter app) – loaded here so /api/* is always available
Route::prefix('api')->middleware('api')->group(base_path('routes/api.php'));

Route::view('/', 'welcome')->name('welcome');

// Selcom Checkout webhook (no auth; CSRF excluded in bootstrap/app.php)
$selcomPrefix = config('selcom.prefix', 'selcom');
Route::post("{$selcomPrefix}/checkout-callback", SelcomWebhookController::class)->name('selcom.checkout-callback');
Route::get('/product/{product}', [App\Http\Controllers\PublicProductController::class , 'show'])->name('product.show');
Route::get('/category/{category}', [App\Http\Controllers\PublicCategoryController::class , 'show'])->name('category.show');

Route::get('dashboard', function () {
    if (auth()->user()->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    if (auth()->user()->role === 'agent') {
        return redirect()->route('agent.dashboard');
    }
    return view('dashboard');
})->middleware(['auth', 'verified', 'active'])->name('dashboard');

Route::middleware('guest')->group(function () {
    Volt::route('register/dealer', 'pages.auth.dealer-register')->name('dealer.register');
    Route::get('register/dealer/pending', [App\Http\Controllers\DealerRegisterController::class , 'pending'])->name('dealer.pending');
    Volt::route('register/agent', 'pages.auth.agent-register')->name('agent.register');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Run whitelisted artisan command: GET /command/{command} (admin only)
Route::get('command/{command}', App\Http\Controllers\Admin\ArtisanCommandController::class)
    ->middleware(['auth', 'admin'])
    ->where('command', '[a-zA-Z0-9:_-]+')
    ->name('command.run');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', function () {
            $totalCustomers = \App\Models\User::where('role', 'customer')->count();
            $totalOrders = \App\Models\Order::count();
            $totalProducts = \App\Models\Product::count();
            $recentOrders = \App\Models\Order::with('user')->latest()->take(5)->get();
            $financialMetrics = app(\App\Services\DashboardFinancialService::class)->getMetrics();
            return view('admin.dashboard', compact('totalCustomers', 'totalOrders', 'totalProducts', 'recentOrders', 'financialMetrics'));
        }
        )->name('dashboard');
        Route::resource('products', ProductController::class);
        Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class);

        // Dealers Management
        Route::get('dealers', [App\Http\Controllers\Admin\DealerController::class , 'index'])->name('dealers.index');
        Route::get('dealers/{user}', [App\Http\Controllers\Admin\DealerController::class , 'show'])->name('dealers.show');
        Route::patch('dealers/{user}/approve', [App\Http\Controllers\Admin\DealerController::class , 'approve'])->name('dealers.approve');
        Route::patch('dealers/{user}/reject', [App\Http\Controllers\Admin\DealerController::class , 'reject'])->name('dealers.reject');

        // Agents Management
        Route::get('agents', [App\Http\Controllers\Admin\AgentController::class, 'index'])->name('agents.index');
        Route::get('agents/create', [App\Http\Controllers\Admin\AgentController::class, 'create'])->name('agents.create');
        Route::post('agents', [App\Http\Controllers\Admin\AgentController::class, 'store'])->name('agents.store');
        Route::get('agents/assign-products', [App\Http\Controllers\Admin\AgentController::class, 'assignProductsForm'])->name('agents.assign-products');
        Route::post('agents/assign-products', [App\Http\Controllers\Admin\AgentController::class, 'storeAssignment'])->name('agents.store-assignment');
        Route::get('agents/{agent}', [App\Http\Controllers\Admin\AgentController::class, 'show'])->name('agents.show');

        // Orders
        Route::resource('orders', App\Http\Controllers\Admin\OrderController::class)->only(['index', 'show', 'update']);

        // Customers
        Route::get('customers', [App\Http\Controllers\Admin\CustomerController::class , 'index'])->name('customers.index');

        // Settings
        Route::get('settings', [App\Http\Controllers\Admin\SettingController::class , 'index'])->name('settings.index');
        Route::post('settings', [App\Http\Controllers\Admin\SettingController::class , 'update'])->name('settings.update');

        // Run whitelisted artisan command: GET /admin/command/{command}
        Route::get('command/{command}', App\Http\Controllers\Admin\ArtisanCommandController::class)
            ->where('command', '[a-zA-Z0-9:_-]+')
            ->name('command.run');

        // Reports
        Route::get('reports', [App\Http\Controllers\Admin\ReportController::class , 'index'])->name('reports.index');

        // Expenses
        Route::resource('expenses', App\Http\Controllers\Admin\ExpenseController::class)->except(['show']);

        // Stock Management
        Route::prefix('stock')->name('stock.')->group(function () {
            Route::get('purchases', [App\Http\Controllers\Admin\StockController::class , 'purchases'])->name('purchases');
            Route::get('purchases/create', [App\Http\Controllers\Admin\StockController::class , 'createPurchase'])->name('create-purchase');
            Route::post('purchases', [App\Http\Controllers\Admin\StockController::class , 'storePurchase'])->name('store-purchase');
            Route::get('purchases/{id}/edit', [App\Http\Controllers\Admin\StockController::class , 'editPurchase'])->name('edit-purchase');
            Route::put('purchases/{id}', [App\Http\Controllers\Admin\StockController::class , 'updatePurchase'])->name('update-purchase');
            Route::delete('purchases/{id}', [App\Http\Controllers\Admin\StockController::class , 'destroyPurchase'])->name('destroy-purchase');
            
            // Distribution Sales
            Route::get('distribution', [App\Http\Controllers\Admin\StockController::class , 'distribution'])->name('distribution');
            Route::get('distribution/create', [App\Http\Controllers\Admin\StockController::class, 'createDistribution'])->name('create-distribution');
            Route::post('distribution', [App\Http\Controllers\Admin\StockController::class, 'storeDistribution'])->name('store-distribution');
            Route::get('distribution/{id}/edit', [App\Http\Controllers\Admin\StockController::class, 'editDistribution'])->name('edit-distribution');
            Route::put('distribution/{id}', [App\Http\Controllers\Admin\StockController::class, 'updateDistribution'])->name('update-distribution');
            Route::patch('distribution/{id}/status', [App\Http\Controllers\Admin\StockController::class, 'updateDistributionStatus'])->name('distribution-update-status');

            // Agent Sales
            Route::get('agent-sales', [App\Http\Controllers\Admin\StockController::class , 'agentSales'])->name('agent-sales');
            Route::get('agent-sales/create', [App\Http\Controllers\Admin\StockController::class, 'createAgentSale'])->name('create-agent-sale');
            Route::post('agent-sales', [App\Http\Controllers\Admin\StockController::class, 'storeAgentSale'])->name('store-agent-sale');
            Route::patch('agent-sales/{id}/commission', [App\Http\Controllers\Admin\StockController::class, 'updateAgentSaleCommission'])->name('agent-sales-update-commission');

            Route::get('shop-records', [App\Http\Controllers\Admin\StockController::class , 'shopRecords'])->name('shop-records');
            Route::get('payables', [App\Http\Controllers\Admin\StockController::class , 'payables'])->name('payables');
        }
        );

        // System Helpers (for cPanel/Shared Hosting) - creates public/storage dir (no symlink needed)
        Route::get('system/storage-link', function () {
            if (!auth()->check() || auth()->user()->role !== 'admin') {
                abort(403);
            }

            $storageDir = public_path('storage');
            $legacyDir = storage_path('app/public');

            try {
                // If it's a symlink, remove it so we can use a real directory
                if (is_link($storageDir)) {
                    unlink($storageDir);
                }
                if (!is_dir($storageDir)) {
                    \Illuminate\Support\Facades\File::makeDirectory($storageDir, 0755, true);
                    \Illuminate\Support\Facades\File::put($storageDir . '/.gitignore', "*\n!.gitignore\n");
                }
                // Ensure subdirs exist for uploads
                foreach (['products', 'categories'] as $sub) {
                    $path = $storageDir . '/' . $sub;
                    if (!is_dir($path)) {
                        \Illuminate\Support\Facades\File::makeDirectory($path, 0755, true);
                    }
                }
                // Migrate existing files from storage/app/public if present
                if (is_dir($legacyDir)) {
                    foreach (['products', 'categories'] as $sub) {
                        $src = $legacyDir . '/' . $sub;
                        $dst = $storageDir . '/' . $sub;
                        if (is_dir($src)) {
                            $files = glob($src . '/*');
                            foreach ($files as $file) {
                                if (is_file($file)) {
                                    $dest = $dst . '/' . basename($file);
                                    if (!file_exists($dest)) {
                                        if (!is_dir($dst)) {
                                            \Illuminate\Support\Facades\File::makeDirectory($dst, 0755, true);
                                        }
                                        copy($file, $dest);
                                    }
                                }
                            }
                        }
                    }
                }
                return 'Storage directory ready. Uploads are stored in public/storage (no symlink). <a href="' . route('admin.dashboard') . '">Back to Dashboard</a>';
            } catch (\Exception $e) {
                return '<div class="p-6 max-w-2xl"><strong>Error:</strong> ' . e($e->getMessage()) . '<p class="mt-4"><a href="' . route('admin.dashboard') . '">Back to Dashboard</a></p></div>';
            }
        }
        )->name('system.storage-link');
    });

// Agent dashboard and sales (role = agent)
Route::middleware(['auth', 'verified', 'active', 'agent'])->prefix('agent')->name('agent.')->group(function () {
    Route::get('dashboard', [App\Http\Controllers\AgentController::class, 'dashboard'])->name('dashboard');
    Route::get('assignments/{assignment}/record-sale', [App\Http\Controllers\AgentController::class, 'recordSaleForm'])->name('record-sale-form');
    Route::post('record-sale', [App\Http\Controllers\AgentController::class, 'recordSale'])->name('record-sale');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/cart', [App\Http\Controllers\CartController::class , 'index'])->name('cart.index');
    Route::post('/cart', [App\Http\Controllers\CartController::class , 'store'])->name('cart.store');
    Route::patch('/cart/{item}', [App\Http\Controllers\CartController::class , 'update'])->name('cart.update');
    Route::delete('/cart/{item}', [App\Http\Controllers\CartController::class , 'destroy'])->name('cart.destroy');

    Route::get('/orders', [App\Http\Controllers\OrderController::class , 'index'])->name('orders.index');
    Route::get('/checkout', [App\Http\Controllers\OrderController::class , 'create'])->name('checkout.create');
    Route::post('/checkout', [App\Http\Controllers\OrderController::class , 'store'])->name('checkout.store');

    Route::get('checkout/pay/{order}', [App\Http\Controllers\SelcomController::class , 'pay'])->name('selcom.pay');
    Route::get('checkout/status/{order}', [App\Http\Controllers\SelcomController::class , 'checkStatus'])->name('selcom.status');
    Route::resource('addresses', App\Http\Controllers\AddressController::class);
});

require __DIR__ . '/auth.php';