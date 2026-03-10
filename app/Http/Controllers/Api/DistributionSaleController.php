<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DistributionSale;

class DistributionSaleController extends Controller
{
    public function index()
    {
        $sales = DistributionSale::with(['product:id,name', 'product.category:id,name', 'dealer:id,name', 'paymentOption:id,name'])
            ->latest('date')
            ->take(100)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'dealer_name' => $sale->dealer?->name ?? $sale->dealer_name ?? '–',
                    'product_name' => $sale->product?->name ?? '–',
                    'category_name' => $sale->product?->category?->name ?? '–',
                    'quantity_sold' => (int) ($sale->quantity_sold ?? 0),
                    'total_selling_value' => (float) ($sale->total_selling_value ?? 0),
                    'profit' => (float) ($sale->profit ?? 0),
                    'status' => $sale->status ?? 'pending',
                    'payment_option_name' => $sale->paymentOption?->name,
                    'date' => $sale->date?->format('Y-m-d'),
                    'created_at' => $sale->created_at?->toISOString(),
                ];
            });

        return response()->json(['data' => $sales]);
    }
}
