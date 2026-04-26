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

        Schema::table('agent_credits', function (Blueprint $table) {
            if (! Schema::hasColumn('agent_credits', 'commission_paid')) {
                $table->decimal('commission_paid', 15, 2)->default(0)->after('paid_amount');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('agent_credits')) {
            return;
        }

        Schema::table('agent_credits', function (Blueprint $table) {
            if (Schema::hasColumn('agent_credits', 'commission_paid')) {
                $table->dropColumn('commission_paid');
            }
        });
    }
};
