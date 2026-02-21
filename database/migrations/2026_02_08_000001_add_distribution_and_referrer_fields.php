<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('referred_by')->nullable()->after('how_did_you_hear')->constrained('users')->nullOnDelete();
        });

        Schema::table('distribution_sales', function (Blueprint $table) {
            $table->foreignId('dealer_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->after('dealer_id')->constrained()->nullOnDelete();
            $table->decimal('commission', 15, 2)->default(0)->after('profit');
            $table->string('status')->default('pending')->after('commission'); // pending, complete
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referred_by']);
        });

        Schema::table('distribution_sales', function (Blueprint $table) {
            $table->dropForeign(['dealer_id']);
            $table->dropForeign(['order_id']);
            $table->dropColumn(['dealer_id', 'order_id', 'commission', 'status']);
        });
    }
};
