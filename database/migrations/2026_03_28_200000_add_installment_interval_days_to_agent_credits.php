<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('agent_credits')) {
            return;
        }
        if (Schema::hasColumn('agent_credits', 'installment_interval_days')) {
            return;
        }
        Schema::table('agent_credits', function (Blueprint $table) {
            $table->unsignedSmallInteger('installment_interval_days')->nullable()->after('installment_amount');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('agent_credits') || ! Schema::hasColumn('agent_credits', 'installment_interval_days')) {
            return;
        }
        Schema::table('agent_credits', function (Blueprint $table) {
            $table->dropColumn('installment_interval_days');
        });
    }
};
