<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('agent_assignments')) {
            return;
        }

        Schema::table('agent_assignments', function (Blueprint $table) {
            if (! Schema::hasColumn('agent_assignments', 'purchase_id')) {
                $table->foreignId('purchase_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
            }
        });

        try {
            Schema::table('agent_assignments', function (Blueprint $table) {
                $table->dropUnique('agent_assignments_agent_product_type_unique');
            });
        } catch (\Throwable $e) {
            // Ignore when index does not exist.
        }

        try {
            Schema::table('agent_assignments', function (Blueprint $table) {
                $table->unique(
                    ['agent_id', 'product_id', 'assignment_type', 'purchase_id'],
                    'agent_assignments_agent_product_type_purchase_unique'
                );
            });
        } catch (\Throwable $e) {
            // Ignore when index already exists.
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('agent_assignments')) {
            return;
        }

        try {
            Schema::table('agent_assignments', function (Blueprint $table) {
                $table->dropUnique('agent_assignments_agent_product_type_purchase_unique');
            });
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            Schema::table('agent_assignments', function (Blueprint $table) {
                $table->unique(
                    ['agent_id', 'product_id', 'assignment_type'],
                    'agent_assignments_agent_product_type_unique'
                );
            });
        } catch (\Throwable $e) {
            // ignore
        }

        Schema::table('agent_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('agent_assignments', 'purchase_id')) {
                $table->dropConstrainedForeignId('purchase_id');
            }
        });
    }
};
