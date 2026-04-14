<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerNeed;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentCustomerNeedController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'product_id' => 'required|exists:products,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:64',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        if ((int) $product->category_id !== (int) $validated['category_id']) {
            return response()->json([
                'message' => 'Selected model does not belong to the chosen category.',
            ], 422);
        }

        $need = CustomerNeed::create([
            'agent_id' => Auth::id(),
            'category_id' => $validated['category_id'],
            'product_id' => $validated['product_id'],
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'branch_id' => $validated['branch_id'] ?? null,
        ]);

        $need->load(['category', 'product', 'branch']);

        return response()->json([
            'message' => 'Customer need recorded.',
            'data' => [
                'id' => $need->id,
                'category' => $need->category?->name,
                'product' => $need->product?->name,
                'customer_name' => $need->customer_name,
                'customer_phone' => $need->customer_phone,
                'branch' => $need->branch?->name,
            ],
        ], 201);
    }
}
