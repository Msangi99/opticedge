<?php

namespace App\Console\Commands;

use App\Services\MobileApiCatalogSyncService;
use Illuminate\Console\Command;

class SyncMobileApiCatalog extends Command
{
    protected $signature = 'catalog:sync-mobileapi {--first-load : Sync only when local catalog is empty}';

    protected $description = 'Sync categories/products from MobileAPI.dev and insert only new devices.';

    public function handle(MobileApiCatalogSyncService $syncService): int
    {
        $result = $this->option('first-load')
            ? $syncService->syncIfCatalogEmpty()
            : $syncService->syncInsertOnly();

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
