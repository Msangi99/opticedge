<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('mobileapi_type', 50)->nullable()->after('image');
            $table->string('image', 2048)->nullable()->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('mobileapi_device_id')->nullable()->unique()->after('category_id');
            $table->string('device_type', 50)->nullable()->after('brand');
            $table->json('specifications')->nullable()->after('images');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['mobileapi_device_id', 'device_type', 'specifications']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['mobileapi_type']);
            $table->string('image', 100)->nullable()->change();
        });
    }
};
