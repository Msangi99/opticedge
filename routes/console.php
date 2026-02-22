<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

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
