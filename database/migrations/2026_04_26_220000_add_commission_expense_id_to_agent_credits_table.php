<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('agent_credits') || ! Schema::hasTable('expenses')) {
            return;
        }

        Schema::table('agent_credits', function (Blueprint $table) {
            if (! Schema::hasColumn('agent_credits', 'commission_expense_id')) {
                $table->foreignId('commission_expense_id')
                    ->nullable()
                    ->after('commission_paid')
                    ->constrained('expenses')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('agent_credits')) {
            return;
        }

        Schema::table('agent_credits', function (Blueprint $table) {
            if (Schema::hasColumn('agent_credits', 'commission_expense_id')) {
                $table->dropConstrainedForeignId('commission_expense_id');
            }
        });
    }
};
