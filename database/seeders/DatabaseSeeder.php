<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Setting;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Setting::updateOrCreate(['key' => 'mobileapi_device_types'], ['value' => 'phone']);
        Setting::updateOrCreate(['key' => 'mobileapi_sync_pages'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'mobileapi_brand_sync_max_pages'], ['value' => '100']);

        $this->call(PaymentChannelSeeder::class);
        $this->call(MajorBrandMobileCatalogSeeder::class);

        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@opticedgeafrica.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }
}
