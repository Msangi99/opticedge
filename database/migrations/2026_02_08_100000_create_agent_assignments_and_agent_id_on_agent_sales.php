<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity_assigned')->default(0);
            $table->integer('quantity_sold')->default(0);
            $table->timestamps();
            $table->unique(['agent_id', 'product_id']);
        });

        Schema::table('agent_sales', function (Blueprint $table) {
            $table->foreignId('agent_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('agent_sales', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
        });
        Schema::dropIfExists('agent_assignments');
    }
};
