<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('gsmarena_brand_id', 191)->nullable()->after('mobileapi_manufacturer_id');
            $table->unique('gsmarena_brand_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('gsmarena_device_id', 191)->nullable()->after('mobileapi_device_id');
            $table->unique('gsmarena_device_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['gsmarena_device_id']);
            $table->dropColumn('gsmarena_device_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['gsmarena_brand_id']);
            $table->dropColumn('gsmarena_brand_id');
        });
    }
};
