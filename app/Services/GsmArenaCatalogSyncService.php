<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Catalog sync mirroring {@see https://github.com/nordmarin/gsmarena-api} catalog.js + utils.js
 * (GET https://www.gsmarena.com/… HTML, same paths and selectors).
 */
class GsmArenaCatalogSyncService
{
    private const BASE = 'https://www.gsmarena.com';

    /**
     * Brand display names to match against GSMArena makers list (case-insensitive).
     *
     * @var list<string>
     */
    public const DEFAULT_MAJOR_BRANDS = [
        'Samsung', 'Apple', 'Google', 'Xiaomi', 'OnePlus', 'Oppo', 'Vivo', 'Realme',
        'Huawei', 'Motorola', 'Sony', 'Nokia', 'Nothing', 'Honor', 'Asus', 'Lenovo',
        'Tecno', 'Infinix', 'Itel', 'ZTE', 'Meizu', 'Fairphone', 'Sharp', 'Alcatel',
    ];

    /** @var list<array{id: string, name: string, devices: ?int}>|null */
    private ?array $makersCache = null;

    public function syncIfCatalogEmpty(): array
    {
        if (Product::query()->exists()) {
            return ['ok' => true, 'skipped' => true, 'reason' => 'catalog_not_empty'];
        }

        return $this->syncByManufacturers(self::DEFAULT_MAJOR_BRANDS);
    }

    /**
     * @param  list<string>  $manufacturerNames
     * @return array{ok: bool, skipped?: bool, reason?: string, created_categories: int, created_products: int}
     */
    public function syncByManufacturers(array $manufacturerNames, ?int $maxListingPages = null): array
    {
        $maxListingPages ??= $this->resolveBrandSyncMaxPages();
        $createdCategories = 0;
        $createdProducts = 0;

        $makers = $this->fetchMakers();
        if ($makers === []) {
            Log::warning('GSMArena makers list empty or parse failed.');

            return ['ok' => false, 'skipped' => true, 'reason' => 'makers_parse_failed', 'created_categories' => 0, 'created_products' => 0];
        }

        foreach ($manufacturerNames as $label) {
            $label = trim((string) $label);
            if ($label === '') {
                continue;
            }

            $row = $this->matchMakerRow($makers, $label);
            if ($row === null) {
                Log::debug('GSMArena brand not found in makers list.', ['label' => $label]);
                continue;
            }

            $brandSlug = $row['id'];
            $brandName = $row['name'];

            $category = Category::query()->firstOrCreate(
                ['gsmarena_brand_id' => $brandSlug],
                [
                    'name' => $brandName,
                    'image' => null,
                    'mobileapi_type' => 'phone',
                ]
            );

            if ($category->wasRecentlyCreated) {
                $createdCategories++;
            }

            $devices = $this->fetchBrandDevicesAllPages($brandSlug, $maxListingPages);
            foreach ($devices as $dev) {
                if (! isset($dev['id'], $dev['name'])) {
                    continue;
                }

                $deviceSlug = $dev['id'];
                if (Product::query()->where('gsmarena_device_id', $deviceSlug)->exists()) {
                    continue;
                }

                $detail = $this->fetchDeviceDetail($deviceSlug);
                $images = [];
                $img = $detail['img'] ?? $dev['img'] ?? null;
                $img = $this->absolutizeUrl($img);
                if (is_string($img) && $img !== '') {
                    $images[] = $img;
                }

                $description = $detail['description'] ?? $dev['description'] ?? null;
                if (! is_string($description) || $description === '') {
                    $description = $detail['name'] ?? $dev['name'];
                }

                $spec = [];
                if (($detail['quickSpec'] ?? []) !== [] || ($detail['detailSpec'] ?? []) !== []) {
                    $spec = [
                        'quickSpec' => $detail['quickSpec'] ?? [],
                        'detailSpec' => $detail['detailSpec'] ?? [],
                    ];
                }

                Product::query()->create([
                    'category_id' => $category->id,
                    'gsmarena_device_id' => $deviceSlug,
                    'mobileapi_device_id' => null,
                    'name' => $detail['name'] ?? $dev['name'],
                    'brand' => $brandName,
                    'device_type' => 'phone',
                    'price' => 0,
                    'rating' => 5.0,
                    'stock_quantity' => 0,
                    'description' => is_string($description) ? $description : null,
                    'images' => array_slice($images, 0, 5),
                    'specifications' => $spec !== [] ? $spec : null,
                ]);
                $createdProducts++;
            }
        }

        Setting::query()->updateOrCreate(
            ['key' => 'gsmarena_last_synced_at'],
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
     * Lighter sync: first few major brands, few listing pages (used by --by-type / legacy seeder).
     *
     * @return array{ok: bool, skipped?: bool, reason?: string, created_categories: int, created_products: int}
     */
    public function syncInsertOnly(): array
    {
        return $this->syncByManufacturers(
            array_slice(self::DEFAULT_MAJOR_BRANDS, 0, 8),
            2
        );
    }

    private function throttle(): void
    {
        usleep(450000);
    }

    private function resolveBrandSyncMaxPages(): int
    {
        $raw = Setting::query()->where('key', 'mobileapi_brand_sync_max_pages')->value('value');
        if ($raw === null || $raw === '') {
            return 100;
        }
        $n = (int) $raw;
        if ($n === 0) {
            return 500;
        }

        return max(1, min(500, $n));
    }

    private function httpGet(string $pathOrUrl): string
    {
        $this->throttle();
        $url = str_starts_with($pathOrUrl, 'http') ? $pathOrUrl : self::BASE.$pathOrUrl;

        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.9',
        ])
            ->connectTimeout(20)
            ->timeout(60)
            ->retry(2, 1500)
            ->get($url);

        if ($response->failed()) {
            throw new \RuntimeException('GSMArena HTTP '.$response->status().' for '.$url);
        }

        return (string) $response->body();
    }

    private function loadHtml(string $html): DOMDocument
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $wrapped = '<?xml encoding="UTF-8">'.$html;
        $dom->loadHTML($wrapped, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();

        return $dom;
    }

    /**
     * @return list<array{id: string, name: string, devices: ?int}>
     */
    private function fetchMakers(): array
    {
        if ($this->makersCache !== null) {
            return $this->makersCache;
        }

        try {
            $html = $this->httpGet('/makers.php3');
        } catch (\Throwable $e) {
            Log::warning('GSMArena makers fetch failed.', ['message' => $e->getMessage()]);
            $this->makersCache = [];

            return [];
        }

        $dom = $this->loadHtml($html);
        $xp = new DOMXPath($dom);
        $out = [];
        $seen = [];

        foreach ($xp->query('//table//td') as $td) {
            if (! $td instanceof DOMElement) {
                continue;
            }
            $a = $xp->query('.//a[@href]', $td)->item(0);
            if (! $a instanceof DOMElement) {
                continue;
            }
            $href = trim($a->getAttribute('href'));
            if ($href === '' || ! preg_match('/\.php$/i', $href)) {
                continue;
            }
            $id = preg_replace('/\.php$/i', '', basename($href));
            if ($id === '' || isset($seen[$id])) {
                continue;
            }
            $name = trim(preg_replace('/\s+/u', ' ', $a->textContent));
            $name = preg_replace('/\s*devices?\s*$/iu', '', $name);
            $name = trim(preg_replace('/\d+/u', '', $name));
            $name = trim(preg_replace('/\s+/u', ' ', $name));
            if ($name === '') {
                continue;
            }
            $devices = null;
            $span = $xp->query('.//span', $td)->item(0);
            if ($span instanceof DOMElement) {
                $dtxt = preg_replace('/\s*devices?\s*$/iu', '', trim($span->textContent));
                if (is_numeric($dtxt)) {
                    $devices = (int) $dtxt;
                }
            }
            $seen[$id] = true;
            $out[] = ['id' => $id, 'name' => $name, 'devices' => $devices];
        }

        $this->makersCache = $out;

        return $out;
    }

    /**
     * @param  list<array{id: string, name: string, devices: ?int}>  $makers
     * @return array{id: string, name: string, devices: ?int}|null
     */
    private function matchMakerRow(array $makers, string $wantedLabel): ?array
    {
        $w = mb_strtolower(trim($wantedLabel));
        foreach ($makers as $row) {
            if (mb_strtolower($row['name']) === $w) {
                return $row;
            }
        }
        foreach ($makers as $row) {
            if (mb_strtolower($row['id']) === $w) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @return list<array{id: string, name: string, img: ?string, description: ?string}>
     */
    private function fetchBrandDevicesAllPages(string $brandSlug, int $maxPages): array
    {
        $all = [];
        $seen = [];
        $slug = $brandSlug;
        for ($p = 0; $p < $maxPages; $p++) {
            try {
                $html = $this->httpGet('/'.$slug.'.php');
            } catch (\Throwable $e) {
                Log::debug('GSMArena brand page failed.', ['slug' => $slug, 'message' => $e->getMessage()]);
                break;
            }

            $dom = $this->loadHtml($html);
            $xp = new DOMXPath($dom);
            foreach ($xp->query("//ul[contains(concat(' ', normalize-space(@class), ' '), ' makers ')]//li") as $li) {
                if (! $li instanceof DOMElement) {
                    continue;
                }
                $a = $xp->query('.//a[@href]', $li)->item(0);
                if (! $a instanceof DOMElement) {
                    continue;
                }
                $href = $a->getAttribute('href');
                if (! preg_match('/\.php$/i', $href)) {
                    continue;
                }
                $id = preg_replace('/\.php$/i', '', basename($href));
                if ($id === '' || isset($seen[$id])) {
                    continue;
                }
                $span = $xp->query('.//span', $li)->item(0);
                $name = $span instanceof DOMElement ? trim($span->textContent) : trim($a->textContent);
                $imgEl = $xp->query('.//img', $li)->item(0);
                $img = $imgEl instanceof DOMElement ? $imgEl->getAttribute('src') : null;
                $desc = $imgEl instanceof DOMElement ? $imgEl->getAttribute('title') : null;
                $seen[$id] = true;
                $all[] = [
                    'id' => $id,
                    'name' => $name,
                    'img' => $this->absolutizeUrl($img),
                    'description' => is_string($desc) && $desc !== '' ? $desc : null,
                ];
            }

            $next = $this->parseNextListingSlug($dom);
            if ($next === null || $next === $slug) {
                break;
            }
            $slug = $next;
        }

        return $all;
    }

    private function parseNextListingSlug(DOMDocument $dom): ?string
    {
        $xp = new DOMXPath($dom);
        $a = $xp->query("//a[contains(@class,'prevnextbutton')][@title='Next page']")->item(0);
        if (! $a instanceof DOMElement) {
            return null;
        }
        $href = trim($a->getAttribute('href'));
        if ($href === '') {
            return null;
        }
        $href = basename($href);

        return preg_replace('/\.php$/i', '', $href) ?: null;
    }

    /**
     * @return array{name: string, img: ?string, description: ?string, quickSpec: list<array{name: string, value: string}>, detailSpec: list<array{category: string, specifications: list<array{name: string, value: string}>}>}
     */
    private function fetchDeviceDetail(string $deviceSlug): array
    {
        $out = [
            'name' => '',
            'img' => null,
            'description' => null,
            'quickSpec' => [],
            'detailSpec' => [],
        ];

        try {
            $html = $this->httpGet('/'.$deviceSlug.'.php');
        } catch (\Throwable $e) {
            Log::debug('GSMArena device page failed.', ['slug' => $deviceSlug, 'message' => $e->getMessage()]);

            return $out;
        }

        $dom = $this->loadHtml($html);
        $xp = new DOMXPath($dom);

        $nameNode = $xp->query("//*[contains(@class,'specs-phone-name-title')]")->item(0);
        if ($nameNode) {
            $out['name'] = trim($nameNode->textContent);
        }

        $imgNode = $xp->query("//div[contains(@class,'specs-photo-main')]//a//img")->item(0);
        if ($imgNode instanceof DOMElement) {
            $out['img'] = $this->absolutizeUrl($imgNode->getAttribute('src'));
        }

        $displaySize = trim((string) $xp->evaluate("string(//span[@data-spec='displaysize-hl'])"));
        $displayRes = trim((string) $xp->evaluate("string(//div[@data-spec='displayres-hl'])"));
        $camera = trim((string) $xp->evaluate("string(//*[contains(@class,'accent-camera')])"));
        $video = trim((string) $xp->evaluate("string(//div[@data-spec='videopixels-hl'])"));
        $ram = trim((string) $xp->evaluate("string(//*[contains(@class,'accent-expansion')])"));
        $chipset = trim((string) $xp->evaluate("string(//div[@data-spec='chipset-hl'])"));
        $bat = trim((string) $xp->evaluate("string(//*[contains(@class,'accent-battery')])"));
        $batType = trim((string) $xp->evaluate("string(//div[@data-spec='battype-hl'])"));

        $quick = [];
        foreach (
            [
                ['Display size', $displaySize],
                ['Display resolution', $displayRes],
                ['Camera pixels', $camera],
                ['Video pixels', $video],
                ['RAM size', $ram],
                ['Chipset', $chipset],
                ['Battery size', $bat],
                ['Battery type', $batType],
            ] as [$k, $v]
        ) {
            if ($v !== '') {
                $quick[] = ['name' => $k, 'value' => $v];
            }
        }
        $out['quickSpec'] = $quick;

        foreach ($xp->query('//table') as $table) {
            if (! $table instanceof DOMElement) {
                continue;
            }
            $th = $xp->query('.//th', $table)->item(0);
            $category = $th ? trim($th->textContent) : '';
            if ($category === '') {
                continue;
            }
            $specs = [];
            foreach ($xp->query('.//tr', $table) as $tr) {
                if (! $tr instanceof DOMElement) {
                    continue;
                }
                $ttl = $xp->query(".//td[contains(@class,'ttl')]", $tr)->item(0);
                $nfo = $xp->query(".//td[contains(@class,'nfo')]", $tr)->item(0);
                if (! $ttl || ! $nfo) {
                    continue;
                }
                $n = trim($ttl->textContent);
                $v = trim($nfo->textContent);
                if ($n !== '' && $v !== '') {
                    $specs[] = ['name' => $n, 'value' => $v];
                }
            }
            if ($specs !== []) {
                $out['detailSpec'][] = ['category' => $category, 'specifications' => $specs];
            }
        }

        return $out;
    }

    private function absolutizeUrl(?string $u): ?string
    {
        if ($u === null) {
            return null;
        }
        $u = trim($u);
        if ($u === '') {
            return null;
        }
        if (str_starts_with($u, 'http://') || str_starts_with($u, 'https://')) {
            return $u;
        }
        if (str_starts_with($u, '//')) {
            return 'https:'.$u;
        }

        return self::BASE.(str_starts_with($u, '/') ? $u : '/'.$u);
    }
}
