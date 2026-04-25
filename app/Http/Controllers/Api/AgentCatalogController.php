<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\MobileApiCatalogSyncService;
use Illuminate\Http\JsonResponse;

class AgentCatalogController extends Controller
{
    public function categories(): JsonResponse
    {
        app(MobileApiCatalogSyncService::class)->syncIfCatalogEmpty();

        $categories = Category::query()
            ->whereHas('products')
            ->orderBy('name')
            ->get(['id', 'name', 'image']);

        return response()->json(['data' => $categories]);
    }

    public function productsByCategory(int $category): JsonResponse
    {
        app(MobileApiCatalogSyncService::class)->syncIfCatalogEmpty();

        $exists = Category::whereKey($category)->exists();
        if (!$exists) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        $products = Product::query()
            ->where('category_id', $category)
            ->orderBy('name')
            ->get(['id', 'name', 'brand', 'images', 'specifications']);

        return response()->json(['data' => $products]);
    }
}
