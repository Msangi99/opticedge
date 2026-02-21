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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            // New columns
            $table->string('distributor_name')->nullable();
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->date('paid_date')->nullable();
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->string('payment_status')->default('pending'); // pending, partial, paid
            $table->date('date');
            $table->timestamps();
        });

        Schema::create('agent_sales', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name')->nullable(); // New
            $table->string('seller_name')->nullable();   // Replaces agent_name
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity_sold');
            // Financials
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->decimal('selling_price', 15, 2)->nullable();
            $table->decimal('total_purchase_value', 15, 2)->nullable();
            $table->decimal('total_selling_value', 15, 2)->nullable(); // Amount to collect
            $table->decimal('profit', 15, 2)->nullable();
            $table->decimal('commission_paid', 15, 2)->nullable();
            $table->date('date_of_collection')->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            
            $table->integer('stock_remaining')->nullable();
            $table->date('date');
            $table->timestamps();
        });

        Schema::create('distribution_sales', function (Blueprint $table) {
            $table->id();
            $table->string('dealer_name')->nullable();
            $table->string('seller_name')->nullable(); // New
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity_sold');
            // Financials
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->decimal('selling_price', 15, 2)->nullable();
            $table->decimal('total_purchase_value', 15, 2)->nullable();
            $table->decimal('total_selling_value', 15, 2)->nullable(); // Amount to collect
            $table->decimal('profit', 15, 2)->nullable();
            // Payment / Collection
            $table->decimal('to_be_paid', 15, 2)->nullable(); // Could be same as total_selling_value? User listed "To be paid" separate
            $table->decimal('paid_amount', 15, 2)->default(0); // "Paid"
            $table->date('collection_date')->nullable();
            $table->decimal('collected_amount', 15, 2)->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->date('date');
            $table->timestamps();
        });

        Schema::create('shop_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('opening_stock')->default(0);
            $table->integer('quantity_sold')->default(0);
            $table->integer('transfer_quantity')->default(0);
            $table->date('date');
            $table->timestamps();
        });

        Schema::create('payables', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payables');
        Schema::dropIfExists('shop_records');
        Schema::dropIfExists('distribution_sales');
        Schema::dropIfExists('agent_sales');
        Schema::dropIfExists('purchases');
    }
};
