<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensures payment_option_id exists on agent_sales (fixes DBs that skipped the earlier migration).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('agent_sales', 'payment_option_id')) {
            Schema::table('agent_sales', function (Blueprint $table) {
                if (Schema::hasTable('payment_options')) {
                    $table->foreignId('payment_option_id')
                        ->nullable()
                        ->after('balance')
                        ->constrained('payment_options')
                        ->nullOnDelete();
                } else {
                    $table->unsignedBigInteger('payment_option_id')->nullable()->after('balance');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('agent_sales', 'payment_option_id')) {
            Schema::table('agent_sales', function (Blueprint $table) {
                $table->dropForeign(['payment_option_id']);
            });
            Schema::table('agent_sales', function (Blueprint $table) {
                $table->dropColumn('payment_option_id');
            });
        }
    }
};
