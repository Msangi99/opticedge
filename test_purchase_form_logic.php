<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;

// 1. Create a dummy category
$category = Category::firstOrCreate(['name' => 'Test Electronics']);
echo "Category: {$category->name} (ID: {$category->id})\n";

// 2. Simulate Form Input
$input = [
    'date' => now()->format('Y-m-d'),
    'distributor_name' => 'Test Distro',
    'category_id' => $category->id,
    'model' => 'New Gadget 3000', // This product does not exist yet
    'quantity' => 5,
    'unit_price' => 100,
    'payment_status' => 'pending'
];

// 3. Replicate Controller Logic
$product = Product::firstOrCreate(
    [
        'category_id' => $input['category_id'],
        'name' => $input['model']
    ],
    [
        'price' => 0,
        'quantity' => 0,
        'description' => 'Auto-created from purchase'
    ]
);

echo "Product: {$product->name} (ID: {$product->id})\n";

if ($product->wasRecentlyCreated) {
    echo "SUCCESS: Product was auto-created.\n";
} else {
    echo "INFO: Product already existed.\n";
}

// 4. Create Purchase
$purchase = Purchase::create([
    'date' => $input['date'],
    'distributor_name' => $input['distributor_name'],
    'product_id' => $product->id,
    'quantity' => $input['quantity'],
    'unit_price' => $input['unit_price'],
    'total_amount' => $input['quantity'] * $input['unit_price'],
    'paid_date' => null,
    'paid_amount' => 0,
    'payment_status' => $input['payment_status']
]);

echo "Purchase ID: {$purchase->id} created for Product ID: {$purchase->product_id}\n";
