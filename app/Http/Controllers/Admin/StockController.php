<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\AgentSale;
use App\Models\DistributionSale;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function purchases()
    {
        $purchases = Purchase::with('product')->latest('date')->get();
        return view('admin.stock.purchases', compact('purchases'));
    }

    public function distribution()
    {
        $distributionSales = DistributionSale::with(['product.category', 'dealer'])->latest('date')->get();
        return view('admin.stock.distribution', compact('distributionSales'));
    }

    public function updateDistributionStatus($id)
    {
        $sale = DistributionSale::findOrFail($id);
        $sale->update(['status' => 'complete']);
        return redirect()->route('admin.stock.distribution')->with('success', 'Distribution sale marked as complete.');
    }

    public function agentSales()
    {
        $agentSales = AgentSale::with(['product.category', 'agent'])->latest('date')->get();
        return view('admin.stock.agent-sales', compact('agentSales'));
    }

    public function updateAgentSaleCommission(Request $request, $id)
    {
        $sale = AgentSale::findOrFail($id);
        $validated = $request->validate(['commission_paid' => 'required|numeric|min:0']);
        $sale->update($validated);
        return redirect()->route('admin.stock.agent-sales')->with('success', 'Commission updated.');
    }

    public function shopRecords()
    {
        $shopRecords = \App\Models\ShopRecord::with('product')->latest('date')->get();
        return view('admin.stock.shop-records', compact('shopRecords'));
    }

    public function payables()
    {
        $payables = \App\Models\Payable::latest('date')->get();
        return view('admin.stock.payables', compact('payables'));
    }

    public function createPurchase()
    {
        // Get all categories for the select dropdown
        $categories = \App\Models\Category::orderBy('name')->get();
            
        // Get unique distributors for the datalist
        $distributors = Purchase::select('distributor_name')
            ->whereNotNull('distributor_name')
            ->distinct()
            ->pluck('distributor_name');
            
        return view('admin.stock.create-purchase', compact('categories', 'distributors'));
    }

    public function storePurchase(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'distributor_name' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'model' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'paid_date' => 'nullable|date',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:pending,paid,partial',
            'images' => 'required|array|min:3',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        // Find or create the product based on category and model name
        $product = \App\Models\Product::firstOrCreate(
            [
                'category_id' => $validated['category_id'],
                'name' => $validated['model']
            ],
            [
                'price' => $validated['unit_price'],
                'stock_quantity' => 0,
                'rating' => 5.0,
                'description' => 'Auto-created from purchase',
                'images' => [],
            ]
        );

        // Upload and save product images
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                if ($image->isValid()) {
                    $path = $image->store('products', 'public');
                    $imagePaths[] = $path;
                }
            }
        }
        if (!empty($imagePaths)) {
            $product->update(['images' => $imagePaths]);
        }

        // Remove non-purchase fields from validated data
        unset($validated['category_id']);
        unset($validated['model']);
        unset($validated['images']);

        // Add product_id
        $validated['product_id'] = $product->id;

        // Calculate total amount (backend validation/calculation)
        $validated['total_amount'] = $validated['quantity'] * $validated['unit_price'];
        
        // Default paid amount to 0 if null
        $validated['paid_amount'] = $validated['paid_amount'] ?? 0;

        Purchase::create($validated);

        // Keep product.stock_quantity in sync so Category Management and dashboards show correct counts
        $product->increment('stock_quantity', $validated['quantity']);

        return redirect()->route('admin.stock.purchases')->with('success', 'Purchase recorded successfully.');
    }

    public function editPurchase($id)
    {
        $purchase = Purchase::with('product.category')->findOrFail($id);
        
        // Get all categories for the select dropdown
        $categories = \App\Models\Category::orderBy('name')->get();
            
        // Get unique distributors for the datalist
        $distributors = Purchase::select('distributor_name')
            ->whereNotNull('distributor_name')
            ->distinct()
            ->pluck('distributor_name');
            
        return view('admin.stock.edit-purchase', compact('purchase', 'categories', 'distributors'));
    }

    public function updatePurchase(Request $request, $id)
    {
        $purchase = Purchase::with('product')->findOrFail($id);

        $rules = [
            'paid_date' => 'nullable|date',
            'paid_amount' => 'nullable|numeric|min:0',
        ];
        if ($request->hasFile('images')) {
            $rules['images'] = 'required|array|min:3';
            $rules['images.*'] = 'image|mimes:jpeg,png,jpg,gif,webp|max:5120';
        }
        $validated = $request->validate($rules);

        // Update product images if new ones uploaded
        if ($request->hasFile('images') && $purchase->product) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                if ($image->isValid()) {
                    $path = $image->store('products', 'public');
                    $imagePaths[] = $path;
                }
            }
            if (!empty($imagePaths)) {
                $purchase->product->update(['images' => $imagePaths]);
            }
        }

        // Auto status from paid amount: pending / partial / paid
        $totalAmount = $purchase->total_amount ?? ($purchase->quantity * $purchase->unit_price);
        $paidAmount = (float) ($validated['paid_amount'] ?? 0);
        $paymentStatus = $paidAmount >= $totalAmount ? 'paid' : ($paidAmount > 0 ? 'partial' : 'pending');

        $purchase->update([
            'paid_date' => $validated['paid_date'] ?? null,
            'paid_amount' => $paidAmount,
            'payment_status' => $paymentStatus,
        ]);

        return redirect()->route('admin.stock.purchases')->with('success', 'Purchase updated successfully.');
    }

    public function destroyPurchase($id)
    {
        $purchase = Purchase::with('product')->findOrFail($id);
        $product = $purchase->product;
        $purchase->delete();
        // Keep product.stock_quantity in sync
        if ($product) {
            $product->update(['stock_quantity' => max(0, $product->stock_quantity - $purchase->quantity)]);
        }

        return redirect()->route('admin.stock.purchases')->with('success', 'Purchase deleted successfully.');
    }

    // Distribution Sales
    public function createDistribution()
    {
        // Fetch products that have been purchased at least once
        $products = \App\Models\Product::whereHas('purchases')->orderBy('name')->get();
        
        // Fetch dealers
        $dealers = \App\Models\User::where('role', 'dealer')->orderBy('name')->get();

        return view('admin.stock.create-distribution', compact('products', 'dealers'));
    }

    public function storeDistribution(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'dealer_id' => 'nullable|exists:users,id',
            'dealer_name' => 'nullable|string|max:255',
            'seller_name' => 'nullable|string|max:255',
            'product_id' => 'required|exists:products,id',
            'quantity_sold' => 'required|integer|min:1',
            'selling_price' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
        ]);

        $service = app(\App\Services\DistributionSaleService::class);
        $buyPrice = $service->getBuyPriceForProduct($validated['product_id']);
        $validated['purchase_price'] = $buyPrice;
        $validated['total_selling_value'] = $validated['quantity_sold'] * $validated['selling_price'];
        $validated['total_purchase_value'] = $validated['quantity_sold'] * $buyPrice;
        $validated['commission'] = 0; // Manual entry: no referrer commission
        $validated['profit'] = $validated['total_selling_value'] - $validated['total_purchase_value'] - 0;
        $validated['status'] = 'pending';
        $validated['paid_amount'] = $validated['paid_amount'] ?? 0;
        $validated['balance'] = $validated['total_selling_value'] - $validated['paid_amount'];
        if (!empty($validated['dealer_id'])) {
            $validated['dealer_name'] = \App\Models\User::find($validated['dealer_id'])->name ?? $validated['dealer_name'] ?? null;
        }

        DistributionSale::create($validated);

        // Keep product.stock_quantity in sync for Category Management / dashboards
        \App\Models\Product::where('id', $validated['product_id'])->decrement('stock_quantity', $validated['quantity_sold']);

        return redirect()->route('admin.stock.distribution')->with('success', 'Distribution sale recorded successfully.');
    }

    public function editDistribution($id)
    {
        $sale = DistributionSale::with(['product.category', 'dealer'])->findOrFail($id);
        return view('admin.stock.edit-distribution', compact('sale'));
    }

    public function updateDistribution(Request $request, $id)
    {
        $sale = DistributionSale::findOrFail($id);
        $validated = $request->validate([
            'paid_amount' => 'nullable|numeric|min:0',
            'collection_date' => 'nullable|date',
        ]);
        $paidAmount = (float) ($validated['paid_amount'] ?? $sale->paid_amount ?? 0);
        $totalSelling = (float) ($sale->total_selling_value ?? 0);
        $balance = max(0, $totalSelling - $paidAmount);

        $sale->update([
            'paid_amount' => $paidAmount,
            'balance' => $balance,
            'collection_date' => $validated['collection_date'] ?? $sale->collection_date,
        ]);

        return redirect()->route('admin.stock.distribution')->with('success', 'Distribution sale updated. Pending amount (balance) updated.');
    }

    // Agent Sales
    public function createAgentSale()
    {
        // Fetch products that have been purchased at least once
        $products = \App\Models\Product::whereHas('purchases')->orderBy('name')->get();

        return view('admin.stock.create-agent-sale', compact('products'));
    }

    public function storeAgentSale(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'customer_name' => 'nullable|string|max:255',
            'seller_name' => 'nullable|string|max:255',
            'product_id' => 'required|exists:products,id',
            'quantity_sold' => 'required|integer|min:1',
            'selling_price' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0', // Note: Schema calls it total_selling_value and balance?
            // Agent sales schema has: total_selling_value, balance. No explicit 'paid_amount' column in create_stock_tables for agnet_sales?
            // Let's check schema again.
            // agent_sales: total_selling_value, balance. commission_paid.
            // It seems for agent sales, "paid_amount" might not be the right term if it's credit sales?
            // "total_selling_value" is Amount to collect.
            // Let's assume for now we just store the basic info.
        ]);

        $validated['total_selling_value'] = $validated['quantity_sold'] * $validated['selling_price'];
        // Assuming balance is initially total value if nothing paid? 
        // Or if we have a paid field? Schema didn't have specific 'paid_amount' for agent_sales like distribution did.
        // It has 'balance'. So maybe start with balance = total.
        $validated['balance'] = $validated['total_selling_value'];

        AgentSale::create($validated);

        // Keep product.stock_quantity in sync for Category Management / dashboards
        \App\Models\Product::where('id', $validated['product_id'])->decrement('stock_quantity', $validated['quantity_sold']);

        return redirect()->route('admin.stock.agent-sales')->with('success', 'Agent sale recorded successfully.');
    }
}
