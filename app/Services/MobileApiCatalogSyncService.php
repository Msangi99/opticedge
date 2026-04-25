<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MobileApiCatalogSyncService
{
    private const BASE_URL = 'https://api.mobileapi.dev';

    /**
     * Manufacturer names for MobileAPI {@see https://mobileapi.dev/docs/} GET /devices/by-manufacturer/?manufacturer=…
     *
     * @var list<string>
     */
    public const DEFAULT_MAJOR_BRANDS = [
        'Samsung',
        'Apple',
        'Google',
        'Xiaomi',
        'OnePlus',
        'Oppo',
        'Vivo',
        'Realme',
        'Huawei',
        'Motorola',
        'Sony',
        'Nokia',
        'Nothing',
        'Honor',
        'Asus',
        'Lenovo',
        'Tecno',
        'Infinix',
        'Itel',
        'ZTE',
        'Meizu',
        'Fairphone',
        'Sharp',
        'Alcatel',
    ];

    /** @var array<int, string|null> manufacturer id => public disk path or external URL, or '' if fetch failed */
    private array $manufacturerLogoCache = [];

    public function syncIfCatalogEmpty(): array
    {
        if (Product::query()->exists()) {
            return ['ok' => true, 'skipped' => true, 'reason' => 'catalog_not_empty'];
        }

        return $this->syncByManufacturers(self::DEFAULT_MAJOR_BRANDS);
    }

    /**
     * Paginate GET /devices/by-manufacturer/ for each name; categories = brands, products = models (insert-only by mobileapi_device_id).
     *
     * @param  list<string>  $manufacturerNames
     * @return array{ok: bool, skipped?: bool, reason?: string, created_categories: int, created_products: int}
     */
    public function syncByManufacturers(array $manufacturerNames): array
    {
        $apiKey = (string) Setting::query()->where('key', 'mobileapi_api_key')->value('value');
        if ($apiKey === '') {
            return ['ok' => false, 'skipped' => true, 'reason' => 'missing_api_key', 'created_categories' => 0, 'created_products' => 0];
        }

        $maxPages = $this->resolveBrandSyncMaxPages();
        $hardCap = 500;

        $createdCategories = 0;
        $createdProducts = 0;

        foreach ($manufacturerNames as $manufacturerLabel) {
            $manufacturerLabel = trim((string) $manufacturerLabel);
            if ($manufacturerLabel === '') {
                continue;
            }

            $page = 1;
            while (true) {
                if ($maxPages !== null && $page > $maxPages) {
                    break;
                }
                if ($maxPages === null && $page > $hardCap) {
                    Log::warning('MobileAPI brand sync stopped at hard page cap.', [
                        'manufacturer' => $manufacturerLabel,
                        'page' => $page,
                    ]);
                    break;
                }

                $response = Http::connectTimeout(15)
                    ->timeout(30)
                    ->retry(2, 1200)
                    ->withToken($apiKey)
                    ->acceptJson()
                    ->get(self::BASE_URL . '/devices/by-manufacturer/', [
                        'manufacturer' => $manufacturerLabel,
                        'page' => $page,
                    ]);

                if ($response->failed()) {
                    Log::warning('MobileAPI by-manufacturer request failed.', [
                        'manufacturer' => $manufacturerLabel,
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
                    if (! is_array($device) || ! isset($device['id'], $device['name'])) {
                        continue;
                    }

                    $result = $this->processDeviceForCatalog($device, $apiKey, 'phone');
                    if ($result['new_category']) {
                        $createdCategories++;
                    }
                    if ($result['new_product']) {
                        $createdProducts++;
                    }
                }

                if (! (bool) ($payload['has_next'] ?? false)) {
                    break;
                }

                $page++;
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

    /**
     * Legacy: sync via GET /devices/by-type/ (phones, tablets, … from settings).
     *
     * @return array{ok: bool, skipped?: bool, reason?: string, created_categories: int, created_products: int}
     */
    public function syncInsertOnly(): array
    {
        $apiKey = (string) Setting::query()->where('key', 'mobileapi_api_key')->value('value');
        if ($apiKey === '') {
            return ['ok' => false, 'skipped' => true, 'reason' => 'missing_api_key', 'created_categories' => 0, 'created_products' => 0];
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
                    if (! is_array($device) || ! isset($device['id'], $device['name'])) {
                        continue;
                    }

                    $result = $this->processDeviceForCatalog($device, $apiKey, $type);
                    if ($result['new_category']) {
                        $createdCategories++;
                    }
                    if ($result['new_product']) {
                        $createdProducts++;
                    }
                }

                if (! (bool) ($payload['has_next'] ?? false)) {
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

    /**
     * @return array{new_category: bool, new_product: bool}
     */
    private function processDeviceForCatalog(array $device, string $apiKey, string $mobileapiTypeFallback): array
    {
        $brandName = $this->resolveBrandName($device);
        $manufacturerId = $this->resolveManufacturerId($device);

        if ($manufacturerId !== null) {
            $category = Category::query()->firstOrCreate(
                ['mobileapi_manufacturer_id' => $manufacturerId],
                [
                    'name' => $brandName,
                    'image' => null,
                    'mobileapi_type' => $mobileapiTypeFallback,
                ]
            );
        } else {
            $category = Category::query()->firstOrCreate(
                [
                    'name' => $brandName,
                    'mobileapi_manufacturer_id' => null,
                ],
                [
                    'image' => null,
                    'mobileapi_type' => $mobileapiTypeFallback,
                ]
            );
        }

        $newCategory = $category->wasRecentlyCreated;

        if ($category->name !== $brandName) {
            $category->update(['name' => $brandName]);
        }

        $this->maybeAttachBrandLogo($category, $manufacturerId, $apiKey);

        $exists = Product::query()
            ->where('mobileapi_device_id', (int) $device['id'])
            ->exists();

        if ($exists) {
            return ['new_category' => $newCategory, 'new_product' => false];
        }

        $imagePaths = $this->resolveProductImagePaths($device, (int) $device['id']);

        Product::query()->create([
            'category_id' => $category->id,
            'name' => (string) $device['name'],
            'brand' => $brandName,
            'price' => 0,
            'rating' => 5.0,
            'stock_quantity' => 0,
            'description' => $device['description'] ?? null,
            'images' => $imagePaths,
            'mobileapi_device_id' => (int) $device['id'],
            'device_type' => (string) ($device['device_type'] ?? $mobileapiTypeFallback),
            'specifications' => $this->mapSpecifications($device),
        ]);

        return ['new_category' => $newCategory, 'new_product' => true];
    }

    /**
     * null = follow API pagination until has_next is false (hard-capped elsewhere).
     */
    private function resolveBrandSyncMaxPages(): ?int
    {
        $raw = Setting::query()->where('key', 'mobileapi_brand_sync_max_pages')->value('value');
        if ($raw === null || $raw === '') {
            return 100;
        }
        $n = (int) $raw;
        if ($n === 0) {
            return null;
        }

        return max(1, min(500, $n));
    }

    private function maybeAttachBrandLogo(Category $category, ?int $manufacturerId, string $apiKey): void
    {
        if ($manufacturerId === null || filled($category->image)) {
            return;
        }

        if (array_key_exists($manufacturerId, $this->manufacturerLogoCache)) {
            $cached = $this->manufacturerLogoCache[$manufacturerId];
            if ($cached !== null && $cached !== '') {
                $category->update(['image' => $cached]);
            }

            return;
        }

        $pathOrUrl = $this->fetchManufacturerLogo($manufacturerId, $apiKey);
        $this->manufacturerLogoCache[$manufacturerId] = $pathOrUrl;

        if ($pathOrUrl !== null && $pathOrUrl !== '') {
            $category->update(['image' => $pathOrUrl]);
        }
    }

    private function fetchManufacturerLogo(int $manufacturerId, string $apiKey): ?string
    {
        try {
            $response = Http::connectTimeout(10)
                ->timeout(20)
                ->retry(1, 800)
                ->withToken($apiKey)
                ->acceptJson()
                ->get(self::BASE_URL . '/manufacturers/'.$manufacturerId.'/');

            if ($response->failed()) {
                return null;
            }

            $data = $response->json();
            if (! is_array($data)) {
                return null;
            }

            foreach (['logo_url', 'logo', 'image_url', 'brand_logo'] as $urlKey) {
                $url = $data[$urlKey] ?? null;
                if (is_string($url) && str_starts_with($url, 'http')) {
                    return $url;
                }
            }

            foreach (['logo_b64', 'image_b64', 'logo'] as $b64Key) {
                $b64 = $data[$b64Key] ?? null;
                if (is_string($b64) && str_contains($b64, 'http')) {
                    continue;
                }
                if (is_string($b64) && $b64 !== '') {
                    $relative = 'categories/brands/'.$manufacturerId.'.png';
                    $stored = $this->storeDecodedBase64($b64, $relative);
                    if ($stored !== null) {
                        return $stored;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::debug('MobileAPI manufacturer logo fetch skipped.', [
                'manufacturer_id' => $manufacturerId,
                'message' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * @return list<string> storage paths on public disk or absolute image URLs
     */
    private function resolveProductImagePaths(array $device, int $deviceId): array
    {
        $urls = array_values(array_filter([
            $device['image_url'] ?? null,
            $device['main_image_url'] ?? null,
        ], fn ($v) => is_string($v) && $v !== ''));

        if ($urls !== []) {
            return $urls;
        }

        $b64 = $device['image_b64'] ?? $device['main_image_b64'] ?? null;
        if (is_string($b64) && $b64 !== '') {
            $stored = $this->storeDecodedBase64($b64, 'products/mobileapi/'.$deviceId.'.png');
            if ($stored !== null) {
                return [$stored];
            }
        }

        return [];
    }

    private function storeDecodedBase64(string $b64, string $relativePath): ?string
    {
        $b64 = preg_replace('#^data:image/[^;]+;base64,#', '', $b64) ?? $b64;
        $binary = base64_decode($b64, true);
        if ($binary === false || $binary === '') {
            return null;
        }

        Storage::disk('public')->put($relativePath, $binary);

        return $relativePath;
    }

    private function resolveBrandName(array $device): string
    {
        $nested = $device['manufacturer'] ?? $device['brand'] ?? null;
        if (is_array($nested) && ! empty($nested['name'])) {
            return trim((string) $nested['name']);
        }

        $n = $device['manufacturer_name'] ?? $device['brand_name'] ?? null;
        if (is_string($n) && trim($n) !== '') {
            return trim($n);
        }

        return 'Unknown';
    }

    private function resolveManufacturerId(array $device): ?int
    {
        $nested = $device['manufacturer'] ?? $device['brand'] ?? null;
        if (is_array($nested) && isset($nested['id'])) {
            return (int) $nested['id'];
        }

        return null;
    }

    private function resolveTypes(string $typesRaw): array
    {
        $types = collect(explode(',', $typesRaw !== '' ? $typesRaw : 'phone'))
            ->map(fn ($item) => strtolower(trim($item)))
            ->filter()
            ->values()
            ->all();

        return $types !== [] ? $types : ['phone'];
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
