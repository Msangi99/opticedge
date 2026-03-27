<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Completes agent credit setup if 2026_03_28_140000 partially failed (e.g. before payment_options existed).
     */
    public function up(): void
    {
        if (! Schema::hasTable('agent_credit_payments')) {
            Schema::create('agent_credit_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('agent_credit_id')->constrained('agent_credits')->cascadeOnDelete();
                if (Schema::hasTable('payment_options')) {
                    $table->foreignId('payment_option_id')->nullable()->constrained('payment_options')->nullOnDelete();
                } else {
                    $table->unsignedBigInteger('payment_option_id')->nullable();
                }
                $table->decimal('amount', 15, 2);
                $table->date('paid_date')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('product_list') && ! Schema::hasColumn('product_list', 'agent_credit_id')) {
            Schema::table('product_list', function (Blueprint $table) {
                $table->foreignId('agent_credit_id')->nullable()->constrained('agent_credits')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('product_list', 'agent_credit_id')) {
            Schema::table('product_list', function (Blueprint $table) {
                $table->dropForeign(['agent_credit_id']);
                $table->dropColumn('agent_credit_id');
            });
        }
        Schema::dropIfExists('agent_credit_payments');
    }
};
