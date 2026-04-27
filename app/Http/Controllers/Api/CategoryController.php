<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * List categories. For admin list (with_counts=1) includes products_count.
     */
    public function index(Request $request)
    {
        $withCounts = $request->query('with_counts');
        $query = Category::orderBy('name');

        if ($withCounts) {
            $categories = $query->withCount('products')->get(['id', 'name'])->map(function ($c) {
                return ['id' => $c->id, 'name' => $c->name, 'products_count' => $c->products_count ?? 0];
            });
        } else {
            $categories = $query->get(['id', 'name']);
        }

        return response()->json(['data' => $categories]);
    }

    /**
     * Distinct models (products) in a category — same catalog rows as agent catalog, for admin UI.
     */
    public function models(int $category): JsonResponse
    {
        if (! Category::whereKey($category)->exists()) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        $products = Product::query()
            ->where('category_id', $category)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['data' => $products]);
    }
}
