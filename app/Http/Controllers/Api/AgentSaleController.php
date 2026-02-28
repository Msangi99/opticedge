<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentSale;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AgentSaleController extends Controller
{
    /**
     * List all agent sales for admin dashboard.
     */
    public function index()
    {
        $sales = AgentSale::with(['product.category', 'agent'])
            ->latest('date')
            ->take(10)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'agent_name' => $sale->agent?->name ?? 'Unknown Agent',
                    'customer_name' => $sale->customer_name ?? '–',
                    'product_name' => $sale->product?->name ?? '–',
                    'category_name' => $sale->product?->category?->name ?? '–',
                    'quantity_sold' => (int) ($sale->quantity_sold ?? 0),
                    'selling_price' => (float) ($sale->selling_price ?? 0),
                    'total_selling_value' => (float) ($sale->total_selling_value ?? 0),
                    'profit' => (float) ($sale->profit ?? 0),
                    'commission_paid' => (float) ($sale->commission_paid ?? 0),
                    'date' => $sale->date ? (is_string($sale->date) ? Carbon::parse($sale->date)->toISOString() : $sale->date->toISOString()) : null,
                ];
            })
            ->values()
            ->all();

        return response()->json(['data' => $sales]);
    }
}
