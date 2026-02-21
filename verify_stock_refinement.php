<?php

use App\Models\Purchase;
use App\Models\AgentSale;
use App\Models\DistributionSale;
use App\Models\ShopRecord;
use App\Models\Payable;
use App\Models\Product;
use App\Models\Category;

// Ensure a category exists
$category = Category::firstOrCreate(['name' => 'Electronics']);

// Ensure a product exists
$product = Product::firstOrCreate(
    ['name' => 'Refined Test Phone'],
    [
        'category_id' => $category->id,
        'image' => 'default.png',
        'description' => 'Test phone for stock verification',
        'price' => 500,
        'stock_quantity' => 100
    ]
);

echo "Product ID: " . $product->id . "\n";

// 1. Create Purchase with new fields
$purchase = Purchase::create([
    'product_id' => $product->id,
    'quantity' => 10,
    'unit_price' => 450.00,
    'distributor_name' => 'Tech Distributors Inc.', // New
    'total_amount' => 4500.00, // New
    'paid_date' => now(), // New
    'paid_amount' => 2000.00, // New
    'payment_status' => 'partial', // New
    'date' => now(),
]);
echo "Purchase created with Distributor: " . $purchase->distributor_name . "\n";

// 2. Create Agent Sale (Detailed)
$agentSale = AgentSale::create([
    'customer_name' => 'Alice Wonderland',
    'seller_name' => 'Agent Smith',
    'product_id' => $product->id,
    'quantity_sold' => 2,
    'purchase_price' => 450.00,
    'selling_price' => 600.00,
    'total_purchase_value' => 900.00,
    'total_selling_value' => 1200.00,
    'profit' => 300.00,
    'commission_paid' => 50.00,
    'date_of_collection' => now(),
    'balance' => 0.00,
    // 'stock_remaining' => 8, // Optional depending on logic
    'date' => now(),
]);
echo "Agent Sale created for Customer: " . $agentSale->customer_name . "\n";

// 3. Create Shop Record (New Table)
$shopRecord = ShopRecord::create([
    'product_id' => $product->id,
    'opening_stock' => 100,
    'quantity_sold' => 5,
    'transfer_quantity' => 0,
    'date' => now(),
]);
echo "Shop Record created. Opening Stock: " . $shopRecord->opening_stock . "\n";

// 4. Create Distribution Sale (Detailed)
$distributionSale = DistributionSale::create([
    'dealer_name' => 'Mega Electronics',
    'seller_name' => 'John Doe',
    'product_id' => $product->id,
    'quantity_sold' => 5,
    'purchase_price' => 450.00,
    'selling_price' => 550.00,
    'total_purchase_value' => 2250.00,
    'total_selling_value' => 2750.00,
    'profit' => 500.00,
    'to_be_paid' => 2750.00,
    'paid_amount' => 2000.00,
    'collection_date' => now(),
    'collected_amount' => 2000.00,
    'balance' => 750.00,
    'date' => now(),
]);
echo "Distribution Sale created for Dealer: " . $distributionSale->dealer_name . "\n";

// 5. Create Payable (New Table)
$payable = Payable::create([
    'item_name' => 'Electricity Bill',
    'amount' => 150.50,
    'date' => now(),
]);
echo "Payable created: " . $payable->item_name . " - " . $payable->amount . "\n";

// Verification Counts
echo "\n--- Verification Counts ---\n";
echo "Purchases: " . Purchase::count() . "\n";
echo "Agent Sales: " . AgentSale::count() . "\n";
echo "Distribution Sales: " . DistributionSale::count() . "\n";
echo "Shop Records: " . ShopRecord::count() . "\n";
echo "Payables: " . Payable::count() . "\n";
