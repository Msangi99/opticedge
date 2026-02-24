<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;

class PurchaseController extends Controller
{
    /**
     * List purchases that have pending limit (for admin app Add Product).
     * Returns purchase name, and category/model from the purchase's product.
     */
    public function forAddProduct()
    {
        $purchases = Purchase::with(['product.category', 'stock'])
            ->where('limit_status', 'pending')
            ->where('limit_remaining', '>', 0)
            ->whereNotNull('stock_id')
            ->orderBy('name')
            ->orderBy('date', 'desc')
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
                    'category_name' => $category?->name,
                    'model' => $product?->name,
                ];
            })
            ->filter(function ($p) {
                return $p['category_id'] && $p['model'];
            })
            ->values()
            ->all();

        return response()->json(['data' => $purchases]);
    }
}
