<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Purchases may have a null stock_id; product_list rows created from a purchase
     * must allow the same so inserts are not rejected by MySQL.
     */
    public function up(): void
    {
        if (! Schema::hasTable('product_list') || ! Schema::hasColumn('product_list', 'stock_id')) {
            return;
        }

        $this->dropForeignKeysReferencingColumn('product_list', 'stock_id');

        DB::statement('ALTER TABLE product_list MODIFY stock_id BIGINT UNSIGNED NULL');

        if (! $this->tableHasForeignKeyTo('product_list', 'stock_id', 'stocks')) {
            Schema::table('product_list', function (Blueprint $table) {
                $table->foreign('stock_id')->references('id')->on('stocks')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_list') || ! Schema::hasColumn('product_list', 'stock_id')) {
            return;
        }

        $fallbackStockId = DB::table('stocks')->orderBy('id')->value('id');
        if ($fallbackStockId !== null) {
            DB::table('product_list')->whereNull('stock_id')->update(['stock_id' => $fallbackStockId]);
        }

        $this->dropForeignKeysReferencingColumn('product_list', 'stock_id');

        DB::statement('ALTER TABLE product_list MODIFY stock_id BIGINT UNSIGNED NOT NULL');

        if (! $this->tableHasForeignKeyTo('product_list', 'stock_id', 'stocks')) {
            Schema::table('product_list', function (Blueprint $table) {
                $table->foreign('stock_id')->references('id')->on('stocks')->cascadeOnDelete();
            });
        }
    }

    /**
     * Drop every FK on $table.$column (names differ across installs / Laravel versions).
     */
    private function dropForeignKeysReferencingColumn(string $table, string $column): void
    {
        $db = Schema::getConnection()->getDatabaseName();

        $names = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', $db)
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->pluck('CONSTRAINT_NAME')
            ->unique();

        foreach ($names as $name) {
            $safe = str_replace('`', '``', (string) $name);
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$safe}`");
        }
    }

    private function tableHasForeignKeyTo(string $table, string $column, string $referencedTable): bool
    {
        $db = Schema::getConnection()->getDatabaseName();

        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', $db)
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->where('REFERENCED_TABLE_NAME', $referencedTable)
            ->exists();
    }
};
