<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('db:fresh-seed {--force : Force the operation to run when in production}', function () {
    $this->info('Running migrate:fresh with --seed...');

    $params = ['--seed' => true];
    if ($this->option('force')) {
        $params['--force'] = true;
    }

    $code = $this->call('migrate:fresh', $params);
    if ($code !== 0) {
        $this->error('migrate:fresh exited with code '.$code);

        return $code;
    }

    $this->info('Database refreshed and seeded.');

    return 0;
})->purpose('Drop all tables, re-run migrations, then run database seeders (migrate:fresh --seed)');

Artisan::command('db:export-schema-sql {--path= : Output file path (default: database/sql/full_schema_<driver>.sql)}', function () {
    $connection = DB::connection();
    $driver = $connection->getDriverName();

    if (! in_array($driver, ['mysql', 'mariadb', 'sqlite'], true)) {
        $this->error('Unsupported driver for schema export: '.$driver.'. Use mysql, mariadb, or sqlite.');

        return 1;
    }

    $defaultName = $driver === 'sqlite' ? 'full_schema_sqlite.sql' : 'full_schema_mysql.sql';
    $target = $this->option('path') ?: database_path('sql/'.$defaultName);
    $dir = dirname($target);
    if (! is_dir($dir)) {
        if (! @mkdir($dir, 0755, true) && ! is_dir($dir)) {
            $this->error('Could not create directory: '.$dir);

            return 1;
        }
    }

    $this->info('Exporting schema from connection ['.$connection->getName().'] driver ['.$driver.']...');

    try {
        $exit = Artisan::call('schema:dump', [
            '--path' => $target,
        ]);
        if ($exit !== 0) {
            $this->error('schema:dump exited with code '.$exit);

            return $exit;
        }
        $this->info('Schema written to: '.$target);
        if ($driver === 'sqlite') {
            $this->warn('This file is SQLite DDL. For MySQL manual import, run the same command against a MySQL .env connection.');
        }

        return 0;
    } catch (\Throwable $e) {
        $this->error($e->getMessage());

        return 1;
    }
})->purpose('Dump ALL tables (structure + migrations rows) to database/sql for manual import (uses mysqldump on MySQL)');

Artisan::command('stock:recalc', function () {
    $this->info('Recalculating product stock from purchases, distribution sales, agent sales, and orders...');
    $products = \App\Models\Product::all();
    $updated = 0;
    foreach ($products as $product) {
        $purchased = (int) $product->purchases()->sum('quantity');
        $distSold = (int) \App\Models\DistributionSale::where('product_id', $product->id)->sum('quantity_sold');
        $agentSold = (int) \App\Models\AgentSale::where('product_id', $product->id)->sum('quantity_sold');
        $orderQty = (int) \App\Models\OrderItem::where('product_id', $product->id)->sum('quantity');
        $correct = max(0, $purchased - $distSold - $agentSold - $orderQty);
        if ((int) $product->stock_quantity !== $correct) {
            $product->update(['stock_quantity' => $correct]);
            $updated++;
            $this->line("  {$product->name}: {$product->stock_quantity} → {$correct}");
        }
    }
    $this->info("Done. Updated {$updated} product(s).");
})->purpose('Recalculate product stock_quantity from purchases and sales (fix Category Management counts)');

// Update opening balance daily at 6:00 PM
Schedule::command('payment-options:update-opening-balance')
    ->dailyAt('18:00')
    ->timezone('Africa/Dar_es_Salaam')
    ->description('Update opening balance for all payment options daily at 6:00 PM');