<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * List all stocks with current quantity (from product_list count).
     */
    public function index()
    {
        $stocks = Stock::withCount(['productListItems as quantity_available' => function ($q) {
            $q->whereNull('sold_at');
        }])->get()->map(function ($stock) {
            return [
                'id' => $stock->id,
                'name' => $stock->name,
                'stock_limit' => $stock->stock_limit,
                'quantity' => $stock->quantity_available ?? $stock->quantity,
                'under_limit' => ($stock->quantity_available ?? $stock->quantity) < $stock->stock_limit,
            ];
        });

        return response()->json(['data' => $stocks]);
    }

    /**
     * Create a new stock with name and limit.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'stock_limit' => 'required|integer|min:1',
        ]);

        $stock = Stock::create($validated);

        return response()->json([
            'message' => 'Stock created.',
            'data' => ['id' => $stock->id, 'name' => $stock->name, 'stock_limit' => $stock->stock_limit],
        ], 201);
    }

    /**
     * List stocks from purchases that have limit_status = pending (for app add-product).
     */
    public function stocksUnderLimit()
    {
        $stockIds = \App\Models\Purchase::where('limit_status', 'pending')
            ->where('limit_remaining', '>', 0)
            ->whereNotNull('stock_id')
            ->pluck('stock_id')
            ->unique()
            ->filter();

        $stocks = Stock::whereIn('id', $stockIds)
            ->withCount(['productListItems as quantity_available' => function ($q) {
                $q->whereNull('sold_at');
            }])
            ->get()
            ->map(function ($stock) {
                return [
                    'id' => $stock->id,
                    'name' => $stock->name,
                    'stock_limit' => $stock->stock_limit,
                    'quantity' => $stock->quantity_available ?? $stock->quantity,
                ];
            });

        return response()->json(['data' => $stocks->values()->all()]);
    }
}
