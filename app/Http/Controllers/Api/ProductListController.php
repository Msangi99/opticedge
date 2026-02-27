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
            'selected_images' => 'nullable|array',
            'selected_images.*' => 'string|max:255',
            'images' => 'nullable|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
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

        // Handle images: either selected from gallery or uploaded from device
        $imagePaths = [];
        
        // If selected images from gallery are provided, use those
        if ($request->has('selected_images') && is_array($request->selected_images) && !empty($request->selected_images)) {
            $imagePaths = $request->selected_images;
        }
        // If new images are uploaded, upload them
        elseif ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                if ($image->isValid()) {
                    $path = $image->store('products', 'public');
                    $imagePaths[] = $path;
                }
            }
        }
        // Otherwise, use images from purchase product if available
        else {
            $purchaseProductImages = $purchase->product?->images ?? [];
            $imagePaths = is_string($purchaseProductImages) ? json_decode($purchaseProductImages, true) : $purchaseProductImages;
            if (!is_array($imagePaths)) {
                $imagePaths = [];
            }
        }

        $product = Product::firstOrCreate(
            [
                'category_id' => $categoryId,
                'name' => $model,
            ],
            [
                'price' => (float) ($purchase->sell_price ?? 0),
                'stock_quantity' => 0,
                'rating' => 5.0,
                'description' => 'From product list',
                'images' => $imagePaths,
            ]
        );

        // Update product images if they were provided (selected or uploaded)
        if (!empty($imagePaths)) {
            $product->update(['images' => $imagePaths]);
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
