<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductListItem;
use App\Models\Product;
use App\Models\AgentSale;
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
        $currentQty = $stock->productListItems()->whereNull('sold_at')->count();
        if ($currentQty >= $stock->stock_limit) {
            return response()->json([
                'message' => 'This stock has reached its limit.',
            ], 422);
        }

        $product = Product::firstOrCreate(
            [
                'category_id' => $validated['category_id'],
                'name' => $validated['model'],
            ],
            [
                'price' => 0,
                'stock_quantity' => 0,
                'rating' => 5.0,
                'description' => 'From product list',
                'images' => [],
            ]
        );

        $validated['product_id'] = $product->id;
        $item = ProductListItem::create($validated);

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
     */
    public function showByImei(string $imei)
    {
        $item = ProductListItem::with(['category', 'product'])
            ->where('imei_number', $imei)
            ->whereNull('sold_at')
            ->first();

        if (!$item) {
            return response()->json(['message' => 'Device not found or already sold.'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $item->id,
                'imei_number' => $item->imei_number,
                'model' => $item->model,
                'category_id' => $item->category_id,
                'category_name' => $item->category?->name,
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
            return response()->json(['message' => 'This device has already been sold.'], 422);
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
