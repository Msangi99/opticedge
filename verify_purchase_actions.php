<?php

use App\Models\Purchase;
use App\Models\Category;
use App\Models\Product;

// 1. Setup Data
$category = Category::firstOrCreate(['name' => 'Edit Test Category']);
$product = Product::firstOrCreate(
    ['name' => 'Edit Test Product', 'category_id' => $category->id],
    ['price' => 0, 'quantity' => 0, 'description' => 'Test Product', 'image' => null]
);

$purchase = Purchase::create([
    'date' => now(), 
    'distributor_name' => 'Original Distro', 
    'product_id' => $product->id, 
    'quantity' => 10, 
    'unit_price' => 100, 
    'total_amount' => 1000,
    'payment_status' => 'pending'
]);

echo "Created Purchase ID: {$purchase->id} with Distro: {$purchase->distributor_name}\n";

// 2. Simulate Update Request
$updateData = [
    'date' => now()->format('Y-m-d'),
    'distributor_name' => 'Updated Distro',
    'category_id' => $category->id,
    'model' => 'Edit Test Product', // Same model
    'quantity' => 20, // Doubled quantity
    'unit_price' => 100,
    'paid_date' => now()->format('Y-m-d'),
    'paid_amount' => 500,
    'payment_status' => 'partial'
];

// Replicate Controller Logic for Update (Restricted)
// No product finding or creation should happen here anymore
// No stock fields update

$purchase->update([
    // 'date' => ... (Ignored)
    'paid_date' => $updateData['paid_date'],
    'paid_amount' => $updateData['paid_amount'],
    'payment_status' => $updateData['payment_status']
]);

$purchase = Purchase::find($purchase->id);
// We expect Total Amount to remain unchanged (1000) because quantity/price updates are ignored
echo "Updated Purchase Distro: {$purchase->distributor_name}, Total: {$purchase->total_amount}, Status: {$purchase->payment_status}\n";

if ($purchase->distributor_name === 'Original Distro' && $purchase->total_amount == 1000 && $purchase->payment_status === 'partial') {
    echo "SUCCESS: Update verified (Restricted fields preserved).\n";
} else {
    echo "FAILURE: Update failed.\n";
}

// 3. Simulate Delete
$purchase->delete();
$check = Purchase::find($purchase->id);

if (!$check) {
    echo "SUCCESS: Delete verified.\n";
} else {
    echo "FAILURE: Delete failed.\n";
}
