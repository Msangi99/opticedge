<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;

class PurchaseController extends Controller
{
    /**
     * List all purchases for Stocks page: name, limit (quantity), available (limit_status), status (payment_status).
     */
    public function index()
    {
        $purchases = Purchase::with(['product.category', 'stock'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name ?? 'Purchase #' . $p->id,
                    'limit' => (int) $p->quantity,
                    'available' => $p->limit_status ?? '–',
                    'status' => $p->payment_status ?? '–',
                ];
            })
            ->values()
            ->all();

        return response()->json(['data' => $purchases]);
    }

    /**
     * List product_list items for a purchase: model, category name, imei_number.
     */
    public function items(int $id)
    {
        $purchase = Purchase::findOrFail($id);
        $items = $purchase->productListItems()
            ->with('category:id,name')
            ->orderBy('model')
            ->orderBy('imei_number')
            ->get()
            ->map(function ($item) {
                return [
                    'model' => $item->model ?? '–',
                    'category' => $item->category?->name ?? '–',
                    'imei_number' => $item->imei_number ?? '–',
                ];
            })
            ->values()
            ->all();

        return response()->json(['data' => $items]);
    }

    /**
     * List purchases with limit_status = 'pending' and limit_remaining > 0 (for admin app Add Product dropdown).
     * stock_id can be null; returns purchase name and category/model from the purchase's product.
     */
    public function forAddProduct()
    {
        $purchases = Purchase::with(['product.category', 'stock'])
            ->where('limit_status', 'pending')
            ->where('limit_remaining', '>', 0)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($p) {
                $product = $p->product;
                $category = $product?->category;

                return [
                    'id' => $p->id,
                    'name' => $p->name ?? 'Purchase #' . $p->id,
                    'stock_id' => $p->stock_id,
                    'stock_name' => $p->stock?->name,
                    'category_id' => $product?->category_id,
                    'category_name' => $category?->name ?? '–',
                    'model' => $product?->name ?? '–',
                ];
            })
            ->values()
            ->all();

        return response()->json(['data' => $purchases]);
    }
}
