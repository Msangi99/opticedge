<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_list')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE product_list MODIFY imei_number VARCHAR(512) NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE product_list ALTER COLUMN imei_number TYPE VARCHAR(512)');
        }
        // SQLite: column length is not enforced the same way; skip or recreate table — skip for dev SQLite
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_list')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE product_list MODIFY imei_number VARCHAR(255) NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE product_list ALTER COLUMN imei_number TYPE VARCHAR(255)');
        }
    }
};
