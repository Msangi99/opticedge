<?php

use App\Models\Purchase;
use App\Models\Category;
use App\Models\Product;

// 1. Setup Data
// Create a product if needed
$category = Category::firstOrCreate(['name' => 'General']);
$product = Product::firstOrCreate(['name' => 'Test Item', 'category_id' => $category->id], ['price' => 10, 'quantity' => 100]);

// Create purchases with different distributors
Purchase::create([
    'date' => now(), 'distributor_name' => 'Alpha Suppliers', 'product_id' => $product->id, 
    'quantity' => 10, 'unit_price' => 50, 'total_amount' => 500
]);
Purchase::create([
    'date' => now(), 'distributor_name' => 'Beta Distributors', 'product_id' => $product->id, 
    'quantity' => 5, 'unit_price' => 50, 'total_amount' => 250
]);
Purchase::create([
    'date' => now(), 'distributor_name' => 'Alpha Suppliers', 'product_id' => $product->id, 
    'quantity' => 20, 'unit_price' => 50, 'total_amount' => 1000
]);
Purchase::create([
    'date' => now(), 'distributor_name' => 'Gamma Traders', 'product_id' => $product->id, 
    'quantity' => 1, 'unit_price' => 50, 'total_amount' => 50
]);

// 2. Run the Logic from StockController
$distributors = Purchase::select('distributor_name')
    ->whereNotNull('distributor_name')
    ->distinct()
    ->pluck('distributor_name');

// 3. Output Results
echo "Found Distributors:\n";
foreach ($distributors as $d) {
    echo "- $d\n";
}

if ($distributors->contains('Alpha Suppliers') && $distributors->contains('Beta Distributors') && $distributors->count() >= 3) {
    echo "SUCCESS: Distinct distributors fetched correctly.\n";
} else {
    echo "FAILURE: Logical error in fetching distributors.\n";
}
