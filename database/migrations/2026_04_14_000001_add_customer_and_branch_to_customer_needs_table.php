<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customer_needs')) {
            return;
        }

        Schema::table('customer_needs', function (Blueprint $table) {
            if (! Schema::hasColumn('customer_needs', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('product_id');
            }
            if (! Schema::hasColumn('customer_needs', 'customer_phone')) {
                $table->string('customer_phone', 64)->nullable()->after('customer_name');
            }
            if (! Schema::hasColumn('customer_needs', 'branch_id')) {
                if (Schema::hasTable('branches')) {
                    $table->foreignId('branch_id')->nullable()->after('customer_phone')->constrained('branches')->nullOnDelete();
                } else {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('customer_phone');
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('customer_needs')) {
            return;
        }

        Schema::table('customer_needs', function (Blueprint $table) {
            if (Schema::hasColumn('customer_needs', 'branch_id')) {
                try {
                    $table->dropForeign(['branch_id']);
                } catch (\Throwable) {
                }
                $table->dropColumn('branch_id');
            }
            if (Schema::hasColumn('customer_needs', 'customer_phone')) {
                $table->dropColumn('customer_phone');
            }
            if (Schema::hasColumn('customer_needs', 'customer_name')) {
                $table->dropColumn('customer_name');
            }
        });
    }
};
