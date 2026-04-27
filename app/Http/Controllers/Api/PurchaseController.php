<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;

class PurchaseController extends Controller
{
    private function serializePurchase(Purchase $p): array
    {
        $qty = (int) ($p->quantity ?? 0);
        $unit = (float) ($p->unit_price ?? 0);
        $total = (float) ($p->total_amount ?? ($qty * $unit));
        $paid = (float) ($p->paid_amount ?? 0);
        $pending = max(0, $total - $paid);

        return [
            'id' => $p->id,
            'name' => $p->name ?? 'Purchase #' . $p->id,
            // Legacy summary fields used by Stocks page.
            'limit' => $qty,
            'available' => (int) ($p->limit_remaining ?? 0),
            'available_status' => $p->limit_status ?? '–',
            'status' => $p->payment_status ?? '–',
            // Extended details aligned with website purchases table.
            'date' => $p->date,
            'branch_id' => $p->branch_id,
            'branch_name' => $p->branch?->name,
            'distributor_name' => $p->distributor_name,
            'product_name' => $p->product?->name ?? 'N/A',
            'product_category_name' => $p->product?->category?->name ?? null,
            'quantity' => $qty,
            'unit_price' => $unit,
            'total_amount' => $total,
            'paid_date' => $p->paid_date,
            'paid_amount' => $paid,
            'pending_amount' => $pending,
            'sell_price' => $p->sell_price !== null ? (float) $p->sell_price : null,
            'payment_status' => $p->payment_status ?? '–',
            'payment_option_id' => $p->payment_option_id,
            'payment_option_name' => $p->paymentOption?->name,
            'payment_receipt_image' => $p->payment_receipt_image,
            'payment_receipt_url' => $p->payment_receipt_image ? asset('storage/' . $p->payment_receipt_image) : null,
            'created_at' => $p->created_at?->toISOString(),
            'updated_at' => $p->updated_at?->toISOString(),
        ];
    }

    /**
     * List purchases for admin app.
     * Includes stock summary fields plus purchase information shown on website purchases page.
     */
    public function index()
    {
        $purchases = Purchase::with(['product.category', 'stock', 'branch'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn ($p) => $this->serializePurchase($p))
            ->values()
            ->all();

        return response()->json(['data' => $purchases]);
    }

    /**
     * One purchase details for mobile app.
     */
    public function show(int $id)
    {
        $purchase = Purchase::with([
            'product.category',
            'stock',
            'branch',
            'paymentOption',
            'payments.paymentOption',
        ])->findOrFail($id);

        $data = $this->serializePurchase($purchase);
        $data['payments'] = $purchase->payments
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => (float) ($payment->amount ?? 0),
                    'paid_date' => optional($payment->paid_date)->toDateString(),
                    'payment_option_id' => $payment->payment_option_id,
                    'payment_option_name' => $payment->paymentOption?->name,
                    'created_at' => $payment->created_at?->toISOString(),
                ];
            })
            ->values()
            ->all();

        return response()->json(['data' => $data]);
    }

    /**
     * List product_list items for a purchase: model, category name, imei_number.
     */
    public function items(int $id)
    {
        $purchase = Purchase::findOrFail($id);
        $items = $purchase->productListItems()
            ->with('category:id,name')
            ->orderBy('model')
            ->orderBy('imei_number')
            ->get()
            ->map(function ($item) {
                return [
                    'model' => $item->model ?? '–',
                    'category' => $item->category?->name ?? '–',
                    'imei_number' => $item->imei_number ?? '–',
                ];
            })
            ->values()
            ->all();

        return response()->json(['data' => $items]);
    }

    /**
     * List purchases with limit_status = 'pending' and limit_remaining > 0 (for admin app Add Product dropdown).
     * stock_id can be null; returns purchase name and category/model from the purchase's product.
     */
    public function forAddProduct()
    {
        $purchases = Purchase::with(['product.category', 'stock', 'branch'])
            ->where('limit_status', 'pending')
            ->where('limit_remaining', '>', 0)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($p) {
                $product = $p->product;
                $category = $product?->category;

                return [
                    'id' => $p->id,
                    'name' => $p->name ?? 'Purchase #' . $p->id,
                    'stock_id' => $p->stock_id,
                    'stock_name' => $p->stock?->name,
                    'branch_id' => $p->branch_id,
                    'branch_name' => $p->branch?->name,
                    'category_id' => $product?->category_id,
                    'category_name' => $category?->name ?? '–',
                    'model' => $product?->name ?? '–',
                ];
            })
            ->values()
            ->all();

        return response()->json(['data' => $purchases]);
    }

    /**
     * Get all purchase images (gallery) for mobile app image selection.
     * Returns all product images from all purchases, grouped by purchase.
     */
    public function imagesGallery()
    {
        $purchases = Purchase::with('product')
            ->whereHas('product', function ($q) {
                $q->whereNotNull('images');
            })
            ->get()
            ->flatMap(function ($purchase) {
                $product = $purchase->product;
                if (!$product || empty($product->images)) {
                    return [];
                }

                $images = is_string($product->images) ? json_decode($product->images, true) : $product->images;
                if (!is_array($images)) {
                    return [];
                }

                return collect($images)->map(function ($imagePath) use ($purchase, $product) {
                    return [
                        'id' => $purchase->id . '_' . md5($imagePath),
                        'purchase_id' => $purchase->id,
                        'purchase_name' => $purchase->name ?? 'Purchase #' . $purchase->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'image_path' => $imagePath,
                        'image_url' => asset('storage/' . $imagePath),
                    ];
                });
            })
            ->values()
            ->all();

        return response()->json(['data' => $purchases]);
    }
}
