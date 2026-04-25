<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\MobileApiCatalogSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class AgentCatalogController extends Controller
{
    public function categories(): JsonResponse
    {
        app(MobileApiCatalogSyncService::class)->syncIfCatalogEmpty();

        $categories = Category::query()
            ->whereHas('products')
            ->orderBy('name')
            ->get(['id', 'name', 'image'])
            ->map(fn (Category $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'image' => $this->publicImageUrl($c->image),
            ]);

        return response()->json(['data' => $categories]);
    }

    public function productsByCategory(int $category): JsonResponse
    {
        app(MobileApiCatalogSyncService::class)->syncIfCatalogEmpty();

        $exists = Category::whereKey($category)->exists();
        if (! $exists) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        $products = Product::query()
            ->with(['category:id,name,image'])
            ->where('category_id', $category)
            ->orderBy('name')
            ->get(['id', 'name', 'brand', 'category_id', 'images', 'specifications', 'description', 'device_type']);

        $data = $products->map(fn (Product $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'model' => $p->name,
            'brand' => $p->brand,
            'description' => $p->description,
            'device_type' => $p->device_type,
            'images' => $this->mapPublicImageUrls(is_array($p->images) ? $p->images : []),
            'specifications' => $p->specifications,
            'category' => $p->relationLoaded('category') && $p->category
                ? [
                    'id' => $p->category->id,
                    'name' => $p->category->name,
                    'image' => $this->publicImageUrl($p->category->image),
                ]
                : null,
        ]);

        return response()->json(['data' => $data]);
    }

    private function mapPublicImageUrls(array $pathsOrUrls): array
    {
        return array_values(array_filter(array_map(
            fn ($v) => is_string($v) ? $this->publicImageUrl($v) : null,
            $pathsOrUrls
        )));
    }

    private function publicImageUrl(?string $pathOrUrl): ?string
    {
        if ($pathOrUrl === null || $pathOrUrl === '') {
            return null;
        }
        if (str_starts_with($pathOrUrl, 'http://') || str_starts_with($pathOrUrl, 'https://')) {
            return $pathOrUrl;
        }

        return Storage::disk('public')->url($pathOrUrl);
    }
}
