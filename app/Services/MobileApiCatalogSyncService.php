<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MobileApiCatalogSyncService
{
    private const BASE_URL = 'https://api.mobileapi.dev';

    public function syncIfCatalogEmpty(): array
    {
        if (Product::query()->exists()) {
            return ['ok' => true, 'skipped' => true, 'reason' => 'catalog_not_empty'];
        }

        return $this->syncInsertOnly();
    }

    public function syncInsertOnly(): array
    {
        $apiKey = (string) Setting::query()->where('key', 'mobileapi_api_key')->value('value');
        if ($apiKey === '') {
            return ['ok' => false, 'skipped' => true, 'reason' => 'missing_api_key'];
        }

        $typesRaw = (string) Setting::query()->where('key', 'mobileapi_device_types')->value('value');
        $types = $this->resolveTypes($typesRaw);

        $pagesRaw = (string) Setting::query()->where('key', 'mobileapi_sync_pages')->value('value');
        $maxPages = max(1, min(20, (int) ($pagesRaw !== '' ? $pagesRaw : 1)));

        $createdCategories = 0;
        $createdProducts = 0;

        foreach ($types as $type) {
            for ($page = 1; $page <= $maxPages; $page++) {
                $response = Http::connectTimeout(15)
                    ->timeout(30)
                    ->retry(2, 1200)
                    ->withToken($apiKey)
                    ->acceptJson()
                    ->get(self::BASE_URL . '/devices/by-type/', [
                        'type' => $type,
                        'page' => $page,
                    ]);

                if ($response->failed()) {
                    Log::warning('MobileAPI sync request failed.', [
                        'type' => $type,
                        'page' => $page,
                        'status' => $response->status(),
                    ]);
                    break;
                }

                $payload = $response->json();
                $devices = is_array($payload['devices'] ?? null) ? $payload['devices'] : [];
                if ($devices === []) {
                    break;
                }

                foreach ($devices as $device) {
                    if (!is_array($device) || !isset($device['id'], $device['name'])) {
                        continue;
                    }

                    $category = Category::query()->firstOrCreate(
                        ['name' => ucfirst((string) ($device['device_type'] ?? $type))],
                        ['image' => null, 'mobileapi_type' => (string) ($device['device_type'] ?? $type)]
                    );

                    if ($category->wasRecentlyCreated) {
                        $createdCategories++;
                    }

                    $exists = Product::query()
                        ->where('mobileapi_device_id', (int) $device['id'])
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    $imageCandidates = array_values(array_filter([
                        $device['image_url'] ?? null,
                        $device['main_image_url'] ?? null,
                    ], fn ($v) => is_string($v) && $v !== ''));

                    Product::query()->create([
                        'category_id' => $category->id,
                        'name' => (string) $device['name'],
                        'brand' => (string) ($device['manufacturer_name'] ?? $device['brand_name'] ?? 'Unknown'),
                        'price' => 0,
                        'rating' => 5.0,
                        'stock_quantity' => 0,
                        'description' => $device['description'] ?? null,
                        'images' => $imageCandidates,
                        'mobileapi_device_id' => (int) $device['id'],
                        'device_type' => (string) ($device['device_type'] ?? $type),
                        'specifications' => $this->mapSpecifications($device),
                    ]);
                    $createdProducts++;
                }

                if (!(bool) ($payload['has_next'] ?? false)) {
                    break;
                }
            }
        }

        Setting::query()->updateOrCreate(
            ['key' => 'mobileapi_last_synced_at'],
            ['value' => now()->toIso8601String()]
        );

        return [
            'ok' => true,
            'skipped' => false,
            'created_categories' => $createdCategories,
            'created_products' => $createdProducts,
        ];
    }

    private function resolveTypes(string $typesRaw): array
    {
        $types = collect(explode(',', $typesRaw !== '' ? $typesRaw : 'phone,tablet'))
            ->map(fn ($item) => strtolower(trim($item)))
            ->filter()
            ->values()
            ->all();

        return $types !== [] ? $types : ['phone', 'tablet'];
    }

    private function mapSpecifications(array $device): array
    {
        return array_filter([
            'manufacturer_name' => $device['manufacturer_name'] ?? $device['brand_name'] ?? null,
            'device_type' => $device['device_type'] ?? null,
            'colors' => $device['colors'] ?? null,
            'storage' => $device['storage'] ?? null,
            'screen_resolution' => $device['screen_resolution'] ?? null,
            'weight' => $device['weight'] ?? null,
            'thickness' => $device['thickness'] ?? null,
            'release_date' => $device['release_date'] ?? null,
            'camera' => $device['camera'] ?? null,
            'battery_capacity' => $device['battery_capacity'] ?? null,
            'hardware' => $device['hardware'] ?? null,
            'model_numbers' => $device['model_numbers'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');
    }
}
