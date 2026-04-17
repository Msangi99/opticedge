<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add payment_option_id when missing (safe if column was added manually or migration partially ran).
     */
    public function up(): void
    {
        if (Schema::hasColumn('purchases', 'payment_option_id')) {
            return;
        }

        Schema::table('purchases', function (Blueprint $table) {
            if (Schema::hasTable('payment_options')) {
                $table->foreignId('payment_option_id')
                    ->nullable()
                    ->after('payment_receipt_image')
                    ->constrained('payment_options')
                    ->nullOnDelete();
            } else {
                $table->unsignedBigInteger('payment_option_id')->nullable()->after('payment_receipt_image');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('purchases', 'payment_option_id')) {
            return;
        }

        try {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropForeign(['payment_option_id']);
            });
        } catch (\Throwable $e) {
            // Column may exist without the expected FK name
        }

        if (Schema::hasColumn('purchases', 'payment_option_id')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropColumn('payment_option_id');
            });
        }
    }
};
