<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pending_sales', function (Blueprint $table) {
            $table->foreignId('seller_id')->nullable()->after('seller_name')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pending_sales', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
        });
    }
};
