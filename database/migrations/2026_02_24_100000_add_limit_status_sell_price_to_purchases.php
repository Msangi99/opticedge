<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('limit_status')->default('pending')->after('date'); // pending, complete
            $table->unsignedInteger('limit_remaining')->nullable()->after('limit_status'); // decrements when app adds IMEI
            $table->decimal('sell_price', 15, 2)->nullable()->after('limit_remaining');
        });

        // Backfill: existing purchases get limit_remaining = quantity
        \Illuminate\Support\Facades\DB::table('purchases')->update([
            'limit_remaining' => \Illuminate\Support\Facades\DB::raw('COALESCE(limit_remaining, quantity)'),
            'limit_status' => \Illuminate\Support\Facades\DB::raw("COALESCE(limit_status, 'pending')"),
        ]);
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['limit_status', 'limit_remaining', 'sell_price']);
        });
    }
};
