<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('payment_option_id')->nullable()->after('amount')->constrained('payment_options')->nullOnDelete();
        });
        
        // Make cash_used nullable since we're using payment_option_id now
        DB::statement('ALTER TABLE expenses MODIFY cash_used VARCHAR(255) NULL');
        
        // Keep cash_used for backward compatibility, but we'll use payment_option_id going forward
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['payment_option_id']);
            $table->dropColumn('payment_option_id');
        });
    }
};
