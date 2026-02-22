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
     * List stocks that have not hit their limit (for admin add-product dropdown).
     */
    public function stocksUnderLimit()
    {
        $stocks = Stock::withCount(['productListItems as quantity_available' => function ($q) {
            $q->whereNull('sold_at');
        }])->get()->filter(function ($stock) {
            $qty = $stock->quantity_available ?? $stock->quantity;
            return $qty < $stock->stock_limit;
        })->map(function ($stock) {
            return [
                'id' => $stock->id,
                'name' => $stock->name,
                'stock_limit' => $stock->stock_limit,
                'quantity' => $stock->quantity_available ?? $stock->quantity,
            ];
        })->values();

        return response()->json(['data' => $stocks]);
    }
}
