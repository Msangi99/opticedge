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
        Schema::create('selcompays', function (Blueprint $table) {
            $table->id();
            $table->string('transid', 191)->unique(); // Our transaction reference sent to Selcom
            $table->string('order_id')->nullable(); // Selcom's order ID
            $table->string('phone_number');
            $table->decimal('amount', 12, 2);
            $table->string('payment_status')->default('pending');
            $table->foreignId('local_order_id')->nullable()->constrained('orders')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('selcompays');
    }
};
