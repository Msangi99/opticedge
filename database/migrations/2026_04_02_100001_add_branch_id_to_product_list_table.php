<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('product_list', 'branch_id')) {
            return;
        }

        Schema::table('product_list', function (Blueprint $table) {
            if (Schema::hasTable('branches')) {
                $table->foreignId('branch_id')->nullable()->after('purchase_id')->constrained('branches')->nullOnDelete();
            } else {
                $table->unsignedBigInteger('branch_id')->nullable()->after('purchase_id');
            }
        });

        if (Schema::hasTable('branches') && Schema::hasColumn('purchases', 'branch_id')) {
            $pairs = DB::table('product_list as pl')
                ->join('purchases as p', 'pl.purchase_id', '=', 'p.id')
                ->whereNotNull('p.branch_id')
                ->select('pl.id as pl_id', 'p.branch_id')
                ->get();

            foreach ($pairs as $row) {
                DB::table('product_list')->where('id', $row->pl_id)->update(['branch_id' => $row->branch_id]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('product_list', 'branch_id')) {
            return;
        }

        Schema::table('product_list', function (Blueprint $table) {
            try {
                $table->dropForeign(['branch_id']);
            } catch (\Throwable) {
                // Column may exist without FK (e.g. branches table added later).
            }
            $table->dropColumn('branch_id');
        });
    }
};
