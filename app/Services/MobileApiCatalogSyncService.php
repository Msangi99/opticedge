<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    /**
     * @var array<int, array{name: ?string, logo: ?string}>
     */
    private array $manufacturerProfileCache = [];

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

                    $result = $this->processDeviceForCatalog($device, $apiKey, 'phone', $manufacturerLabel);
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

                    $result = $this->processDeviceForCatalog($device, $apiKey, $type, null);
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
     * @param  string|null  $syncManufacturerLabel  Query label used in /devices/by-manufacturer/ (clean brand, e.g. "Samsung")
     * @return array{new_category: bool, new_product: bool}
     */
    private function processDeviceForCatalog(array $device, string $apiKey, string $mobileapiTypeFallback, ?string $syncManufacturerLabel = null): array
    {
        $modelName = trim((string) $device['name']);
        $brandName = $this->resolveBrandName($device, $syncManufacturerLabel);
        $brandName = $this->stripModelSuffixFromBrand($brandName, $modelName);
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

        $brandForProduct = $this->syncCategoryManufacturerProfile($category, $manufacturerId, $apiKey, $brandName);

        $exists = Product::query()
            ->where('mobileapi_device_id', (int) $device['id'])
            ->exists();

        if ($exists) {
            return ['new_category' => $newCategory, 'new_product' => false];
        }

        $imagePaths = $this->resolveProductImagesWithGalleryFallback($device, (int) $device['id'], $apiKey);

        Product::query()->create([
            'category_id' => $category->id,
            'name' => $modelName,
            'brand' => $brandForProduct,
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

    /**
     * One GET /manufacturers/{id}/ — canonical brand name (category) + logo. Returns display brand for products.
     */
    private function syncCategoryManufacturerProfile(Category $category, ?int $manufacturerId, string $apiKey, string $fallbackBrandName): string
    {
        $displayName = trim($fallbackBrandName);
        $logo = null;

        if ($manufacturerId !== null) {
            $profile = $this->getManufacturerProfile($manufacturerId, $apiKey);
            if (is_string($profile['name']) && trim($profile['name']) !== '') {
                $displayName = trim($profile['name']);
            }
            $logo = $profile['logo'];
        }

        $updates = [];
        if ($category->name !== $displayName) {
            $updates['name'] = $displayName;
        }
        if (! filled($category->image) && $logo !== null && $logo !== '') {
            $updates['image'] = $logo;
        }
        if ($updates !== []) {
            $category->update($updates);
        }

        return $displayName;
    }

    /**
     * @return array{name: ?string, logo: ?string}
     */
    private function getManufacturerProfile(int $manufacturerId, string $apiKey): array
    {
        if (array_key_exists($manufacturerId, $this->manufacturerProfileCache)) {
            return $this->manufacturerProfileCache[$manufacturerId];
        }

        $out = ['name' => null, 'logo' => null];

        try {
            $response = Http::connectTimeout(10)
                ->timeout(20)
                ->retry(1, 800)
                ->withToken($apiKey)
                ->acceptJson()
                ->get(self::BASE_URL . '/manufacturers/'.$manufacturerId.'/');

            if ($response->failed()) {
                $this->manufacturerProfileCache[$manufacturerId] = $out;

                return $out;
            }

            $data = $response->json();
            if (! is_array($data)) {
                $this->manufacturerProfileCache[$manufacturerId] = $out;

                return $out;
            }

            $n = $data['name'] ?? null;
            if (is_string($n) && trim($n) !== '') {
                $out['name'] = trim($n);
            }

            foreach (['logo_url', 'logo', 'image_url', 'brand_logo'] as $urlKey) {
                $url = $data[$urlKey] ?? null;
                if (is_string($url) && str_starts_with($url, 'http')) {
                    $out['logo'] = $url;
                    break;
                }
            }

            if ($out['logo'] === null) {
                foreach (['logo_b64', 'image_b64', 'logo'] as $b64Key) {
                    $b64 = $data[$b64Key] ?? null;
                    if (is_string($b64) && str_contains($b64, 'http')) {
                        continue;
                    }
                    if (is_string($b64) && $b64 !== '') {
                        $relative = 'categories/brands/'.$manufacturerId.'.png';
                        $stored = $this->storeDecodedBase64($b64, $relative);
                        if ($stored !== null) {
                            $out['logo'] = $stored;
                            break;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::debug('MobileAPI manufacturer profile fetch skipped.', [
                'manufacturer_id' => $manufacturerId,
                'message' => $e->getMessage(),
            ]);
        }

        $this->manufacturerProfileCache[$manufacturerId] = $out;

        return $out;
    }

    /**
     * Prefer GET /devices/{id}/images/ (thumbnail, main, gallery); fill from listing if needed.
     * Remote MobileAPI image URLs require auth — we persist bytes to the public disk so admin <img> works.
     *
     * @return list<string> storage paths on public disk (relative to public disk root)
     */
    private function resolveProductImagesWithGalleryFallback(array $device, int $deviceId, string $apiKey): array
    {
        $paths = $this->fetchDeviceImagesFromGallery($deviceId, $apiKey);

        if (count($paths) < 5) {
            foreach ($this->resolveProductImagePathsFromListing($device, $deviceId, $apiKey) as $p) {
                if (count($paths) >= 5) {
                    break;
                }
                if (! in_array($p, $paths, true)) {
                    $paths[] = $p;
                }
            }
        }

        return array_slice($paths, 0, 5);
    }

    /**
     * @return list<string> relative paths on the public disk
     */
    private function resolveProductImagePathsFromListing(array $device, int $deviceId, string $apiKey): array
    {
        $paths = [];
        $urls = array_values(array_filter([
            $device['image_url'] ?? null,
            $device['main_image_url'] ?? null,
        ], fn ($v) => is_string($v) && (str_starts_with($v, 'http://') || str_starts_with($v, 'https://'))));

        $i = 0;
        foreach ($urls as $url) {
            $stored = $this->persistAuthenticatedProductImage(
                $url,
                $apiKey,
                'products/mobileapi/'.$deviceId.'/listing_'.$i
            );
            if ($stored !== null) {
                $paths[] = $stored;
            }
            $i++;
        }

        if ($paths !== []) {
            return $paths;
        }

        $b64 = $device['image_b64'] ?? $device['main_image_b64'] ?? null;
        if (is_string($b64) && $b64 !== '') {
            $stored = $this->storeDecodedBase64($b64, 'products/mobileapi/'.$deviceId.'/listing_b64.png');
            if ($stored !== null) {
                return [$stored];
            }
        }

        return [];
    }

    /**
     * GET https://api.mobileapi.dev/devices/{id}/images/
     *
     * @return list<string> relative paths on the public disk
     */
    private function fetchDeviceImagesFromGallery(int $deviceId, string $apiKey): array
    {
        $paths = [];

        try {
            $response = Http::connectTimeout(12)
                ->timeout(30)
                ->retry(1, 1000)
                ->withToken($apiKey)
                ->acceptJson()
                ->get(self::BASE_URL.'/devices/'.$deviceId.'/images/', [
                    'limit' => 10,
                ]);

            if ($response->failed()) {
                Log::debug('MobileAPI device images request failed.', [
                    'device_id' => $deviceId,
                    'status' => $response->status(),
                ]);

                return [];
            }

            $rows = $this->unwrapDeviceImageRows($response->json());
            if ($rows === []) {
                return [];
            }

            usort($rows, function ($a, $b) {
                $oa = is_array($a) && isset($a['order']) ? (int) $a['order'] : 0;
                $ob = is_array($b) && isset($b['order']) ? (int) $b['order'] : 0;

                return $oa <=> $ob;
            });

            foreach ($rows as $row) {
                if (! is_array($row) || count($paths) >= 5) {
                    break;
                }

                $order = isset($row['order']) ? (int) $row['order'] : 0;
                $type = isset($row['type']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $row['type']) : 'img';
                $rid = isset($row['id']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $row['id']) : 'x';
                $base = 'products/mobileapi/'.$deviceId.'/gallery_o'.$order.'_t'.$type.'_i'.$rid;

                $url = $row['image_url'] ?? null;
                if (is_string($url) && (str_starts_with($url, 'http://') || str_starts_with($url, 'https://'))) {
                    $stored = $this->persistAuthenticatedProductImage($url, $apiKey, $base);
                    if ($stored !== null) {
                        $paths[] = $stored;
                    }

                    continue;
                }

                $b64 = $row['image_b64'] ?? null;
                if (is_string($b64) && $b64 !== '' && ! str_contains($b64, 'http')) {
                    $stored = $this->storeDecodedBase64($b64, $base.'.png');
                    if ($stored !== null) {
                        $paths[] = $stored;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::debug('MobileAPI device images fetch skipped.', [
                'device_id' => $deviceId,
                'message' => $e->getMessage(),
            ]);
        }

        return $paths;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function unwrapDeviceImageRows(mixed $json): array
    {
        if (! is_array($json)) {
            return [];
        }

        if ($json === []) {
            return [];
        }

        if (array_is_list($json)) {
            return $json;
        }

        foreach (['images', 'data', 'results'] as $key) {
            if (isset($json[$key]) && is_array($json[$key])) {
                $inner = $json[$key];

                return array_is_list($inner) ? $inner : array_values($inner);
            }
        }

        return [];
    }

    /**
     * Download image bytes (MobileAPI hosts often require the same API key as JSON endpoints).
     */
    private function persistAuthenticatedProductImage(string $url, string $apiKey, string $relativePathWithoutExtension): ?string
    {
        try {
            $client = Http::connectTimeout(15)
                ->timeout(90)
                ->retry(1, 800)
                ->withHeaders([
                    'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
                ]);

            $response = $client->withToken($apiKey)->get($url);
            if (! $response->successful() || strlen($response->body()) < 32) {
                $response = Http::connectTimeout(15)
                    ->timeout(90)
                    ->withHeaders(['Accept' => 'image/*,*/*'])
                    ->get($url, ['key' => $apiKey]);
            }

            if (! $response->successful() || strlen($response->body()) < 32) {
                return null;
            }

            $ext = $this->guessImageExtensionFromResponse($url, $response);
            $path = $relativePathWithoutExtension.'.'.$ext;
            Storage::disk('public')->put($path, $response->body());

            return $path;
        } catch (\Throwable $e) {
            Log::debug('MobileAPI product image download failed.', [
                'url' => $url,
                'message' => $e->getMessage(),
            ]);
        }

        return null;
    }

    private function guessImageExtensionFromResponse(string $url, \Illuminate\Http\Client\Response $response): string
    {
        $ct = strtolower((string) $response->header('Content-Type'));
        if (str_contains($ct, 'png')) {
            return 'png';
        }
        if (str_contains($ct, 'webp')) {
            return 'webp';
        }
        if (str_contains($ct, 'gif')) {
            return 'gif';
        }
        if (str_contains($ct, 'jpeg') || str_contains($ct, 'jpg')) {
            return 'jpg';
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (is_string($path) && preg_match('/\.(png|jpe?g|gif|webp)(\?|$)/i', $path, $m)) {
            return strtolower($m[1]) === 'jpeg' ? 'jpg' : strtolower($m[1]);
        }

        return 'jpg';
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

    /**
     * Brand for categories / product.brand — never use device model name here.
     * Prefer manufacturer fields; avoid mixing product "brand" strings that include the model.
     *
     * @param  string|null  $syncManufacturerLabel  e.g. "Samsung" from by-manufacturer query
     */
    private function resolveBrandName(array $device, ?string $syncManufacturerLabel = null): string
    {
        if (is_array($device['manufacturer'] ?? null) && ! empty($device['manufacturer']['name'])) {
            return trim((string) $device['manufacturer']['name']);
        }

        if (is_string($device['manufacturer_name'] ?? null) && trim((string) $device['manufacturer_name']) !== '') {
            return trim((string) $device['manufacturer_name']);
        }

        if (is_string($syncManufacturerLabel) && trim($syncManufacturerLabel) !== '') {
            return trim($syncManufacturerLabel);
        }

        if (is_array($device['brand'] ?? null) && ! empty($device['brand']['name'])) {
            return trim((string) $device['brand']['name']);
        }

        if (is_string($device['brand_name'] ?? null) && trim((string) $device['brand_name']) !== '') {
            return trim((string) $device['brand_name']);
        }

        return 'Unknown';
    }

    /**
     * If API sent "Samsung Galaxy S24" as brand and model is "Galaxy S24", trim the model tail.
     */
    private function stripModelSuffixFromBrand(string $brand, string $model): string
    {
        $brand = trim($brand);
        $model = trim($model);
        if ($brand === '' || $model === '') {
            return $brand;
        }

        if (str_ends_with($brand, ' '.$model)) {
            return trim(Str::beforeLast($brand, ' '.$model));
        }

        if (str_ends_with($brand, $model) && mb_strlen($brand) > mb_strlen($model)) {
            return trim(mb_substr($brand, 0, mb_strlen($brand) - mb_strlen($model)), " \t,-–|");
        }

        return $brand;
    }

    private function resolveManufacturerId(array $device): ?int
    {
        if (is_array($device['manufacturer'] ?? null) && isset($device['manufacturer']['id'])) {
            return (int) $device['manufacturer']['id'];
        }

        if (is_array($device['brand'] ?? null) && isset($device['brand']['id'])) {
            return (int) $device['brand']['id'];
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
