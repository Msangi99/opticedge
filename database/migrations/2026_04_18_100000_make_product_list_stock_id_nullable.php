<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Purchases may have a null stock_id; product_list rows created from a purchase
     * must allow the same so inserts are not rejected by MySQL.
     */
    public function up(): void
    {
        if (! Schema::hasTable('product_list')) {
            return;
        }

        Schema::table('product_list', function (Blueprint $table) {
            $table->dropForeign(['stock_id']);
        });

        DB::statement('ALTER TABLE product_list MODIFY stock_id BIGINT UNSIGNED NULL');

        Schema::table('product_list', function (Blueprint $table) {
            $table->foreign('stock_id')->references('id')->on('stocks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_list')) {
            return;
        }

        $fallbackStockId = DB::table('stocks')->orderBy('id')->value('id');
        if ($fallbackStockId !== null) {
            DB::table('product_list')->whereNull('stock_id')->update(['stock_id' => $fallbackStockId]);
        }

        Schema::table('product_list', function (Blueprint $table) {
            $table->dropForeign(['stock_id']);
        });

        DB::statement('ALTER TABLE product_list MODIFY stock_id BIGINT UNSIGNED NOT NULL');

        Schema::table('product_list', function (Blueprint $table) {
            $table->foreign('stock_id')->references('id')->on('stocks')->cascadeOnDelete();
        });
    }
};
