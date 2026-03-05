<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pending_sales', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name')->nullable();
            $table->string('seller_name')->nullable();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity_sold');
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->decimal('selling_price', 15, 2)->nullable();
            $table->decimal('total_purchase_value', 15, 2)->nullable();
            $table->decimal('total_selling_value', 15, 2)->nullable();
            $table->decimal('profit', 15, 2)->nullable();
            $table->foreignId('payment_option_id')->nullable()->constrained('payment_options')->nullOnDelete();
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_sales');
    }
};
