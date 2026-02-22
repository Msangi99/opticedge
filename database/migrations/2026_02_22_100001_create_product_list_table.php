<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_list', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('model');
            $table->string('imei_number')->unique();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('sold_at')->nullable();
            $table->foreignId('agent_sale_id')->nullable()->constrained('agent_sales')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_list');
    }
};
