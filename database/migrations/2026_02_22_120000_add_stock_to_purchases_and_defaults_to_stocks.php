<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('stock_id')->nullable()->after('id')->constrained('stocks')->nullOnDelete();
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->foreignId('default_category_id')->nullable()->after('stock_limit')->constrained('categories')->nullOnDelete();
            $table->string('default_model')->nullable()->after('default_category_id');
            $table->unsignedInteger('default_quantity')->nullable()->after('default_model');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['stock_id']);
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropForeign(['default_category_id']);
            $table->dropColumn(['default_model', 'default_quantity']);
        });
    }
};
