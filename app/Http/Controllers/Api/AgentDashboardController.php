<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentAssignment;
use App\Models\AgentSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentDashboardController extends Controller
{
    /**
     * Get agent dashboard data: assignments, stats, and recent sales.
     */
    public function index()
    {
        $agentId = Auth::id();
        
        // Get assignments
        $assignments = AgentAssignment::where('agent_id', $agentId)
            ->with('product.category')
            ->get()
            ->map(function ($a) {
                $remaining = $a->quantity_assigned - $a->quantity_sold;
                return [
                    'id' => $a->id,
                    'product_id' => $a->product_id,
                    'product_name' => $a->product?->name ?? '–',
                    'category_name' => $a->product?->category?->name ?? '–',
                    'quantity_assigned' => (int) $a->quantity_assigned,
                    'quantity_sold' => (int) $a->quantity_sold,
                    'quantity_remaining' => $remaining,
                ];
            })
            ->values()
            ->all();

        // Calculate stats
        $totalAssigned = AgentAssignment::where('agent_id', $agentId)->sum('quantity_assigned');
        $totalSold = AgentAssignment::where('agent_id', $agentId)->sum('quantity_sold');
        $totalRemaining = $totalAssigned - $totalSold;

        // Get recent sales
        $recentSales = AgentSale::where('agent_id', $agentId)
            ->with(['product.category'])
            ->latest('date')
            ->take(10)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'customer_name' => $sale->customer_name ?? '–',
                    'product_name' => $sale->product?->name ?? '–',
                    'category_name' => $sale->product?->category?->name ?? '–',
                    'quantity_sold' => (int) ($sale->quantity_sold ?? 0),
                    'selling_price' => (float) ($sale->selling_price ?? 0),
                    'total_selling_value' => (float) ($sale->total_selling_value ?? 0),
                    'profit' => (float) ($sale->profit ?? 0),
                    'date' => $sale->date ? $sale->date->toISOString() : null,
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'data' => [
                'assignments' => $assignments,
                'stats' => [
                    'total_assigned' => (int) $totalAssigned,
                    'total_sold' => (int) $totalSold,
                    'total_remaining' => (int) $totalRemaining,
                ],
                'recent_sales' => $recentSales,
            ],
        ]);
    }
}
