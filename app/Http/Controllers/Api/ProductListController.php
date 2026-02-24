<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductListItem;
use App\Models\Product;
use App\Models\AgentSale;
use App\Models\Purchase;
use App\Services\DistributionSaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductListController extends Controller
{
    /**
     * Admin: Add a product to product_list (stock_id, category_id, model, imei_number).
     * Quantity for the stock is derived from count of product_list rows; enforce stock limit.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'stock_id' => 'required|exists:stocks,id',
            'category_id' => 'required|exists:categories,id',
            'model' => 'required|string|max:255',
            'imei_number' => 'required|string|max:255|unique:product_list,imei_number',
        ]);

        $stock = \App\Models\Stock::findOrFail($validated['stock_id']);

        // Assign to a pending purchase for this stock (quantity = limit; decrement on each IMEI add)
        $purchase = Purchase::where('stock_id', $stock->id)
            ->where('limit_status', 'pending')
            ->where('limit_remaining', '>', 0)
            ->latest('date')
            ->latest('id')
            ->first();

        if (!$purchase) {
            return response()->json([
                'message' => 'No pending purchase limit for this stock. Create a purchase with this stock first.',
            ], 422);
        }

        $product = Product::firstOrCreate(
            [
                'category_id' => $validated['category_id'],
                'name' => $validated['model'],
            ],
            [
                'price' => (float) ($purchase->sell_price ?? 0),
                'stock_quantity' => 0,
                'rating' => 5.0,
                'description' => 'From product list',
                'images' => $purchase->product?->images ?? [],
            ]
        );

        $validated['product_id'] = $product->id;
        $validated['purchase_id'] = $purchase->id;
        $item = ProductListItem::create($validated);

        // Decrement purchase limit; when 0, mark complete
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
            $sale = AgentSale::create([
                'agent_id' => $agent->id,
                'customer_name' => $validated['customer_name'],
                'seller_name' => $agent->name,
                'product_id' => $product->id,
                'quantity_sold' => 1,
                'purchase_price' => $buyPrice,
                'selling_price' => (float) $validated['selling_price'],
                'total_purchase_value' => $totalBuy,
                'total_selling_value' => $totalSell,
                'profit' => $profit,
                'commission_paid' => 0,
                'balance' => $totalSell,
                'date' => now()->toDateString(),
            ]);

            $item->update([
                'sold_at' => now(),
                'agent_sale_id' => $sale->id,
            ]);

            $product->decrement('stock_quantity'); // keep product.stock_quantity in sync if used elsewhere

            return $sale;
        });

        return response()->json([
            'message' => 'Sale recorded.',
            'data' => [
                'agent_sale_id' => $sale->id,
                'customer_name' => $sale->customer_name,
                'selling_price' => $sale->selling_price,
            ],
        ], 201);
    }
}
