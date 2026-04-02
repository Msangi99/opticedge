<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_product_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_agent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('to_agent_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 32)->default('pending'); // pending, approved, rejected, cancelled
            $table->text('message')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('agent_product_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_product_transfer_id')->constrained('agent_product_transfers')->cascadeOnDelete();
            $table->foreignId('product_list_id')->constrained('product_list')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['agent_product_transfer_id', 'product_list_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_product_transfer_items');
        Schema::dropIfExists('agent_product_transfers');
    }
};
