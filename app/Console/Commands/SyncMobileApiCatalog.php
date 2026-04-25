<?php

namespace App\Console\Commands;

use App\Services\GsmArenaCatalogSyncService;
use Illuminate\Console\Command;

class SyncMobileApiCatalog extends Command
{
    protected $signature = 'catalog:sync-mobileapi {--first-load : Sync only when local catalog is empty} {--by-type : Lighter multi-brand sync (fewer listing pages) instead of full major-brand run}';

    protected $description = 'Sync brand categories and phone models from GSMArena HTML (same paths/selectors as github.com/nordmarin/gsmarena-api; insert-only by device slug).';

    public function handle(GsmArenaCatalogSyncService $syncService): int
    {
        if ($this->option('first-load')) {
            $result = $syncService->syncIfCatalogEmpty();
        } elseif ($this->option('by-type')) {
            $result = $syncService->syncInsertOnly();
        } else {
            $result = $syncService->syncByManufacturers(GsmArenaCatalogSyncService::DEFAULT_MAJOR_BRANDS);
        }

        if (!($result['ok'] ?? false)) {
            $this->warn('Catalog sync skipped: ' . ($result['reason'] ?? 'unknown_reason'));
            return self::SUCCESS;
        }

        if (($result['skipped'] ?? false) === true) {
            $this->info('Catalog sync skipped: ' . ($result['reason'] ?? 'no_reason'));
            return self::SUCCESS;
        }

        $this->info('GSMArena catalog sync completed.');
        $this->line('Created categories: ' . (int) ($result['created_categories'] ?? 0));
        $this->line('Created products: ' . (int) ($result['created_products'] ?? 0));

        return self::SUCCESS;
    }
}
