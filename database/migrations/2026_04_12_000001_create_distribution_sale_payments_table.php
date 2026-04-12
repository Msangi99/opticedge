<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribution_sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribution_sale_id')->constrained('distribution_sales')->cascadeOnDelete();
            $table->foreignId('payment_option_id')->nullable()->constrained('payment_options')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('paid_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_sale_payments');
    }
};
