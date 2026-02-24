<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_list', function (Blueprint $table) {
            $table->foreignId('purchase_id')->nullable()->after('stock_id')->constrained('purchases')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_list', function (Blueprint $table) {
            $table->dropForeign(['purchase_id']);
        });
    }
};
