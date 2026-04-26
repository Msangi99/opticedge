<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('categories') && !Schema::hasTable('brands')) {
            Schema::rename('categories', 'brands');
        }

        if (Schema::hasTable('products') && !Schema::hasTable('models')) {
            Schema::rename('products', 'models');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('models') && !Schema::hasTable('products')) {
            Schema::rename('models', 'products');
        }

        if (Schema::hasTable('brands') && !Schema::hasTable('categories')) {
            Schema::rename('brands', 'categories');
        }
    }
};
