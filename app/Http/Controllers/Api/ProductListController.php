<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductListItem;
use App\Models\Product;
use App\Models\AgentCredit;
use App\Models\AgentCreditPayment;
use App\Models\PendingSale;
use App\Models\PaymentOption;
use App\Models\Purchase;
use App\Services\DistributionSaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductListController extends Controller
{
    /**
     * Admin: Add a product to product_list.
     * Accepts either purchase_id + imei_number (category/model from purchase) or stock_id + category_id + model + imei_number.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_id' => 'nullable|exists:purchases,id',
            'stock_id' => 'nullable|exists:stocks,id',
            'category_id' => 'nullable|exists:categories,id',
            'model' => 'nullable|string|max:255',
            'imei_number' => 'required|string|max:255|unique:product_list,imei_number',
        ]);

        $purchase = null;
        $stockId = null;
        $categoryId = null;
        $model = null;

        if (!empty($validated['purchase_id'])) {
            // Use purchase: get category and model from the purchase's product
            $purchase = Purchase::with('product')->findOrFail($validated['purchase_id']);
            if ($purchase->limit_status !== 'pending' || $purchase->limit_remaining <= 0) {
                return response()->json([
                    'message' => 'This purchase has no remaining limit.',
                ], 422);
            }
            if (!$purchase->product_id) {
                return response()->json([
                    'message' => 'Purchase has no product linked.',
                ], 422);
            }
            $stockId = $purchase->stock_id;
            $categoryId = $purchase->product->category_id;
            $model = $purchase->product->name;
        } else {
            // Legacy: stock_id + category_id + model
            if (empty($validated['stock_id']) || empty($validated['category_id']) || empty($validated['model'])) {
                return response()->json([
                    'message' => 'Provide either purchase_id or stock_id, category_id and model.',
                ], 422);
            }
            $stock = \App\Models\Stock::findOrFail($validated['stock_id']);
            $purchase = Purchase::where('stock_id', $stock->id)
                ->where('limit_status', 'pending')
                ->where('limit_remaining', '>', 0)
                ->latest('date')
                ->latest('id')
                ->first();
            if (!$purchase) {
                return response()->json([
                    'message' => 'No pending purchase limit for this stock.',
                ], 422);
            }
            $stockId = $validated['stock_id'];
            $categoryId = $validated['category_id'];
            $model = $validated['model'];
        }

        // Use sell_price if available, otherwise use unit_price
        $productPrice = $purchase->sell_price ?? $purchase->unit_price ?? 0;
        $product = Product::firstOrCreate(
            [
                'category_id' => $categoryId,
                'name' => $model,
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

        $item = ProductListItem::create([
            'stock_id' => $stockId,
            'purchase_id' => $purchase->id,
            'category_id' => $categoryId,
            'model' => $model,
            'imei_number' => $validated['imei_number'],
            'product_id' => $product->id,
        ]);

        $purchase->decrement('limit_remaining');
        if ($purchase->fresh()->limit_remaining <= 0) {
            $purchase->update(['limit_status' => 'complete']);
        }

        return response()->json([
            'message' => 'Product added to list.',
            'data' => [
                'id' => $item->id,
                'stock_id' => $item->stock_id,
                'category_id' => $item->category_id,
                'model' => $item->model,
                'imei_number' => $item->imei_number,
            ],
        ], 201);
    }

    /**
     * Admin: Add multiple IMEIs to product_list for one purchase.
     */
    public function batchStore(Request $request)
    {
        $validated = $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'imei_numbers' => 'required|array|min:1',
            'imei_numbers.*' => 'required|string|max:255',
        ]);

        $purchase = Purchase::with('product')->findOrFail($validated['purchase_id']);
        if ($purchase->limit_status !== 'pending' || $purchase->limit_remaining <= 0) {
            return response()->json([
                'message' => 'This purchase has no remaining limit.',
            ], 422);
        }
        if (! $purchase->product_id) {
            return response()->json([
                'message' => 'Purchase has no product linked.',
            ], 422);
        }

        $raw = array_map('trim', $validated['imei_numbers']);
        $imeis = array_values(array_unique(array_filter($raw, fn ($s) => $s !== '')));

        if (count($imeis) > $purchase->limit_remaining) {
            return response()->json([
                'message' => 'Not enough purchase limit for this many IMEIs. Remaining: '.$purchase->limit_remaining.'.',
            ], 422);
        }

        $created = [];
        $failed = [];

        DB::transaction(function () use ($purchase, $imeis, &$created, &$failed) {
            $purchase->refresh();
            $stockId = $purchase->stock_id;
            $categoryId = $purchase->product->category_id;
            $model = $purchase->product->name;
            $productPrice = $purchase->sell_price ?? $purchase->unit_price ?? 0;

            $product = Product::firstOrCreate(
                [
                    'category_id' => $categoryId,
                    'name' => $model,
                ],
                [
                    'price' => (float) $productPrice,
                    'stock_quantity' => 0,
                    'rating' => 5.0,
                    'description' => 'From product list',
                    'images' => $purchase->product?->images ?? [],
                ]
            );

            if ($purchase->sell_price && $product->price != $purchase->sell_price) {
                $product->update(['price' => (float) $purchase->sell_price]);
            }

            foreach ($imeis as $imei) {
                if (ProductListItem::where('imei_number', $imei)->exists()) {
                    $failed[] = ['imei_number' => $imei, 'message' => 'IMEI already in product list.'];

                    continue;
                }

                $purchase->refresh();
                if ($purchase->limit_remaining <= 0) {
                    $failed[] = ['imei_number' => $imei, 'message' => 'Purchase limit exhausted.'];

                    break;
                }

                $item = ProductListItem::create([
                    'stock_id' => $stockId,
                    'purchase_id' => $purchase->id,
                    'category_id' => $categoryId,
                    'model' => $model,
                    'imei_number' => $imei,
                    'product_id' => $product->id,
                ]);

                $purchase->decrement('limit_remaining');
                if ($purchase->fresh()->limit_remaining <= 0) {
                    $purchase->update(['limit_status' => 'complete']);
                }

                $created[] = [
                    'id' => $item->id,
                    'imei_number' => $item->imei_number,
                    'model' => $item->model,
                ];
            }
        });

        $status = count($created) > 0 ? 201 : 422;

        return response()->json([
            'message' => count($created) > 0 ? 'Batch add completed.' : 'No items added.',
            'data' => [
                'created' => $created,
                'failed' => $failed,
            ],
        ], $status);
    }

    /**
     * Agent: List product_list items that are available to sell (not yet sold).
     * Only returns items where sold_at is null.
     */
    public function available()
    {
        $items = ProductListItem::with(['category', 'product', 'stock', 'purchase'])
            ->whereNull('sold_at')
            ->orderBy('model')
            ->orderBy('imei_number')
            ->get();

        $data = $items->map(function ($item) {
            $sellPrice = null;
            if ($item->purchase_id && $item->purchase) {
                $sellPrice = $item->purchase->sell_price !== null ? (float) $item->purchase->sell_price : null;
            }
            if ($sellPrice === null && $item->stock_id && $item->product_id) {
                $purchase = Purchase::where('stock_id', $item->stock_id)
                    ->where('product_id', $item->product_id)
                    ->whereNotNull('sell_price')
                    ->latest('date')
                    ->first();
                $sellPrice = $purchase ? (float) $purchase->sell_price : null;
            }
            if ($sellPrice === null && $item->product) {
                $sellPrice = $item->product->price > 0 ? (float) $item->product->price : null;
            }
            $sellPrice = $sellPrice ?? 0.0;

            $purchasePrice = null;
            if ($item->purchase_id && $item->purchase) {
                $purchasePrice = (float) $item->purchase->unit_price;
            }
            if ($purchasePrice === null && $item->stock_id && $item->product_id) {
                $purchase = Purchase::where('stock_id', $item->stock_id)
                    ->where('product_id', $item->product_id)
                    ->latest('date')
                    ->first();
                $purchasePrice = $purchase ? (float) $purchase->unit_price : null;
            }
            if ($purchasePrice === null && $item->product) {
                $purchasePrice = (float) $item->product->price;
            }
            $purchasePrice = $purchasePrice ?? 0.0;

            return [
                'id' => $item->id,
                'imei_number' => $item->imei_number,
                'model' => $item->model,
                'category_id' => $item->category_id,
                'category_name' => $item->category?->name,
                'stock_id' => $item->stock_id,
                'stock_name' => $item->stock?->name,
                'sell_price' => $sellPrice,
                'purchase_price' => $purchasePrice,
                'product_id' => $item->product_id,
            ];
        })->values()->all();

        return response()->json(['data' => $data]);
    }

    /**
     * Agent: Get device info by IMEI (only if not sold).
     * Returns which stock the device is in, category and sell price from that stock's purchase.
     */
    public function showByImei(string $imei)
    {
        $item = ProductListItem::with(['category', 'product', 'stock', 'purchase'])
            ->where('imei_number', $imei)
            ->whereNull('sold_at')
            ->first();

        if (!$item) {
            return response()->json([
                'message' => 'This device is not in stock or has already been sold. Only devices that are purchased and still in stock can be sold.',
            ], 404);
        }

        // Stock: which stock this barcode is in
        $stockName = $item->stock?->name;
        $stockId = $item->stock_id;

        // Category from item (linked to stock)
        $categoryName = $item->category?->name;
        $categoryId = $item->category_id;

        // Sell price from the purchase for this stock (recommended selling price)
        $sellPrice = null;
        if ($item->purchase_id && $item->purchase) {
            $sellPrice = $item->purchase->sell_price !== null ? (float) $item->purchase->sell_price : null;
        }
        if ($sellPrice === null && $item->stock_id && $item->product_id) {
            $purchase = Purchase::where('stock_id', $item->stock_id)
                ->where('product_id', $item->product_id)
                ->whereNotNull('sell_price')
                ->latest('date')
                ->first();
            $sellPrice = $purchase ? (float) $purchase->sell_price : null;
        }
        if ($sellPrice === null && $item->product) {
            $sellPrice = $item->product->price > 0 ? (float) $item->product->price : null;
        }
        $sellPrice = $sellPrice ?? 0.0;

        // Purchase (cost) price for reference
        $purchasePrice = null;
        if ($item->purchase_id && $item->purchase) {
            $purchasePrice = (float) $item->purchase->unit_price;
        }
        if ($purchasePrice === null && $item->stock_id && $item->product_id) {
            $purchase = Purchase::where('stock_id', $item->stock_id)
                ->where('product_id', $item->product_id)
                ->latest('date')
                ->first();
            $purchasePrice = $purchase ? (float) $purchase->unit_price : null;
        }
        if ($purchasePrice === null && $item->product) {
            $purchasePrice = (float) $item->product->price;
        }
        $purchasePrice = $purchasePrice ?? 0.0;

        return response()->json([
            'data' => [
                'id' => $item->id,
                'imei_number' => $item->imei_number,
                'model' => $item->model,
                'category_id' => $categoryId,
                'category_name' => $categoryName,
                'stock_id' => $stockId,
                'stock_name' => $stockName,
                'sell_price' => $sellPrice,
                'purchase_price' => $purchasePrice,
                'product_id' => $item->product_id,
            ],
        ]);
    }

    /**
     * Agent: Record sale for one device (by product_list id), enter customer info. Deducts from stock.
     */
    public function sell(Request $request)
    {
        $validated = $request->validate([
            'product_list_id' => 'required|exists:product_list,id',
            'customer_name' => 'required|string|max:255',
            'selling_price' => 'required|numeric|min:0',
        ]);

        $item = ProductListItem::with(['category', 'product'])->findOrFail($validated['product_list_id']);

        if ($item->isSold()) {
            return response()->json([
                'message' => 'This device is not in stock or has already been sold. Only purchased devices still in stock can be sold.',
            ], 422);
        }

        $product = $item->product;
        if (!$product) {
            $product = Product::firstOrCreate(
                [
                    'category_id' => $item->category_id,
                    'name' => $item->model,
                ],
                [
                    'price' => 0,
                    'stock_quantity' => 0,
                    'rating' => 5.0,
                    'description' => 'From product list',
                    'images' => [],
                ]
            );
            $item->update(['product_id' => $product->id]);
        }

        $buyPrice = app(DistributionSaleService::class)->getBuyPriceForProduct($product->id);
        $totalSell = (float) $validated['selling_price'];
        $totalBuy = $buyPrice * 1;
        $profit = $totalSell - $totalBuy;

        $agent = Auth::user();

        $sale = DB::transaction(function () use ($item, $product, $validated, $buyPrice, $totalSell, $totalBuy, $profit, $agent) {
            // Save to pending sales instead of agent_sales
            $sale = PendingSale::create([
                'customer_name' => $validated['customer_name'],
                'seller_name' => $agent->name,
                'product_id' => $product->id,
                'quantity_sold' => 1,
                'purchase_price' => $buyPrice,
                'selling_price' => (float) $validated['selling_price'],
                'total_purchase_value' => $totalBuy,
                'total_selling_value' => $totalSell,
                'profit' => $profit,
                'date' => now()->toDateString(),
            ]);

            $item->update([
                'sold_at' => now(),
                'pending_sale_id' => $sale->id,
            ]);

            $product->decrement('stock_quantity'); // keep product.stock_quantity in sync if used elsewhere

            return $sale;
        });

        return response()->json([
            'message' => 'Sale recorded. Waiting for payment option selection.',
            'data' => [
                'pending_sale_id' => $sale->id,
                'customer_name' => $sale->customer_name,
                'selling_price' => $sale->selling_price,
            ],
        ], 201);
    }

    /**
     * Agent: Sell device on credit (loan to customer). Creates agent_credits row; optional down payment.
     */
    public function sellCredit(Request $request)
    {
        $rules = [
            'product_list_id' => 'required|exists:product_list,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:64',
            'description' => 'nullable|string|max:2000',
            'selling_price' => 'required|numeric|min:0',
            'down_payment' => 'nullable|numeric|min:0',
            'installment_count' => 'nullable|integer|min:0',
            'installment_amount' => 'nullable|numeric|min:0',
            'first_due_date' => 'nullable|date',
            'installment_notes' => 'nullable|string|max:2000',
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('agent_credits', 'installment_interval_days')) {
            $rules['installment_interval_days'] = 'nullable|integer|min:1|max:3650';
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('payment_options')) {
            $rules['payment_option_id'] = 'nullable|exists:payment_options,id';
        } else {
            $rules['payment_option_id'] = 'nullable';
        }

        $validated = $request->validate($rules);

        $item = ProductListItem::with(['category', 'product'])->findOrFail($validated['product_list_id']);

        if ($item->isSold()) {
            return response()->json([
                'message' => 'This device is not in stock or has already been sold.',
            ], 422);
        }

        $product = $item->product;
        if (!$product) {
            $product = Product::firstOrCreate(
                [
                    'category_id' => $item->category_id,
                    'name' => $item->model,
                ],
                [
                    'price' => 0,
                    'stock_quantity' => 0,
                    'rating' => 5.0,
                    'description' => 'From product list',
                    'images' => [],
                ]
            );
            $item->update(['product_id' => $product->id]);
        }

        $totalCredit = (float) $validated['selling_price'];
        $down = (float) ($validated['down_payment'] ?? 0);
        if ($down > $totalCredit + 0.0001) {
            return response()->json([
                'message' => 'Down payment cannot exceed total credit amount.',
            ], 422);
        }

        $eps = 0.0001;
        $paymentOptionId = $validated['payment_option_id'] ?? null;
        if ($paymentOptionId === '' || $paymentOptionId === false) {
            $paymentOptionId = null;
        } else {
            $paymentOptionId = $paymentOptionId !== null ? (int) $paymentOptionId : null;
        }

        if ($down > $eps && $paymentOptionId) {
            $opt = PaymentOption::find($paymentOptionId);
            if (!$opt || $opt->balance + $eps < $down) {
                return response()->json([
                    'message' => 'Insufficient balance in selected payment channel for down payment.',
                ], 422);
            }
        }

        $paymentStatus = $down >= $totalCredit - $eps ? 'paid' : ($down > $eps ? 'partial' : 'pending');

        $agent = Auth::user();

        $notes = $validated['description'] ?? $validated['installment_notes'] ?? null;

        $credit = DB::transaction(function () use ($item, $product, $validated, $totalCredit, $down, $paymentStatus, $paymentOptionId, $agent, $notes) {
            $creditAttrs = [
                'agent_id' => $agent->id,
                'customer_name' => $validated['customer_name'],
                'product_list_id' => $item->id,
                'product_id' => $product->id,
                'total_amount' => $totalCredit,
                'paid_amount' => min($totalCredit, $down),
                'payment_status' => $paymentStatus,
                'payment_option_id' => $paymentOptionId,
                'installment_count' => $validated['installment_count'] ?? null,
                'installment_amount' => isset($validated['installment_amount']) ? (float) $validated['installment_amount'] : null,
                'first_due_date' => $validated['first_due_date'] ?? null,
                'installment_notes' => $notes,
                'date' => now()->toDateString(),
                'paid_date' => $down > $eps ? now()->toDateString() : null,
            ];
            if (\Illuminate\Support\Facades\Schema::hasColumn('agent_credits', 'customer_phone')) {
                $phone = isset($validated['customer_phone']) ? trim((string) $validated['customer_phone']) : '';
                $creditAttrs['customer_phone'] = $phone !== '' ? $phone : null;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('agent_credits', 'installment_interval_days')) {
                $creditAttrs['installment_interval_days'] = isset($validated['installment_interval_days'])
                    ? (int) $validated['installment_interval_days']
                    : null;
            }
            $credit = AgentCredit::create($creditAttrs);

            if ($down > $eps && $paymentOptionId) {
                $opt = PaymentOption::find($paymentOptionId);
                if ($opt) {
                    $opt->decrement('balance', min($down, $totalCredit));
                }
            }

            if ($down > $eps) {
                AgentCreditPayment::create([
                    'agent_credit_id' => $credit->id,
                    'payment_option_id' => $paymentOptionId,
                    'amount' => min($down, $totalCredit),
                    'paid_date' => now()->toDateString(),
                ]);
            }

            $item->update([
                'sold_at' => now(),
                'agent_credit_id' => $credit->id,
                'pending_sale_id' => null,
            ]);

            $product->decrement('stock_quantity');

            return $credit;
        });

        return response()->json([
            'message' => 'Credit sale recorded.',
            'data' => [
                'agent_credit_id' => $credit->id,
                'customer_name' => $credit->customer_name,
                'total_amount' => (float) $credit->total_amount,
                'paid_amount' => (float) $credit->paid_amount,
                'payment_status' => $credit->payment_status,
            ],
        ], 201);
    }
}
