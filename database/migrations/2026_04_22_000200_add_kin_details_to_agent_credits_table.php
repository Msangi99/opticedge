<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_credits', function (Blueprint $table) {
            if (! Schema::hasColumn('agent_credits', 'kin_name')) {
                $table->string('kin_name', 255)->nullable()->after('customer_phone');
            }
            if (! Schema::hasColumn('agent_credits', 'kin_phone')) {
                $table->string('kin_phone', 64)->nullable()->after('kin_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('agent_credits', function (Blueprint $table) {
            if (Schema::hasColumn('agent_credits', 'kin_phone')) {
                $table->dropColumn('kin_phone');
            }
            if (Schema::hasColumn('agent_credits', 'kin_name')) {
                $table->dropColumn('kin_name');
            }
        });
    }
};
