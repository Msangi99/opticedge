<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\AgentSale;
use App\Models\DistributionSale;
use App\Models\PaymentOption;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class StockController extends Controller
{
    /**
     * Stocks page: list all stocks with stock quantity, added (from purchases), and status.
     */
    public function stocks()
    {
        try {
            // Get all stocks
            $stocks = Stock::orderBy('name')->get();
            
            $stocksData = $stocks->map(function ($stock) {
                try {
                    // Calculate added quantity from purchases for this stock
                    $added = (float) Purchase::where('stock_id', $stock->id)->sum('quantity');
                    if (is_null($added) || $added < 0) {
                        $added = 0;
                    }
                } catch (\Exception $e) {
                    Log::warning('Error calculating added quantity for stock ' . $stock->id . ': ' . $e->getMessage());
                    $added = 0;
                }
                
                $stockQuantity = (int) ($stock->stock_limit ?? 0);
                $status = ($stockQuantity > 0 && $stockQuantity == $added) ? 'complete' : 'pending';
                
                return (object) [
                    'id' => $stock->id,
                    'name' => $stock->name ?? 'Unnamed Stock',
                    'stock_quantity' => $stockQuantity,
                    'added' => (int) $added,
                    'status' => $status,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error loading stocks: ' . $e->getMessage());
            $stocksData = collect([]);
        }

        return view('admin.stock.stocks', ['stocks' => $stocksData]);
    }

    /**
     * Show items for one purchase: model, category, IMEI (product_list rows for this purchase).
     */
    public function showPurchase($id)
    {
        $purchase = Purchase::findOrFail($id);
        $items = $purchase->productListItems()
            ->with('category:id,name')
            ->orderBy('model')
            ->orderBy('imei_number')
            ->get();

        return view('admin.stock.purchase-show', [
            'purchase' => $purchase,
            'items' => $items,
        ]);
    }

    /**
     * Show devices (product list items) for a stock: model and IMEI.
     */
    public function showStock(Stock $stock)
    {
        $stock->load(['productListItems' => function ($q) {
            $q->with(['category', 'product'])->orderBy('model')->orderBy('imei_number');
        }]);

        $available = $stock->productListItems->whereNull('sold_at')->count();
        $atLimit = $available >= $stock->stock_limit;

        return view('admin.stock.stock-show', compact('stock', 'atLimit'));
    }

    public function purchases(Request $request)
    {
        $query = Purchase::with(['product', 'stock']);
        
        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }
        
        $purchases = $query->latest('date')->get();
        return view('admin.stock.purchases', compact('purchases'));
    }

    /**
     * View all payment receipts for all purchases.
     */
    public function viewAllReceipts()
    {
        $purchases = Purchase::with(['product', 'stock'])
            ->whereNotNull('payment_receipt_image')
            ->latest('date')
            ->get();
        
        return view('admin.stock.all-receipts', compact('purchases'));
    }

    /**
     * View payment receipts for a specific stock.
     */
    public function viewStockReceipts(Stock $stock)
    {
        $purchases = Purchase::with(['product'])
            ->where('stock_id', $stock->id)
            ->whereNotNull('payment_receipt_image')
            ->latest('date')
            ->get();
        
        return view('admin.stock.stock-receipts', compact('stock', 'purchases'));
    }

    public function distribution(Request $request)
    {
        $query = DistributionSale::with(['product.category', 'dealer', 'paymentOption']);
        
        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }
        
        $distributionSales = $query->latest('date')->get();
        $bankPaymentOptions = PaymentOption::visible()->bank()->orderBy('name')->get();
        return view('admin.stock.distribution', compact('distributionSales', 'bankPaymentOptions'));
    }

    /**
     * Save payment channel (bank only) for a pending distribution sale. Amount is added to the selected bank option balance.
     */
    public function saveDistributionChannel(Request $request, $id)
    {
        $sale = DistributionSale::findOrFail($id);
        $st = $sale->status ?? 'pending';
        if ($st !== 'pending') {
            return redirect()->route('admin.stock.distribution')->with('error', 'Only pending distribution sales can have channel updated.');
        }

        $validated = $request->validate([
            'payment_option_id' => 'required|exists:payment_options,id',
        ]);

        $option = PaymentOption::findOrFail($validated['payment_option_id']);
        if ($option->type !== PaymentOption::TYPE_BANK) {
            return redirect()->route('admin.stock.distribution')->with('error', 'Only bank channels are allowed for dealer sales.');
        }

        $sale->update([
            'payment_option_id' => $option->id,
            'status' => 'complete'
        ]);
        $amount = (float) ($sale->total_selling_value ?? 0);
        if ($amount > 0) {
            $option->increment('balance', $amount);
        }

        return redirect()->route('admin.stock.distribution')->with('success', 'Channel saved. Status updated to complete. Amount added to ' . $option->name . '.');
    }

    public function updateDistributionStatus($id)
    {
        $sale = DistributionSale::findOrFail($id);
        $sale->update(['status' => 'complete']);
        return redirect()->route('admin.stock.distribution')->with('success', 'Distribution sale marked as complete.');
    }

    public function agentSales(Request $request)
    {
        $query = AgentSale::with(['product.category', 'agent', 'paymentOption']);
        
        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }
        
        $agentSales = $query->latest('date')->get();
        $paymentOptions = PaymentOption::visible()->orderBy('name')->get();
        return view('admin.stock.agent-sales', compact('agentSales', 'paymentOptions'));
    }

    public function saveAgentSaleChannel(Request $request, $id)
    {
        $sale = AgentSale::findOrFail($id);

        $validated = $request->validate([
            'payment_option_id' => 'required|exists:payment_options,id',
        ]);

        $option = PaymentOption::findOrFail($validated['payment_option_id']);
        $sale->update(['payment_option_id' => $option->id]);
        
        $amount = (float) ($sale->total_selling_value ?? 0);
        if ($amount > 0) {
            $option->increment('balance', $amount);
        }

        return redirect()->route('admin.stock.agent-sales')->with('success', 'Channel saved. Amount added to ' . $option->name . '.');
    }

    public function updateAgentSaleCommission(Request $request, $id)
    {
        $sale = AgentSale::findOrFail($id);
        $validated = $request->validate(['commission_paid' => 'required|numeric|min:0']);
        $sale->update($validated);
        return redirect()->route('admin.stock.agent-sales')->with('success', 'Commission updated.');
    }

    public function shopRecords()
    {
        $shopRecords = \App\Models\ShopRecord::with('product')->latest('date')->get();
        return view('admin.stock.shop-records', compact('shopRecords'));
    }

    public function payables()
    {
        $payables = \App\Models\Payable::latest('date')->get();
        return view('admin.stock.payables', compact('payables'));
    }

    /**
     * Form: scan IMEI, select stock (from pending purchases), select model from selected stock.
     */
    public function addProductForm()
    {
        $stocks = Stock::whereHas('purchases', function ($q) {
            $q->where('limit_status', 'pending')->where('limit_remaining', '>', 0);
        })->orderBy('name')->get(['id', 'name']);
        return view('admin.stock.add-product', compact('stocks'));
    }

    /**
     * JSON: distinct models (and category_id) for a stock (from product_list + purchases).
     */
    public function modelsForStock(Stock $stock)
    {
        $fromList = \App\Models\ProductListItem::where('stock_id', $stock->id)
            ->select('model', 'category_id')
            ->distinct()
            ->get()
            ->map(fn ($r) => ['model' => $r->model, 'category_id' => $r->category_id]);
        $fromPurchases = Purchase::where('stock_id', $stock->id)
            ->with('product:id,category_id,name')
            ->get()
            ->map(function ($p) {
                return $p->product ? ['model' => $p->product->name, 'category_id' => $p->product->category_id] : null;
            })
            ->filter()
            ->unique('model')
            ->values();
        $combined = $fromList->concat($fromPurchases)->unique('model')->values()->all();
        return response()->json(['data' => $combined]);
    }

    /**
     * Save one IMEI: stock_id, model, imei_number (same logic as API product-list store).
     */
    public function storeProductFromForm(Request $request)
    {
        $validated = $request->validate([
            'stock_id' => 'required|exists:stocks,id',
            'model' => 'required|string|max:255',
            'imei_number' => 'required|string|max:255|unique:product_list,imei_number',
            'category_id' => 'required|exists:categories,id',
        ]);

        $stock = Stock::findOrFail($validated['stock_id']);
        $purchase = Purchase::where('stock_id', $stock->id)
            ->where('limit_status', 'pending')
            ->where('limit_remaining', '>', 0)
            ->latest('date')->latest('id')->first();

        if (!$purchase) {
            return redirect()->route('admin.stock.add-product')
                ->withInput()
                ->withErrors(['stock_id' => 'No pending purchase limit for this stock.']);
        }

        // Use sell_price if available, otherwise use unit_price
        $productPrice = $purchase->sell_price ?? $purchase->unit_price ?? 0;
        $product = \App\Models\Product::firstOrCreate(
            [
                'category_id' => $validated['category_id'],
                'name' => $validated['model'],
            ],
            [
                'price' => (float) $productPrice,
                'stock_quantity' => 0,
                'rating' => 5.0,
                'description' => 'From product list',
                'images' => $purchase->product?->images ?? [],
            ]
        );
        
        // Update product price if sell_price is available
        if ($purchase->sell_price && $product->price != $purchase->sell_price) {
            $product->update(['price' => (float) $purchase->sell_price]);
        }

        \App\Models\ProductListItem::create([
            'stock_id' => $stock->id,
            'purchase_id' => $purchase->id,
            'category_id' => $validated['category_id'],
            'model' => $validated['model'],
            'imei_number' => $validated['imei_number'],
            'product_id' => $product->id,
        ]);

        $purchase->decrement('limit_remaining');
        if ($purchase->fresh()->limit_remaining <= 0) {
            $purchase->update(['limit_status' => 'complete']);
        }

        return redirect()->route('admin.stock.add-product')->with('success', 'Product (IMEI) added.');
    }

    public function createPurchase(Request $request)
    {
        $categories = \App\Models\Category::orderBy('name')->get();
        $distributors = Purchase::select('distributor_name')
            ->whereNotNull('distributor_name')
            ->distinct()
            ->pluck('distributor_name');

        $fromStock = null;
        if ($request->has('from_stock')) {
            $fromStock = Stock::with(['defaultCategory', 'productListItems' => fn ($q) => $q->with(['category', 'product'])->latest('id')->limit(1)])
                ->find($request->from_stock);

            if ($fromStock) {
                // Quantity = stock limit (total quantity for this purchase from stock)
                $fromStock->purchase_quantity = $fromStock->stock_limit;

                // Category and model: from product list items in this stock (as added in app), or fallback to stock defaults
                $firstItem = $fromStock->productListItems->first();
                if ($firstItem) {
                    $fromStock->purchase_category_id = $firstItem->category_id ?? $firstItem->product?->category_id;
                    $fromStock->purchase_category_name = $firstItem->category?->name ?? $firstItem->product?->category?->name ?? '–';
                    $fromStock->purchase_model = $firstItem->model ?? $firstItem->product?->name ?? '–';
                } else {
                    $fromStock->purchase_category_id = $fromStock->default_category_id;
                    $fromStock->purchase_category_name = $fromStock->defaultCategory?->name ?? '–';
                    $fromStock->purchase_model = $fromStock->default_model ?? '–';
                    if (!$fromStock->purchase_category_id || !$fromStock->purchase_model) {
                        return redirect()->route('admin.stock.stocks')
                            ->with('info', 'Add products to this stock in the app first. Then "Add via Purchases" will use that category and model.');
                    }
                }
            }
        }

        // Get all purchase images for gallery
        $purchaseImages = Purchase::with('product')
            ->whereHas('product', function ($q) {
                $q->whereNotNull('images');
            })
            ->get()
            ->flatMap(function ($purchase) {
                $product = $purchase->product;
                if (!$product || empty($product->images)) {
                    return [];
                }

                $images = is_string($product->images) ? json_decode($product->images, true) : $product->images;
                if (!is_array($images)) {
                    return [];
                }

                return collect($images)->map(function ($imagePath) use ($purchase, $product) {
                    return [
                        'id' => $purchase->id . '_' . md5($imagePath),
                        'purchase_id' => $purchase->id,
                        'purchase_name' => $purchase->name ?? 'Purchase #' . $purchase->id,
                        'product_name' => $product->name,
                        'image_path' => $imagePath,
                        'image_url' => asset('storage/' . $imagePath),
                    ];
                });
            })
            ->values()
            ->all();

        return view('admin.stock.create-purchase', compact('categories', 'distributors', 'fromStock', 'purchaseImages'));
    }

    public function storePurchase(Request $request)
    {
        $validated = $request->validate([
            'stock_id' => 'nullable|exists:stocks,id',
            'name' => 'nullable|string|max:255',
            'date' => 'required|date',
            'distributor_name' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'model' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'sell_price' => 'nullable|numeric|min:0',
            'paid_date' => 'nullable|date',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_option_id' => 'nullable|exists:payment_options,id',
            'selected_images' => 'nullable|array',
            'selected_images.*' => 'string|max:255',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'payment_receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        // Validate that at least 3 images are provided (either from gallery or upload)
        $selectedCount = count($request->input('selected_images', []));
        $uploadedCount = $request->hasFile('images') ? count($request->file('images')) : 0;
        $totalImages = $selectedCount + $uploadedCount;
        
        if ($totalImages < 3) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['images' => 'Please select at least 3 images from gallery or upload from device.']);
        }

        // Find or create the product based on category and model name
        // Use sell_price if available, otherwise use unit_price for initial product creation
        $productPrice = $validated['sell_price'] ?? $validated['unit_price'];
        $product = \App\Models\Product::firstOrCreate(
            [
                'category_id' => $validated['category_id'],
                'name' => $validated['model']
            ],
            [
                'price' => $productPrice,
                'stock_quantity' => 0,
                'rating' => 5.0,
                'description' => 'Auto-created from purchase',
                'images' => [],
            ]
        );
        
        // Note: Product price will be updated after purchase creation to use latest sell_price

        // Combine selected images from gallery and uploaded images
        $imagePaths = [];
        
        // Add selected images from gallery
        if ($request->has('selected_images') && is_array($request->selected_images)) {
            foreach ($request->selected_images as $selectedPath) {
                // Validate that the image path exists in storage
                if (Storage::disk('public')->exists($selectedPath)) {
                    $imagePaths[] = $selectedPath;
                }
            }
        }
        
        // Add uploaded images from device
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                if ($image->isValid()) {
                    $path = $image->store('products', 'public');
                    $imagePaths[] = $path;
                }
            }
        }
        
        if (!empty($imagePaths)) {
            $product->update(['images' => $imagePaths]);
        }

        $stockId = !empty($validated['stock_id']) ? (int) $validated['stock_id'] : null;
        $quantity = $validated['quantity'] ?? 0;

        // Remove non-purchase fields from validated data
        unset($validated['category_id']);
        unset($validated['model']);
        unset($validated['images']);
        unset($validated['stock_id']);

        // Add product_id and optional stock_id
        $validated['product_id'] = $product->id;
        if ($stockId) {
            $validated['stock_id'] = $stockId;
        }

        // Calculate total amount (backend validation/calculation)
        $validated['total_amount'] = $quantity * $validated['unit_price'];
        $paidAmount = (float) ($validated['paid_amount'] ?? 0);
        
        // Auto status from paid amount: pending / partial / paid (like in edit)
        $totalAmount = $validated['total_amount'];
        $paymentStatus = $paidAmount >= $totalAmount ? 'paid' : ($paidAmount > 0 ? 'partial' : 'pending');
        $validated['payment_status'] = $paymentStatus;
        $validated['paid_amount'] = $paidAmount;
        
        // Quantity = limit: track remaining; when app adds IMEIs, decrement until 0 then set complete
        $validated['limit_status'] = 'pending';
        $validated['limit_remaining'] = $quantity;
        $validated['sell_price'] = $request->filled('sell_price') ? $request->input('sell_price') : null;
        $validated['payment_option_id'] = $request->filled('payment_option_id') ? $request->input('payment_option_id') : null;

        // Handle payment option balance deduction if payment is made
        if ($paidAmount > 0 && $validated['payment_option_id']) {
            $paymentOption = PaymentOption::find($validated['payment_option_id']);
            if ($paymentOption) {
                if ($paymentOption->balance >= $paidAmount) {
                    $paymentOption->decrement('balance', $paidAmount);
                } else {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['payment_option_id' => 'Insufficient balance in selected payment channel.']);
                }
            }
        }

        // Create purchase first to get the ID
        $purchase = Purchase::create($validated);

        // Upload payment receipt image if provided (store in purchase-specific directory)
        if ($request->hasFile('payment_receipt_image')) {
            $receiptImage = $request->file('payment_receipt_image');
            if ($receiptImage->isValid()) {
                $receiptDir = 'receipts/purchase-' . $purchase->id;
                $paymentReceiptPath = $receiptImage->store($receiptDir, 'public');
                $purchase->update(['payment_receipt_image' => $paymentReceiptPath]);
            }
        }

        // Keep product.stock_quantity in sync so Category Management and dashboards show correct counts
        $product->increment('stock_quantity', $validated['quantity']);

        // Record initial payment in history if payment was made
        if ($paidAmount > 0 && $request->filled('payment_option_id')) {
            try {
                PurchasePayment::create([
                    'purchase_id' => $purchase->id,
                    'payment_option_id' => $request->input('payment_option_id'),
                    'amount' => $paidAmount,
                    'paid_date' => $validated['paid_date'] ?? now()->toDateString(),
                ]);
            } catch (\Exception $e) {
                // Table might not exist yet - migration needs to be run
                // Log error but don't fail the purchase creation
                Log::warning('Failed to create purchase payment record: ' . $e->getMessage());
            }
        }

        // Update product price to use the latest purchase's sell_price (if available)
        // This ensures front page products show the correct sell_price instead of unit_price
        $latestPurchase = Purchase::where('product_id', $product->id)
            ->whereNotNull('sell_price')
            ->latest('date')
            ->latest('id')
            ->first();
        
        if ($latestPurchase && $latestPurchase->sell_price) {
            $product->update(['price' => $latestPurchase->sell_price]);
        }

        return redirect()->route('admin.stock.purchases')->with('success', 'Purchase recorded successfully.');
    }

    public function editPurchase($id)
    {
        $purchase = Purchase::with(['product.category', 'payments.paymentOption'])->findOrFail($id);
        
        // Get all categories for the select dropdown
        $categories = \App\Models\Category::orderBy('name')->get();
            
        // Get unique distributors for the datalist
        $distributors = Purchase::select('distributor_name')
            ->whereNotNull('distributor_name')
            ->distinct()
            ->pluck('distributor_name');
        
        // Get payment options with balance for selection
        $paymentOptions = PaymentOption::visible()->orderBy('name')->get();
            
        return view('admin.stock.edit-purchase', compact('purchase', 'categories', 'distributors', 'paymentOptions'));
    }

    public function updatePurchase(Request $request, $id)
    {
        $purchase = Purchase::with('product')->findOrFail($id);

        $rules = [
            'name' => 'nullable|string|max:255',
            'paid_date' => 'nullable|date',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_option_id' => 'nullable|exists:payment_options,id',
            'payment_receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ];
        if ($request->hasFile('images')) {
            $rules['images'] = 'required|array|min:3';
            $rules['images.*'] = 'image|mimes:jpeg,png,jpg,gif,webp|max:5120';
        }
        $validated = $request->validate($rules);

        // Update product images if new ones uploaded
        if ($request->hasFile('images') && $purchase->product) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                if ($image->isValid()) {
                    $path = $image->store('products', 'public');
                    $imagePaths[] = $path;
                }
            }
            if (!empty($imagePaths)) {
                $purchase->product->update(['images' => $imagePaths]);
            }
        }

        // Upload payment receipt image if provided (store in purchase-specific directory)
        $paymentReceiptPath = $purchase->payment_receipt_image;
        if ($request->hasFile('payment_receipt_image')) {
            $receiptImage = $request->file('payment_receipt_image');
            if ($receiptImage->isValid()) {
                // Delete old receipt image if exists
                if ($purchase->payment_receipt_image && Storage::disk('public')->exists($purchase->payment_receipt_image)) {
                    Storage::disk('public')->delete($purchase->payment_receipt_image);
                }
                // Store in purchase-specific directory: receipts/purchase-{id}/
                $receiptDir = 'receipts/purchase-' . $purchase->id;
                $paymentReceiptPath = $receiptImage->store($receiptDir, 'public');
            }
        }

        // Auto status from paid amount: pending / partial / paid
        $totalAmount = $purchase->total_amount ?? ($purchase->quantity * $purchase->unit_price);
        $paidAmount = (float) ($validated['paid_amount'] ?? 0);
        
        // Ensure paid amount doesn't exceed total amount
        if ($paidAmount > $totalAmount) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['paid_amount' => 'Paid amount cannot exceed total purchase value.']);
        }
        
        $paymentStatus = $paidAmount >= $totalAmount ? 'paid' : ($paidAmount > 0 ? 'partial' : 'pending');
        
        // Handle payment option and balance deduction
        $oldPaymentOption = $purchase->payment_option_id;
        $oldPaidAmount = $purchase->paid_amount ?? 0;
        $newPaymentOptionId = $validated['payment_option_id'] ?? null;
        $newPaidDate = $validated['paid_date'] ?? null;
        
        // Calculate the difference in payment amount
        $paymentDifference = $paidAmount - $oldPaidAmount;
        
        // If payment option changed or paid amount changed, update balances
        if ($newPaymentOptionId && $paidAmount > 0) {
            $paymentOption = PaymentOption::find($newPaymentOptionId);
            if ($paymentOption) {
                // If there was a previous payment option, refund the old amount
                if ($oldPaymentOption && $oldPaymentOption != $newPaymentOptionId) {
                    $oldOption = PaymentOption::find($oldPaymentOption);
                    if ($oldOption) {
                        $oldOption->increment('balance', $oldPaidAmount);
                    }
                }
                
                // Deduct new amount from selected payment option
                if ($paymentOption->balance >= $paidAmount) {
                    $paymentOption->decrement('balance', $paidAmount);
                } else {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['payment_option_id' => 'Insufficient balance in selected payment channel.']);
                }
            }
        } elseif ($oldPaymentOption && (!$newPaymentOptionId || $paidAmount == 0)) {
            // If payment option removed or amount set to 0, refund to old option
            $oldOption = PaymentOption::find($oldPaymentOption);
            if ($oldOption) {
                $oldOption->increment('balance', $oldPaidAmount);
            }
        } elseif ($oldPaymentOption && $oldPaymentOption == $newPaymentOptionId && $paymentDifference != 0) {
            // Same payment option but amount changed - adjust balance
            $paymentOption = PaymentOption::find($newPaymentOptionId);
            if ($paymentOption) {
                if ($paymentDifference > 0) {
                    // Additional payment - deduct difference
                    if ($paymentOption->balance >= $paymentDifference) {
                        $paymentOption->decrement('balance', $paymentDifference);
                    } else {
                        return redirect()->back()
                            ->withInput()
                            ->withErrors(['paid_amount' => 'Insufficient balance in selected payment channel for additional payment.']);
                    }
                } else {
                    // Payment reduced - refund difference
                    $paymentOption->increment('balance', abs($paymentDifference));
                }
            }
        }

        $purchase->update([
            'name' => $validated['name'] ?? $purchase->name,
            'paid_date' => $newPaidDate,
            'paid_amount' => $paidAmount,
            'payment_status' => $paymentStatus,
            'payment_receipt_image' => $paymentReceiptPath,
            'payment_option_id' => $newPaymentOptionId,
        ]);

        // Record payment history if payment was made/changed
        if ($paidAmount > 0 && ($paymentDifference != 0 || $oldPaymentOption != $newPaymentOptionId)) {
            // If amount increased or payment option changed, create a new payment record
            if ($paymentDifference > 0) {
                try {
                    PurchasePayment::create([
                        'purchase_id' => $purchase->id,
                        'payment_option_id' => $newPaymentOptionId,
                        'amount' => $paymentDifference,
                        'paid_date' => $newPaidDate ?? now()->toDateString(),
                    ]);
                } catch (\Exception $e) {
                    // Table might not exist yet - migration needs to be run
                    // Log error but don't fail the purchase update
                    Log::warning('Failed to create purchase payment record: ' . $e->getMessage());
                }
            }
        }

        // Update product price to use the latest purchase's sell_price (if available)
        // This ensures front page products show the correct sell_price instead of unit_price
        if ($purchase->product) {
            $latestPurchase = Purchase::where('product_id', $purchase->product_id)
                ->whereNotNull('sell_price')
                ->latest('date')
                ->latest('id')
                ->first();
            
            if ($latestPurchase && $latestPurchase->sell_price) {
                $purchase->product->update(['price' => $latestPurchase->sell_price]);
            }
        }

        return redirect()->route('admin.stock.purchases')->with('success', 'Purchase updated successfully.');
    }

    public function destroyPurchase($id)
    {
        $purchase = Purchase::with('product')->findOrFail($id);
        $product = $purchase->product;
        $purchase->delete();
        // Keep product.stock_quantity in sync
        if ($product) {
            $product->update(['stock_quantity' => max(0, $product->stock_quantity - $purchase->quantity)]);
        }

        return redirect()->route('admin.stock.purchases')->with('success', 'Purchase deleted successfully.');
    }

    // Distribution Sales
    public function createDistribution()
    {
        // Fetch products that have been purchased at least once
        $products = \App\Models\Product::whereHas('purchases')->orderBy('name')->get();
        
        // Fetch dealers
        $dealers = \App\Models\User::where('role', 'dealer')->orderBy('name')->get();

        return view('admin.stock.create-distribution', compact('products', 'dealers'));
    }

    public function storeDistribution(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'dealer_id' => 'nullable|exists:users,id',
            'dealer_name' => 'nullable|string|max:255',
            'seller_name' => 'nullable|string|max:255',
            'product_id' => 'required|exists:products,id',
            'quantity_sold' => 'required|integer|min:1',
            'selling_price' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
        ]);

        $service = app(\App\Services\DistributionSaleService::class);
        $buyPrice = $service->getBuyPriceForProduct($validated['product_id']); // Now uses sell_price from purchases
        $validated['purchase_price'] = $buyPrice;
        $validated['total_selling_value'] = $validated['quantity_sold'] * $validated['selling_price'];
        $validated['total_purchase_value'] = $validated['quantity_sold'] * $buyPrice;
        $validated['commission'] = 0; // Manual entry: no referrer commission
        $validated['profit'] = $validated['total_selling_value'] - $validated['total_purchase_value'] - 0;
        $validated['status'] = 'pending';
        $validated['paid_amount'] = $validated['paid_amount'] ?? 0;
        $validated['balance'] = $validated['total_selling_value'] - $validated['paid_amount'];
        if (!empty($validated['dealer_id'])) {
            $validated['dealer_name'] = \App\Models\User::find($validated['dealer_id'])->name ?? $validated['dealer_name'] ?? null;
        }

        DistributionSale::create($validated);

        // Keep product.stock_quantity in sync for Category Management / dashboards
        \App\Models\Product::where('id', $validated['product_id'])->decrement('stock_quantity', $validated['quantity_sold']);

        return redirect()->route('admin.stock.distribution')->with('success', 'Distribution sale recorded successfully.');
    }

    public function editDistribution($id)
    {
        $sale = DistributionSale::with(['product.category', 'dealer'])->findOrFail($id);
        return view('admin.stock.edit-distribution', compact('sale'));
    }

    public function updateDistribution(Request $request, $id)
    {
        $sale = DistributionSale::findOrFail($id);
        $validated = $request->validate([
            'paid_amount' => 'nullable|numeric|min:0',
            'collection_date' => 'nullable|date',
        ]);
        $paidAmount = (float) ($validated['paid_amount'] ?? $sale->paid_amount ?? 0);
        $totalSelling = (float) ($sale->total_selling_value ?? 0);
        $balance = max(0, $totalSelling - $paidAmount);

        $sale->update([
            'paid_amount' => $paidAmount,
            'balance' => $balance,
            'collection_date' => $validated['collection_date'] ?? $sale->collection_date,
        ]);

        return redirect()->route('admin.stock.distribution')->with('success', 'Distribution sale updated. Pending amount (balance) updated.');
    }

    public function destroyDistribution($id)
    {
        $sale = DistributionSale::findOrFail($id);
        $product = $sale->product;
        $quantitySold = $sale->quantity_sold;
        
        $sale->delete();
        
        // Keep product.stock_quantity in sync
        if ($product) {
            $product->increment('stock_quantity', $quantitySold);
        }

        return redirect()->route('admin.stock.distribution')->with('success', 'Distribution sale deleted successfully.');
    }

    // Agent Sales
    public function createAgentSale()
    {
        // Fetch products that have been purchased at least once
        $products = \App\Models\Product::whereHas('purchases')->orderBy('name')->get();

        return view('admin.stock.create-agent-sale', compact('products'));
    }

    public function storeAgentSale(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'customer_name' => 'nullable|string|max:255',
            'seller_name' => 'nullable|string|max:255',
            'product_id' => 'required|exists:products,id',
            'quantity_sold' => 'required|integer|min:1',
            'selling_price' => 'required|numeric|min:0',
        ]);

        $service = app(\App\Services\DistributionSaleService::class);
        $buyPrice = $service->getBuyPriceForProduct($validated['product_id']);
        
        $validated['purchase_price'] = $buyPrice;
        $validated['total_selling_value'] = $validated['quantity_sold'] * $validated['selling_price'];
        $validated['total_purchase_value'] = $validated['quantity_sold'] * $buyPrice;
        $validated['profit'] = $validated['total_selling_value'] - $validated['total_purchase_value'];

        // Save to pending sales instead of agent_sales
        \App\Models\PendingSale::create($validated);

        // Keep product.stock_quantity in sync for Category Management / dashboards
        \App\Models\Product::where('id', $validated['product_id'])->decrement('stock_quantity', $validated['quantity_sold']);

        return redirect()->route('admin.stock.pending-sales')->with('success', 'Sale recorded successfully. Please select payment option and save.');
    }

    public function pendingSales()
    {
        $pendingSales = \App\Models\PendingSale::with(['product.category', 'paymentOption'])->latest('date')->get();
        $paymentOptions = \App\Models\PaymentOption::visible()->orderBy('name')->get();
        return view('admin.stock.pending-sales', compact('pendingSales', 'paymentOptions'));
    }

    public function savePendingSale(Request $request, $id)
    {
        $validated = $request->validate([
            'payment_option_id' => 'required|exists:payment_options,id',
        ]);

        $pendingSale = \App\Models\PendingSale::findOrFail($id);
        $pendingSale->update($validated);

        // Add amount to payment option balance
        if ($pendingSale->paymentOption) {
            $pendingSale->paymentOption->increment('balance', $pendingSale->total_selling_value);
        }

        // Move to agent_sales table
        $agentSale = AgentSale::create([
            'customer_name' => $pendingSale->customer_name,
            'seller_name' => $pendingSale->seller_name,
            'product_id' => $pendingSale->product_id,
            'quantity_sold' => $pendingSale->quantity_sold,
            'purchase_price' => $pendingSale->purchase_price,
            'selling_price' => $pendingSale->selling_price,
            'total_purchase_value' => $pendingSale->total_purchase_value,
            'total_selling_value' => $pendingSale->total_selling_value,
            'profit' => $pendingSale->profit,
            'balance' => 0, // Already paid via payment option
            'date' => $pendingSale->date,
        ]);

        // Update product_list items linked to this pending sale
        \App\Models\ProductListItem::where('pending_sale_id', $pendingSale->id)
            ->update([
                'agent_sale_id' => $agentSale->id,
                'pending_sale_id' => null,
            ]);

        // Delete from pending sales
        $pendingSale->delete();

        return redirect()->route('admin.stock.pending-sales')->with('success', 'Sale saved successfully. Amount added to payment option balance.');
    }

    /**
     * Update all existing products to use sell_price from their latest purchase.
     * This ensures front page products show the correct sell_price instead of unit_price.
     */
    public function updateAllProductPrices()
    {
        $products = \App\Models\Product::all();
        $updatedCount = 0;

        foreach ($products as $product) {
            $latestPurchase = Purchase::where('product_id', $product->id)
                ->whereNotNull('sell_price')
                ->latest('date')
                ->latest('id')
                ->first();
            
            if ($latestPurchase && $latestPurchase->sell_price) {
                $product->update(['price' => $latestPurchase->sell_price]);
                $updatedCount++;
            }
        }

        return redirect()->route('admin.stock.purchases')
            ->with('success', "Updated {$updatedCount} product(s) to use sell_price from their latest purchase.");
    }
}
