<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_sales', function (Blueprint $table) {
            if (! Schema::hasColumn('agent_sales', 'commission_expense_id')) {
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
        Schema::table('agent_sales', function (Blueprint $table) {
            if (Schema::hasColumn('agent_sales', 'commission_expense_id')) {
                $table->dropForeign(['commission_expense_id']);
                $table->dropColumn('commission_expense_id');
            }
        });
    }
};
