<?php

namespace Database\Seeders;

use App\Services\MobileApiCatalogSyncService;
use Illuminate\Database\Seeder;

class MobileApiCatalogSeeder extends Seeder
{
    public function run(): void
    {
        app(MobileApiCatalogSyncService::class)->syncInsertOnly();
    }
}
