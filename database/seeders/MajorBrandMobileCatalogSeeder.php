<?php

namespace Database\Seeders;

use App\Services\MobileApiCatalogSyncService;
use Illuminate\Database\Seeder;

/**
 * Seeds phone brand categories and device models via MobileAPI.dev
 * {@see https://mobileapi.dev/docs/} GET /devices/by-manufacturer/
 *
 * Requires settings: mobileapi_api_key. Optional: mobileapi_brand_sync_max_pages (0 = all pages up to 500 cap).
 */
class MajorBrandMobileCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $result = app(MobileApiCatalogSyncService::class)->syncByManufacturers(
            MobileApiCatalogSyncService::DEFAULT_MAJOR_BRANDS
        );

        if (($result['skipped'] ?? false) === true) {
            $this->command?->warn('Major brand catalog skipped: '.($result['reason'] ?? 'unknown'));

            return;
        }

        $this->command?->info(sprintf(
            'Major brand catalog: +%d categories, +%d products.',
            (int) ($result['created_categories'] ?? 0),
            (int) ($result['created_products'] ?? 0)
        ));
    }
}
