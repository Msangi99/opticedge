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
        if (Schema::hasColumn('agent_credits', 'customer_phone')) {
            return;
        }
        Schema::table('agent_credits', function (Blueprint $table) {
            $table->string('customer_phone', 64)->nullable()->after('customer_name');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('agent_credits') || ! Schema::hasColumn('agent_credits', 'customer_phone')) {
            return;
        }
        Schema::table('agent_credits', function (Blueprint $table) {
            $table->dropColumn('customer_phone');
        });
    }
};
