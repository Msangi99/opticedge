<?php

namespace Database\Seeders;

use App\Services\GsmArenaCatalogSyncService;
use Illuminate\Database\Seeder;

/**
 * Seeds phone brand categories and device models from GSMArena HTML
 * (same behavior as {@see https://github.com/nordmarin/gsmarena-api} catalog service).
 *
 * Optional setting: mobileapi_brand_sync_max_pages (listing pages per brand; 0 = cap 500).
 */
class MajorBrandMobileCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $result = app(GsmArenaCatalogSyncService::class)->syncByManufacturers(
            GsmArenaCatalogSyncService::DEFAULT_MAJOR_BRANDS
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
