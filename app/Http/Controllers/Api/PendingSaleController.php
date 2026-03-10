<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PendingSale;

class PendingSaleController extends Controller
{
    public function index()
    {
        $sales = PendingSale::with(['product:id,name', 'product.category:id,name', 'paymentOption:id,name'])
            ->latest('date')
            ->take(100)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'customer_name' => $sale->customer_name ?? '–',
                    'seller_name' => $sale->seller_name ?? '–',
                    'product_name' => $sale->product?->name ?? '–',
                    'category_name' => $sale->product?->category?->name ?? '–',
                    'quantity_sold' => (int) ($sale->quantity_sold ?? 0),
                    'total_selling_value' => (float) ($sale->total_selling_value ?? 0),
                    'profit' => (float) ($sale->profit ?? 0),
                    'payment_option_name' => $sale->paymentOption?->name,
                    'date' => $sale->date?->format('Y-m-d'),
                    'created_at' => $sale->created_at?->toISOString(),
                ];
            });

        return response()->json(['data' => $sales]);
    }
}
