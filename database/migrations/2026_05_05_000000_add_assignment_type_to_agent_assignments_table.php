<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('agent_assignments')) {
            return;
        }

        if (! Schema::hasColumn('agent_assignments', 'assignment_type')) {
            Schema::table('agent_assignments', function (Blueprint $table) {
                // 'imei' = legacy IMEI-locked assignment, 'total' = quantity-only assignment
                $table->string('assignment_type', 16)->default('imei')->after('product_id');
            });

            // Backfill existing rows explicitly to avoid driver-default surprises.
            DB::table('agent_assignments')->update(['assignment_type' => 'imei']);
        }

        // Replace the (agent_id, product_id) unique index so the same product can be
        // assigned to one agent both ways (one IMEI row + one total row).
        $driver = Schema::getConnection()->getDriverName();
        try {
            Schema::table('agent_assignments', function (Blueprint $table) {
                $table->dropUnique(['agent_id', 'product_id']);
            });
        } catch (\Throwable $e) {
            // Index might not exist (older partial schema). Ignore.
        }

        // Add the new composite unique. Use a short, explicit name to stay portable.
        try {
            Schema::table('agent_assignments', function (Blueprint $table) {
                $table->unique(
                    ['agent_id', 'product_id', 'assignment_type'],
                    'agent_assignments_agent_product_type_unique'
                );
            });
        } catch (\Throwable $e) {
            // If a previous run already created it, ignore.
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('agent_assignments')) {
            return;
        }

        try {
            Schema::table('agent_assignments', function (Blueprint $table) {
                $table->dropUnique('agent_assignments_agent_product_type_unique');
            });
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            Schema::table('agent_assignments', function (Blueprint $table) {
                $table->unique(['agent_id', 'product_id']);
            });
        } catch (\Throwable $e) {
            // ignore
        }

        if (Schema::hasColumn('agent_assignments', 'assignment_type')) {
            Schema::table('agent_assignments', function (Blueprint $table) {
                $table->dropColumn('assignment_type');
            });
        }
    }
};
