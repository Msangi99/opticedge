<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class AgentCatalogController extends Controller
{
    public function categories(): JsonResponse
    {
        $categories = Category::query()
            ->whereHas('products')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['data' => $categories]);
    }

    public function productsByCategory(int $category): JsonResponse
    {
        $exists = Category::whereKey($category)->exists();
        if (!$exists) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        $products = Product::query()
            ->where('category_id', $category)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['data' => $products]);
    }
}
