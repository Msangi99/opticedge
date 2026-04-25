<?php

namespace Database\Seeders;

use App\Services\GsmArenaCatalogSyncService;
use Illuminate\Database\Seeder;

class MobileApiCatalogSeeder extends Seeder
{
    public function run(): void
    {
        app(GsmArenaCatalogSyncService::class)->syncInsertOnly();
    }
}
