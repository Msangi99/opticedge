<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Expenses now use payment_option_id; cash_used is optional (backward compat).
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE expenses MODIFY cash_used VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE expenses MODIFY cash_used VARCHAR(255) NOT NULL');
    }
};
