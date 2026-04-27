<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('agent_product_transfers')) {
            Schema::create('agent_product_transfers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('from_agent_id');
                $table->unsignedBigInteger('to_agent_id');
                $table->string('status', 32)->default('pending');
                $table->text('message')->nullable();
                $table->text('admin_note')->nullable();
                $table->timestamp('decided_at')->nullable();
                $table->unsignedBigInteger('decided_by')->nullable();
                $table->timestamps();

                $table->index(['status', 'created_at']);
            });
        }

        if (!Schema::hasTable('agent_product_transfer_items')) {
            Schema::create('agent_product_transfer_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('agent_product_transfer_id');
                $table->unsignedBigInteger('product_list_id');
                $table->timestamps();

                $table->unique(['agent_product_transfer_id', 'product_list_id'], 'apti_transfer_product_list_uniq');
                $table->index('agent_product_transfer_id');
                $table->index('product_list_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_product_transfer_items');
        Schema::dropIfExists('agent_product_transfers');
    }
};
