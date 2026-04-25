<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('mobileapi_manufacturer_id')->nullable()->after('mobileapi_type');
            $table->unique('mobileapi_manufacturer_id');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['mobileapi_manufacturer_id']);
            $table->dropColumn('mobileapi_manufacturer_id');
        });
    }
};
