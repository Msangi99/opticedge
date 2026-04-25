<?php

namespace App\Console\Commands;

use App\Services\MobileApiCatalogSyncService;
use Illuminate\Console\Command;

class SyncMobileApiCatalog extends Command
{
    protected $signature = 'catalog:sync-mobileapi {--first-load : Sync only when local catalog is empty} {--by-type : Use devices/by-type (settings) instead of major brands by manufacturer}';

    protected $description = 'Sync brand categories and phone models from MobileAPI.dev (insert-only by device id).';

    public function handle(MobileApiCatalogSyncService $syncService): int
    {
        if ($this->option('first-load')) {
            $result = $syncService->syncIfCatalogEmpty();
        } elseif ($this->option('by-type')) {
            $result = $syncService->syncInsertOnly();
        } else {
            $result = $syncService->syncByManufacturers(MobileApiCatalogSyncService::DEFAULT_MAJOR_BRANDS);
        }

        if (!($result['ok'] ?? false)) {
            $this->warn('MobileAPI sync skipped: ' . ($result['reason'] ?? 'unknown_reason'));
            return self::SUCCESS;
        }

        if (($result['skipped'] ?? false) === true) {
            $this->info('MobileAPI sync skipped: ' . ($result['reason'] ?? 'no_reason'));
            return self::SUCCESS;
        }

        $this->info('MobileAPI sync completed.');
        $this->line('Created categories: ' . (int) ($result['created_categories'] ?? 0));
        $this->line('Created products: ' . (int) ($result['created_products'] ?? 0));

        return self::SUCCESS;
    }
}
