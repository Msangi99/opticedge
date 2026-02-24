<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;

class PurchaseController extends Controller
{
    /**
     * List purchases that have pending limit_status only (for admin app Add Product dropdown).
     * Returns purchase name, and category/model from the purchase's product.
     */
    public function forAddProduct()
    {
        $purchases = Purchase::with(['product.category', 'stock'])
            ->where('limit_status', 'pending')
            ->where('limit_remaining', '>', 0)
            ->whereNotNull('stock_id')
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
