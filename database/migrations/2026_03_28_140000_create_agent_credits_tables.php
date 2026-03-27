<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('agent_credits')) {
            return;
        }

        Schema::create('agent_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->cascadeOnDelete();
            $table->string('customer_name');
            $table->foreignId('product_list_id')->nullable()->constrained('product_list')->nullOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->string('payment_status')->default('pending');
            if (Schema::hasTable('payment_options')) {
                $table->foreignId('payment_option_id')->nullable()->constrained('payment_options')->nullOnDelete();
            } else {
                $table->unsignedBigInteger('payment_option_id')->nullable();
            }
            $table->unsignedInteger('installment_count')->nullable();
            $table->decimal('installment_amount', 15, 2)->nullable();
            $table->date('first_due_date')->nullable();
            $table->text('installment_notes')->nullable();
            $table->date('date');
            $table->date('paid_date')->nullable();
            $table->timestamps();
        });

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

        Schema::table('product_list', function (Blueprint $table) {
            if (! Schema::hasColumn('product_list', 'agent_credit_id')) {
                $table->foreignId('agent_credit_id')->nullable()->constrained('agent_credits')->nullOnDelete();
            }
        });
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
        Schema::dropIfExists('agent_credits');
    }
};
