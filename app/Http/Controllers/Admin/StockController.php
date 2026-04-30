<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\AgentSale;
use App\Models\DistributionSale;
use App\Models\DistributionSalePayment;
use App\Models\Expense;
use App\Models\PaymentOption;
use App\Models\Product;
use App\Models\ProductListItem;
use App\Models\Stock;
use App\Models\Vendor;
use App\Models\Setting;
use App\Services\BarcodeImageDecoder;
use App\Support\ImeiListParser;
use App\Support\PdfDownload;
use App\Support\PurchaseInvoiceNumber;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class StockController extends Controller
{
    /**
     * Stocks page: list all stocks with stock quantity, added (from purchases), and status.
     */
    public function stocks()
    {
        $usingPurchases = false;

        try {
            // Get all stocks
            $stocks = Stock::orderBy('name')->get();
            
            $stocksData = $stocks->map(function ($stock) {
                try {
                    // Added quantity must reflect real devices already entered (IMEI rows),
                    // not purchase target quantity.
                    $added = (int) ProductListItem::where('stock_id', $stock->id)->count();
                } catch (\Exception $e) {
                    Log::warning('Error calculating added quantity for stock ' . $stock->id . ': ' . $e->getMessage());
                    $added = 0;
                }

                $stockQuantity = (int) ($stock->stock_limit ?? 0);
                $status = ($stockQuantity > 0 && $stockQuantity == $added) ? 'complete' : 'pending';

                $imeiCount = $added;
                $unsoldCount = (int) ProductListItem::where('stock_id', $stock->id)->whereNull('sold_at')->count();

                return (object) [
                    'id' => $stock->id,
                    'name' => $stock->name ?? 'Unnamed Stock',
                    'stock_quantity' => $stockQuantity,
                    'added' => (int) $added,
                    'status' => $status,
                    'stock_status' => $imeiCount === 0
                        ? 'pending'
                        : ($unsoldCount > 0 ? 'in_stock' : 'sold_out'),
                    'imei_count' => $imeiCount,
                ];
            });
            
            // If no stocks exist but purchases exist, build rows from purchases instead
            if ($stocksData->isEmpty()) {
                $purchases = Purchase::withCount([
                    'productListItems',
                    'productListItems as unsold_items_count' => function ($q) {
                        $q->whereNull('sold_at');
                    },
                ])->orderBy('date', 'desc')->get();

                if ($purchases->isNotEmpty()) {
                    $usingPurchases = true;

                    $stocksData = $purchases->map(function ($purchase) {
                        $limit = (int) ($purchase->quantity ?? 0);
                        $added = (int) ($purchase->product_list_items_count ?? 0);
                        $status = ($limit > 0 && $added === $limit) ? 'complete' : 'pending';
                        $unsoldCount = (int) ($purchase->unsold_items_count ?? 0);

                        return (object) [
                            'id' => $purchase->id,
                            'name' => $purchase->name ?? 'Unnamed Purchase',
                            'stock_quantity' => $limit,
                            'added' => $added,
                            'status' => $status,
                            'stock_status' => $added === 0
                                ? 'pending'
                                : ($unsoldCount > 0 ? 'in_stock' : 'sold_out'),
                            'imei_count' => $added,
                        ];
                    });
                }
            }
        } catch (\Exception $e) {
            Log::error('Error loading stocks: ' . $e->getMessage());
            $stocksData = collect([]);
        }

        $stockDashboard = [
            'rows' => $stocksData->count(),
            'total_limit' => (int) $stocksData->sum('stock_quantity'),
            'total_added' => (int) $stocksData->sum('added'),
            'complete' => $stocksData->where('status', 'complete')->count(),
            'pending' => $stocksData->where('status', 'pending')->count(),
        ];

        return view('admin.stock.stocks', [
            'stocks' => $stocksData,
            'hasPurchases' => $usingPurchases,
            'stockDashboard' => $stockDashboard,
        ]);
    }

    /**
     * Show items for one purchase: model, category, IMEI (product_list rows for this purchase).
     */
    public function showPurchase($id)
    {
        $purchase = Purchase::findOrFail($id);
        $items = $purchase->productListItems()
            ->with([
                'category:id,name',
                'product:id,name,category_id',
                'stock:id,name',
                'agentProductListAssignment.agent:id,name,email',
                'agentCredit.agent:id,name,email',
                'agentCredit.paymentOption:id,name',
                'pendingSale',
                'agentSale.agent:id,name,email',
            ])
            ->orderBy('model')
            ->orderBy('imei_number')
            ->get();

        return view('admin.stock.purchase-show', [
            'purchase' => $purchase,
            'items' => $items,
        ]);
    }

    /**
     * Delete one IMEI row from a purchase details page.
     */
    public function destroyPurchaseItem(Purchase $purchase, ProductListItem $productListItem)
    {
        if ((int) $productListItem->purchase_id !== (int) $purchase->id) {
            return redirect()
                ->route('admin.stock.purchase.show', $purchase->id)
                ->withErrors(['error' => 'This IMEI does not belong to the selected purchase.']);
        }

        if ($productListItem->sold_at || $productListItem->agent_sale_id || $productListItem->agent_credit_id || $productListItem->pending_sale_id) {
            return redirect()
                ->route('admin.stock.purchase.show', $purchase->id)
                ->withErrors(['error' => 'Cannot delete IMEI that is already linked to a sale or credit.']);
        }

        if ($productListItem->agentProductListAssignment()->exists()) {
            return redirect()
                ->route('admin.stock.purchase.show', $purchase->id)
                ->withErrors(['error' => 'Cannot delete IMEI that is assigned to an agent.']);
        }

        DB::transaction(function () use ($purchase, $productListItem) {
            $productListItem->delete();

            if (Schema::hasColumn('purchases', 'limit_remaining')) {
                $currentRemaining = (int) ($purchase->limit_remaining ?? 0);
                $maxLimit = (int) ($purchase->quantity ?? 0);
                $nextRemaining = $maxLimit > 0
                    ? min($maxLimit, $currentRemaining + 1)
                    : ($currentRemaining + 1);
                $update = ['limit_remaining' => $nextRemaining];
                if (Schema::hasColumn('purchases', 'limit_status')) {
                    $update['limit_status'] = $nextRemaining > 0 ? 'pending' : 'complete';
                }
                $purchase->update($update);
            }
        });

        return redirect()
            ->route('admin.stock.purchase.show', $purchase->id)
            ->with('success', 'IMEI deleted successfully.');
    }

    /**
     * Show devices (product list items) for a stock: model and IMEI.
     */
    public function showStock(Stock $stock)
    {
        $stock->load(['productListItems' => function ($q) {
            $q->with([
                'category',
                'product',
                'purchase',
                'stock:id,name',
                'agentProductListAssignment.agent:id,name,email',
                'agentCredit.agent:id,name,email',
                'agentCredit.paymentOption:id,name',
                'pendingSale',
                'agentSale.agent:id,name,email',
            ])->orderBy('model')->orderBy('imei_number');
        }]);

        $available = $stock->productListItems->whereNull('sold_at')->count();
        $atLimit = $available >= $stock->stock_limit;

        return view('admin.stock.stock-show', compact('stock', 'atLimit'));
    }

    public function purchases(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $preset = $request->input('preset');

        if ($request->filled('preset')) {
            $now = Carbon::now();
            switch ($preset) {
                case 'this_week':
                    $dateFrom = $now->copy()->startOfWeek()->toDateString();
                    $dateTo = $now->copy()->endOfWeek()->toDateString();
                    break;
                case 'last_week':
                    $dateFrom = $now->copy()->subWeek()->startOfWeek()->toDateString();
                    $dateTo = $now->copy()->subWeek()->endOfWeek()->toDateString();
                    break;
                case 'last_30_days':
                    $dateFrom = $now->copy()->subDays(30)->toDateString();
                    $dateTo = $now->toDateString();
                    break;
            }
        }

        $query = Purchase::with(['product', 'stock', 'branch']);

        if ($dateFrom) {
            $query->where('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('date', '<=', $dateTo);
        }

        $purchases = $query->latest('date')->get();

        $purchaseDashboard = [
            'count' => $purchases->count(),
            'total_value' => (float) $purchases->sum(function ($p) {
                return (float) ($p->total_amount ?? ($p->quantity * $p->unit_price));
            }),
            'pending_amount' => (float) $purchases->sum(function ($p) {
                $total = (float) ($p->total_amount ?? ($p->quantity * $p->unit_price));

                return max(0, $total - (float) ($p->paid_amount ?? 0));
            }),
        ];

        return view('admin.stock.purchases', compact('purchases', 'dateFrom', 'dateTo', 'preset', 'purchaseDashboard'));
    }

    public function exportPurchasesCsv(Request $request)
    {
        $query = Purchase::with(['product.category', 'branch']);

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->input('date_to'));
        }

        $purchases = $query->latest('date')->get();
        $filename = 'purchases-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($purchases) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Invoice',
                'Date',
                'Branch',
                'Distributor',
                'Product',
                'Quantity',
                'Unit Price',
                'Total Amount',
                'Paid Date',
                'Paid Amount',
                'Pending Amount',
                'Sell Price',
                'Status',
            ]);

            foreach ($purchases as $purchase) {
                $total = (float) ($purchase->total_amount ?? ($purchase->quantity * $purchase->unit_price));
                $paid = (float) ($purchase->paid_amount ?? 0);
                $pending = max(0, $total - $paid);

                fputcsv($handle, [
                    $purchase->name ?? '',
                    $purchase->date ?? '',
                    $purchase->branch?->name ?? '',
                    $purchase->distributor_name ?? '',
                    trim(($purchase->product?->category?->name ? $purchase->product->category->name . ' - ' : '') . ($purchase->product?->name ?? '')),
                    (int) ($purchase->quantity ?? 0),
                    number_format((float) ($purchase->unit_price ?? 0), 2, '.', ''),
                    number_format($total, 2, '.', ''),
                    $purchase->paid_date ?? '',
                    number_format($paid, 2, '.', ''),
                    number_format($pending, 2, '.', ''),
                    $purchase->sell_price !== null ? number_format((float) $purchase->sell_price, 2, '.', '') : '',
                    $purchase->payment_status ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * View all payment receipts for all purchases.
     */
    public function viewAllReceipts()
    {
        $purchases = Purchase::with(['product', 'stock'])
            ->whereNotNull('payment_receipt_image')
            ->latest('date')
            ->get();
        
        return view('admin.stock.all-receipts', compact('purchases'));
    }

    /**
     * View payment receipts for a specific stock.
     */
    public function viewStockReceipts(Stock $stock)
    {
        $purchases = Purchase::with(['product'])
            ->where('stock_id', $stock->id)
            ->whereNotNull('payment_receipt_image')
            ->latest('date')
            ->get();
        
        return view('admin.stock.stock-receipts', compact('stock', 'purchases'));
    }

    public function distribution(Request $request)
    {
        $query = DistributionSale::with(['product.category', 'dealer']);
        
        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }
        
        $distributionSales = $query->latest('date')->get();

        $distributionDashboard = [
            'count' => $distributionSales->count(),
            'total_sell' => (float) $distributionSales->sum('total_selling_value'),
            'total_profit' => (float) $distributionSales->sum('profit'),
            'pending' => $distributionSales->filter(function ($s) {
                $total = (float) ($s->total_selling_value ?? 0);
                $paid = (float) ($s->paid_amount ?? 0);

                return $paid < $total - 0.0001;
            })->count(),
        ];

        return view('admin.stock.distribution', compact('distributionSales', 'distributionDashboard'));
    }

    public function exportDistributionCsv(Request $request)
    {
        $query = DistributionSale::with(['product.category', 'dealer']);

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->input('date_to'));
        }

        $sales = $query->latest('date')->get();
        $filename = 'distribution-sales-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($sales) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Date',
                'Dealer',
                'Seller',
                'Product',
                'Quantity',
                'Buy Price',
                'Sell Price',
                'Total Buy',
                'Total Sell',
                'Paid Amount',
                'Pending Amount',
                'Commission',
                'Profit',
                'Status',
            ]);

            foreach ($sales as $sale) {
                $totalSell = (float) ($sale->total_selling_value ?? 0);
                $paid = (float) ($sale->paid_amount ?? 0);

                fputcsv($handle, [
                    $sale->date ?? '',
                    $sale->dealer_name ?? $sale->dealer?->name ?? '',
                    $sale->seller_name ?? '',
                    trim(($sale->product?->category?->name ? $sale->product->category->name . ' - ' : '') . ($sale->product?->name ?? '')),
                    (int) ($sale->quantity_sold ?? 0),
                    number_format((float) ($sale->purchase_price ?? 0), 2, '.', ''),
                    number_format((float) ($sale->selling_price ?? 0), 2, '.', ''),
                    number_format((float) ($sale->total_purchase_value ?? 0), 2, '.', ''),
                    number_format($totalSell, 2, '.', ''),
                    number_format($paid, 2, '.', ''),
                    number_format(max(0, $totalSell - $paid), 2, '.', ''),
                    number_format((float) ($sale->commission ?? 0), 2, '.', ''),
                    number_format((float) ($sale->profit ?? 0), 2, '.', ''),
                    $sale->status ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Legacy route: channel and installments are handled on the edit distribution page (like purchases).
     */
    public function saveDistributionChannel(Request $request, $id)
    {
        return redirect()->route('admin.stock.distribution')
            ->with('info', 'Use Edit on the sale to record payments, payment channel, and remaining balance.');
    }

    public function updateDistributionStatus($id)
    {
        $sale = DistributionSale::findOrFail($id);
        $sale->update(['status' => 'complete']);
        return redirect()->route('admin.stock.distribution')->with('success', 'Distribution sale marked as complete.');
    }

    public function agentSales(Request $request)
    {
        $query = AgentSale::with(['product.category', 'agent', 'paymentOption']);
        
        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }
        
        $agentSales = $query->latest('date')->get();
        $paymentOptions = PaymentOption::visible()->orderBy('name')->get();

        $agentSalesDashboard = [
            'count' => $agentSales->count(),
            'total_sell' => (float) $agentSales->sum('total_selling_value'),
            'total_profit' => (float) $agentSales->sum('profit'),
        ];

        return view('admin.stock.agent-sales', compact('agentSales', 'paymentOptions', 'agentSalesDashboard'));
    }

    public function exportAgentSalesCsv(Request $request)
    {
        $query = AgentSale::with(['product.category', 'agent', 'paymentOption']);

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->input('date_to'));
        }

        $sales = $query->latest('date')->get();
        $filename = 'agent-sales-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($sales) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Date',
                'Customer',
                'Seller',
                'Product',
                'Quantity',
                'Buy Price',
                'Sell Price',
                'Total Buy',
                'Total Sell',
                'Profit',
                'Commission Paid',
                'Payment Channel',
            ]);

            foreach ($sales as $sale) {
                fputcsv($handle, [
                    $sale->date ?? '',
                    $sale->customer_name ?? '',
                    $sale->seller_name ?? $sale->agent?->name ?? '',
                    trim(($sale->product?->category?->name ? $sale->product->category->name . ' - ' : '') . ($sale->product?->name ?? '')),
                    (int) ($sale->quantity_sold ?? 0),
                    number_format((float) ($sale->purchase_price ?? 0), 2, '.', ''),
                    number_format((float) ($sale->selling_price ?? 0), 2, '.', ''),
                    number_format((float) ($sale->total_purchase_value ?? 0), 2, '.', ''),
                    number_format((float) ($sale->total_selling_value ?? 0), 2, '.', ''),
                    number_format((float) ($sale->profit ?? 0), 2, '.', ''),
                    number_format((float) ($sale->commission_paid ?? 0), 2, '.', ''),
                    $sale->paymentOption?->name ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function saveAgentSaleChannel(Request $request, $id)
    {
        $sale = AgentSale::findOrFail($id);

        if ($sale->payment_option_id) {
            return redirect()->route('admin.stock.agent-sales')->with('info', 'Payment channel is already set for this sale.');
        }

        $validated = $request->validate([
            'payment_option_id' => 'required|exists:payment_options,id',
        ]);

        $option = PaymentOption::findOrFail($validated['payment_option_id']);
        $sale->update(['payment_option_id' => $option->id]);
        
        $amount = (float) ($sale->total_selling_value ?? 0);
        if ($amount > 0) {
            $option->increment('balance', $amount);
        }

        return redirect()->route('admin.stock.agent-sales')->with('success', 'Channel saved. Amount added to ' . $option->name . '.');
    }

    public function updateAgentSaleCommission(Request $request, $id)
    {
        $sale = AgentSale::findOrFail($id);
        $validated = $request->validate(['commission_paid' => 'required|numeric|min:0']);
        $newCommission = (float) $validated['commission_paid'];
        $eps = 0.0001;
        $canStoreExpenseId = Schema::hasColumn('agent_sales', 'commission_expense_id');

        $defaultChannelRaw = Setting::query()->where('key', 'default_agent_commission_channel_id')->value('value');
        $defaultChannelId = $defaultChannelRaw !== null && $defaultChannelRaw !== '' ? (int) $defaultChannelRaw : null;
        if ($newCommission > $eps && ! $defaultChannelId) {
            return redirect()->route('admin.stock.agent-sales', $request->query())
                ->withErrors(['error' => 'Choose a default commission channel in Store settings before saving commission.']);
        }

        try {
            DB::transaction(function () use ($sale, $newCommission, $defaultChannelId, $eps, $canStoreExpenseId) {
                $sale->refresh();

                $linkedExpense = null;
                if ($canStoreExpenseId && $sale->commission_expense_id) {
                    $linkedExpense = Expense::query()->lockForUpdate()->find($sale->commission_expense_id);
                }
                if (! $linkedExpense) {
                    $linkedExpense = Expense::query()
                        ->lockForUpdate()
                        ->where('activity', 'Agent sale commission (sale #' . $sale->id . ')')
                        ->latest('id')
                        ->first();
                }

                if ($linkedExpense) {
                    $opt = $linkedExpense->paymentOption;
                    if ($opt) {
                        $opt->increment('balance', (float) $linkedExpense->amount);
                    }
                    $sale->commission_expense_id = null;
                    $sale->saveQuietly();
                    $linkedExpense->delete();
                }

                $commissionExpenseId = null;
                if ($newCommission > $eps) {
                    $option = PaymentOption::query()
                        ->visible()
                        ->whereKey($defaultChannelId)
                        ->lockForUpdate()
                        ->first();

                    if (! $option) {
                        throw new \InvalidArgumentException('The default commission channel is invalid or hidden. Update Store settings.');
                    }

                    if ((float) $option->balance + $eps < $newCommission) {
                        throw new \InvalidArgumentException('Insufficient balance in the default commission channel for this amount.');
                    }

                    $option->decrement('balance', $newCommission);

                    $expense = Expense::create([
                        'activity' => 'Agent sale commission (sale #' . $sale->id . ')',
                        'amount' => $newCommission,
                        'cash_used' => null,
                        'payment_option_id' => $option->id,
                        'date' => now()->toDateString(),
                    ]);
                    $commissionExpenseId = $expense->id;
                }

                $payload = ['commission_paid' => $newCommission];
                if ($canStoreExpenseId) {
                    $payload['commission_expense_id'] = $commissionExpenseId;
                }
                $sale->update($payload);
            });
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('admin.stock.agent-sales', $request->query())
                ->withErrors(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            Log::error('Agent sale commission save failed: ' . $e->getMessage(), ['exception' => $e]);

            return redirect()->route('admin.stock.agent-sales', $request->query())
                ->withErrors(['error' => 'Could not save commission. Try again or check logs.']);
        }

        return redirect()->route('admin.stock.agent-sales', $request->query())->with('success', 'Commission updated and expense synced.');
    }

    public function destroyAgentSale($id)
    {
        $sale = AgentSale::findOrFail($id);
        $product = $sale->product;
        $qty = (int) ($sale->quantity_sold ?? 0);

        try {
            DB::transaction(function () use ($sale) {
                if ($sale->payment_option_id) {
                    $option = PaymentOption::find($sale->payment_option_id);
                    $amount = (float) ($sale->total_selling_value ?? 0);
                    if ($option && $amount > 0) {
                        if ((float) $option->balance >= $amount) {
                            $option->decrement('balance', $amount);
                        } else {
                            throw new \RuntimeException('Cannot delete sale because the linked channel balance is already lower than this sale amount.');
                        }
                    }
                }

                if (Schema::hasColumn('agent_sales', 'commission_expense_id') && ! empty($sale->commission_expense_id)) {
                    $expense = Expense::find($sale->commission_expense_id);
                    if ($expense) {
                        if ($expense->payment_option_id) {
                            $expOpt = PaymentOption::find($expense->payment_option_id);
                            if ($expOpt) {
                                $expOpt->increment('balance', (float) $expense->amount);
                            }
                        }
                        $expense->delete();
                    }
                }

                \App\Models\ProductListItem::where('agent_sale_id', $sale->id)->update([
                    'agent_sale_id' => null,
                    'sold_at' => null,
                ]);

                $sale->delete();
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('admin.stock.agent-sales')
                ->withErrors(['error' => $e->getMessage()]);
        }

        if ($product && $qty > 0) {
            $product->increment('stock_quantity', $qty);
        }

        return redirect()->route('admin.stock.agent-sales')->with('success', 'Agent sale deleted successfully.');
    }

    public function downloadAgentSaleInvoice($id)
    {
        $sale = AgentSale::with(['product.category', 'agent', 'productListItem'])->findOrFail($id);

        $invoiceNo = 'AS-' . str_pad((string) $sale->id, 6, '0', STR_PAD_LEFT);
        $invoiceDate = $sale->date ? Carbon::parse($sale->date) : now();
        $filename = 'agent-sale-invoice-' . strtolower($invoiceNo) . '-' . $invoiceDate->format('Ymd') . '.pdf';
        $title = 'RECEIPT';

        return PdfDownload::fromView('admin.stock.receipt-invoice', compact('sale', 'invoiceNo', 'invoiceDate', 'title'), $filename);
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

    /**
     * Form: scan IMEI, select stock (from pending purchases), select model from selected stock.
     */
    public function addProductForm()
    {
        $stocks = Stock::query()
            ->orderBy('name')
            ->get(['id', 'name']);
        return view('admin.stock.add-product', compact('stocks'));
    }

    /**
     * JSON: distinct models (and category_id) for a stock (from product_list + purchases).
     */
    public function modelsForStock(Stock $stock)
    {
        $fromList = \App\Models\ProductListItem::query()
            ->where('stock_id', $stock->id)
            ->with('product:id,name,category_id')
            ->get()
            ->map(function ($r) {
                $model = trim((string) ($r->model ?: ($r->product->name ?? '')));
                $categoryId = $r->category_id ?: ($r->product->category_id ?? null);
                if ($model === '' || empty($categoryId)) {
                    return null;
                }

                return ['model' => $model, 'category_id' => (int) $categoryId];
            })
            ->filter()
            ->unique('model')
            ->values();

        $fromPurchases = Purchase::where('stock_id', $stock->id)
            ->with('product:id,category_id,name')
            ->get()
            ->map(function ($p) {
                $model = trim((string) ($p->product->name ?? ''));
                $categoryId = $p->product->category_id ?? null;
                if ($model === '' || empty($categoryId)) {
                    return null;
                }

                return ['model' => $model, 'category_id' => (int) $categoryId];
            })
            ->filter()
            ->unique('model')
            ->values();
        $combined = $fromList
            ->concat($fromPurchases)
            ->unique('model')
            ->sortBy('model', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
        if ($combined->isEmpty() && ! empty($stock->default_model) && ! empty($stock->default_category_id)) {
            $combined = collect([[
                'model' => $stock->default_model,
                'category_id' => (int) $stock->default_category_id,
            ]]);
        }

        return response()->json(['data' => $combined->all()]);
    }

    /**
     * Decode QR codes from uploaded photos (server uses GD + ZXing; 1D barcodes work best from the mobile app).
     */
    public function decodeBarcodeImages(Request $request)
    {
        $request->validate([
            'images' => 'required|array|min:1|max:30',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        if (! BarcodeImageDecoder::decodingAvailable()) {
            return response()->json([
                'message' => 'QR decode needs the PHP GD extension.',
                'codes' => [],
            ], 503);
        }

        $decoder = new BarcodeImageDecoder;
        $seen = [];
        $codes = [];

        foreach ($request->file('images', []) as $file) {
            $path = $file->getRealPath();
            if (! $path || ! is_readable($path)) {
                continue;
            }
            foreach ($decoder->decodeFile($path) as $row) {
                $c = trim((string) ($row['code'] ?? ''));
                if ($c !== '' && ! isset($seen[$c])) {
                    $seen[$c] = true;
                    $codes[] = $c;
                }
            }
        }

        return response()->json([
            'codes' => $codes,
            'message' => count($codes) ? null : 'No QR code found. Try clearer photos or type IMEIs manually. For linear barcodes, use the OpticApp admin Add Product (photo) flow.',
        ]);
    }

    /**
     * Save one or more IMEIs: stock_id, model, category_id, imei_numbers (newline / comma separated).
     */
    public function storeProductFromForm(Request $request)
    {
        $validated = $request->validate([
            'stock_id' => 'required|exists:stocks,id',
            'model' => 'required|string|max:255',
            'category_id' => 'required|exists:brands,id',
            'imei_numbers' => 'required|string|max:65535',
        ]);

        $imeis = ImeiListParser::parse($validated['imei_numbers']);

        if ($imeis === []) {
            return redirect()->route('admin.stock.add-product')
                ->withInput()
                ->withErrors(['imei_numbers' => 'Enter at least one IMEI. Use one per line, or separate with spaces, commas, or semicolons.']);
        }

        $lenErrors = ImeiListParser::lengthErrors($imeis);
        if ($lenErrors !== []) {
            return redirect()->route('admin.stock.add-product')
                ->withInput()
                ->withErrors(['imei_numbers' => implode(' ', $lenErrors)]);
        }

        $stock = Stock::findOrFail($validated['stock_id']);
        $purchase = Purchase::where('stock_id', $stock->id)
            ->where('limit_status', 'pending')
            ->where('limit_remaining', '>', 0)
            ->latest('date')->latest('id')->first();

        if (! $purchase) {
            return redirect()->route('admin.stock.add-product')
                ->withInput()
                ->withErrors(['stock_id' => 'No pending purchase limit for this stock.']);
        }

        if (count($imeis) > $purchase->limit_remaining) {
            return redirect()->route('admin.stock.add-product')
                ->withInput()
                ->withErrors([
                    'imei_numbers' => 'Not enough purchase limit for this many IMEIs. Remaining: '.$purchase->limit_remaining.'.',
                ]);
        }

        $failed = [];
        $failureReasons = [
            'duplicates' => [],
            'limit_exhausted' => [],
        ];
        $created = 0;

        DB::transaction(function () use ($purchase, $stock, $validated, $imeis, &$failed, &$failureReasons, &$created) {
            $productPrice = $purchase->sell_price ?? $purchase->unit_price ?? 0;
            $product = Product::firstOrCreate(
                [
                    'category_id' => $validated['category_id'],
                    'name' => $validated['model'],
                ],
                [
                    'price' => (float) $productPrice,
                    'stock_quantity' => 0,
                    'rating' => 5.0,
                    'description' => 'From product list',
                    'images' => $purchase->product?->images ?? [],
                ]
            );

            if ($purchase->sell_price && (float) $product->price != (float) $purchase->sell_price) {
                $product->update(['price' => (float) $purchase->sell_price]);
            }

            foreach ($imeis as $imei) {
                if (ProductListItem::where('imei_number', $imei)->exists()) {
                    $failed[] = $imei.' (already in list)';
                    $failureReasons['duplicates'][] = $imei;
                    continue;
                }

                $purchase->refresh();
                if ($purchase->limit_remaining <= 0) {
                    $failed[] = $imei.' (purchase limit exhausted)';
                    $failureReasons['limit_exhausted'][] = $imei;
                    break;
                }

                ProductListItem::create([
                    'stock_id' => $stock->id,
                    'purchase_id' => $purchase->id,
                    'category_id' => $validated['category_id'],
                    'model' => $validated['model'],
                    'imei_number' => $imei,
                    'product_id' => $product->id,
                ]);

                $purchase->decrement('limit_remaining');
                if ($purchase->fresh()->limit_remaining <= 0) {
                    $purchase->update(['limit_status' => 'complete']);
                }
                $created++;
            }
        });

        if ($created > 0) {
            $msg = 'Added '.$created.' device(s) ('.count($imeis).' IMEI(s) parsed).';
            if ($failed !== []) {
                $msg .= ' Skipped: '.implode('; ', array_slice($failed, 0, 10)).(count($failed) > 10 ? '…' : '');
            }

            return redirect()->route('admin.stock.add-product')->with('success', $msg);
        }

        return redirect()->route('admin.stock.add-product')
            ->withInput()
            ->withErrors(['imei_numbers' => $this->buildDetailedErrorMessage($imeis, $failureReasons)]);
    }

    public function createPurchase(Request $request)
    {
        $vendors = Vendor::orderBy('name')->get();

        $fromStock = null;
        if ($request->has('from_stock')) {
            $fromStock = Stock::with(['defaultCategory', 'productListItems' => fn ($q) => $q->with(['category', 'product'])->latest('id')->limit(1)])
                ->find($request->from_stock);

            if ($fromStock) {
                // Quantity = stock limit (total quantity for this purchase from stock)
                $fromStock->purchase_quantity = $fromStock->stock_limit;

                // Category and model: from product list items in this stock (as added in app), or fallback to stock defaults
                $firstItem = $fromStock->productListItems->first();
                if ($firstItem) {
                    $fromStock->purchase_category_id = $firstItem->category_id ?? $firstItem->product?->category_id;
                    $fromStock->purchase_category_name = $firstItem->category?->name ?? $firstItem->product?->category?->name ?? '–';
                    $fromStock->purchase_model = $firstItem->model ?? $firstItem->product?->name ?? '–';
                } else {
                    $fromStock->purchase_category_id = $fromStock->default_category_id;
                    $fromStock->purchase_category_name = $fromStock->defaultCategory?->name ?? '–';
                    $fromStock->purchase_model = $fromStock->default_model ?? '–';
                    if (!$fromStock->purchase_category_id || !$fromStock->purchase_model) {
                        return redirect()->route('admin.stock.stocks')
                            ->with('info', 'Add products to this stock in the app first. Then "Add via Purchases" will use that category and model.');
                    }
                }
            }
        }

        $branches = Branch::orderBy('name')->get();

        $productsForSelect = Product::with('category')
            ->get()
            ->sortBy(fn (Product $p) => ($p->category?->name ?? '') . $p->name)
            ->values();

        return view('admin.stock.create-purchase', compact('vendors', 'fromStock', 'branches', 'productsForSelect'));
    }

    public function storePurchase(Request $request)
    {
        if (! $request->filled('stock_id')) {
            $request->validate([
                'product_id' => 'required|exists:models,id',
            ]);
            $selectedProduct = Product::findOrFail($request->product_id);
            $request->merge([
                'category_id' => $selectedProduct->category_id,
                'model' => $selectedProduct->name,
            ]);
        }

        $validated = $request->validate([
            'stock_id' => 'nullable|exists:stocks,id',
            'branch_id' => 'required|exists:branches,id',
            'name' => 'nullable|string|max:255',
            'date' => 'required|date',
            'distributor_name' => 'nullable|string|max:255',
            'category_id' => 'required|exists:brands,id',
            'model' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'sell_price' => 'nullable|numeric|min:0',
            'paid_date' => 'nullable|date',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_option_id' => 'nullable|exists:payment_options,id',
            'payment_receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $nameInput = trim((string) ($validated['name'] ?? ''));
        if ($nameInput === '') {
            $dateStr = PurchaseInvoiceNumber::dateString($validated['date']);
            $validated['name'] = PurchaseInvoiceNumber::unique($validated['distributor_name'] ?? null, $dateStr);
        } else {
            $validated['name'] = $nameInput;
        }

        // Product: from explicit selection, or find/create when adding from stock
        $productPrice = $validated['sell_price'] ?? $validated['unit_price'];
        if ($request->filled('stock_id')) {
            $product = Product::firstOrCreate(
                [
                    'category_id' => $validated['category_id'],
                    'name' => $validated['model'],
                ],
                [
                    'price' => $productPrice,
                    'stock_quantity' => 0,
                    'rating' => 5.0,
                    'description' => 'Auto-created from purchase',
                    'images' => [],
                ]
            );
        } else {
            $product = Product::findOrFail($request->product_id);
        }
        
        // Note: Product price will be updated after purchase creation to use latest sell_price

        $stockId = !empty($validated['stock_id']) ? (int) $validated['stock_id'] : null;
        $quantity = $validated['quantity'] ?? 0;

        // Remove non-purchase fields from validated data
        unset($validated['category_id']);
        unset($validated['model']);
        unset($validated['stock_id']);

        // Add product_id and optional stock_id
        $validated['product_id'] = $product->id;
        if ($stockId) {
            $validated['stock_id'] = $stockId;
        }

        // Calculate total amount (backend validation/calculation)
        $validated['total_amount'] = $quantity * $validated['unit_price'];
        $paidAmount = (float) ($validated['paid_amount'] ?? 0);
        
        // Auto status from paid amount: pending / partial / paid (like in edit)
        $totalAmount = $validated['total_amount'];
        $paymentStatus = $paidAmount >= $totalAmount ? 'paid' : ($paidAmount > 0 ? 'partial' : 'pending');
        $validated['payment_status'] = $paymentStatus;
        $validated['paid_amount'] = $paidAmount;
        
        // Quantity = limit: track remaining; when app adds IMEIs, decrement until 0 then set complete
        $validated['limit_status'] = 'pending';
        $validated['limit_remaining'] = $quantity;
        $validated['sell_price'] = $request->filled('sell_price') ? $request->input('sell_price') : null;
        
        // Only add payment_option_id if the column exists (migration has been run)
        $paymentOptionId = $request->filled('payment_option_id') ? $request->input('payment_option_id') : null;
        try {
            // Check if column exists by trying to get schema
            $columns = Schema::getColumnListing('purchases');
            if (in_array('payment_option_id', $columns)) {
                $validated['payment_option_id'] = $paymentOptionId;
            }
        } catch (\Exception $e) {
            // Column doesn't exist, skip it
            Log::warning('payment_option_id column not found in purchases table. Migration may need to be run.');
        }

        // Handle payment option balance deduction if payment is made
        if ($paidAmount > 0 && $paymentOptionId) {
            $paymentOption = PaymentOption::find($paymentOptionId);
            if ($paymentOption) {
                if ($paymentOption->balance >= $paidAmount) {
                    $paymentOption->decrement('balance', $paidAmount);
                } else {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['payment_option_id' => 'Insufficient balance in selected payment channel.']);
                }
            }
        }

        // Create purchase first to get the ID
        $purchase = Purchase::create($validated);

        // Upload payment receipt image if provided (store in purchase-specific directory)
        if ($request->hasFile('payment_receipt_image')) {
            $receiptImage = $request->file('payment_receipt_image');
            if ($receiptImage->isValid()) {
                $receiptDir = 'receipts/purchase-' . $purchase->id;
                $paymentReceiptPath = $receiptImage->store($receiptDir, 'public');
                $purchase->update(['payment_receipt_image' => $paymentReceiptPath]);
            }
        }

        // Keep product.stock_quantity in sync so Category Management and dashboards show correct counts
        $product->increment('stock_quantity', $validated['quantity']);

        // Record initial payment in history if payment was made
        if ($paidAmount > 0 && $request->filled('payment_option_id')) {
            try {
                PurchasePayment::create([
                    'purchase_id' => $purchase->id,
                    'payment_option_id' => $request->input('payment_option_id'),
                    'amount' => $paidAmount,
                    'paid_date' => $validated['paid_date'] ?? now()->toDateString(),
                ]);
            } catch (\Exception $e) {
                // Table might not exist yet - migration needs to be run
                // Log error but don't fail the purchase creation
                Log::warning('Failed to create purchase payment record: ' . $e->getMessage());
            }
        }

        // Update product price to use the latest purchase's sell_price (if available)
        // This ensures front page products show the correct sell_price instead of unit_price
        $latestPurchase = Purchase::where('product_id', $product->id)
            ->whereNotNull('sell_price')
            ->latest('date')
            ->latest('id')
            ->first();
        
        if ($latestPurchase && $latestPurchase->sell_price) {
            $product->update(['price' => $latestPurchase->sell_price]);
        }

        return redirect()->route('admin.stock.purchases')->with('success', 'Purchase recorded successfully.');
    }

    public function editPurchase($id)
    {
        $purchase = Purchase::with(['product.category', 'payments.paymentOption'])->findOrFail($id);
        
        // Get all categories for the select dropdown
        $categories = \App\Models\Category::orderBy('name')->get();
            
        // Get unique distributors for the datalist
        $distributors = Purchase::select('distributor_name')
            ->whereNotNull('distributor_name')
            ->distinct()
            ->pluck('distributor_name');
        
        // Get payment options with balance for selection
        $paymentOptions = PaymentOption::visible()->orderBy('name')->get();
            
        return view('admin.stock.edit-purchase', compact('purchase', 'categories', 'distributors', 'paymentOptions'));
    }

    public function updatePurchase(Request $request, $id)
    {
        $purchase = Purchase::with('product')->findOrFail($id);

        $rules = [
            'name' => 'nullable|string|max:255',
            'paid_date' => 'nullable|date',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_option_id' => 'nullable|exists:payment_options,id',
            'payment_receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ];
        $validated = $request->validate($rules);

        // Upload payment receipt image if provided (store in purchase-specific directory)
        $paymentReceiptPath = $purchase->payment_receipt_image;
        if ($request->hasFile('payment_receipt_image')) {
            $receiptImage = $request->file('payment_receipt_image');
            if ($receiptImage->isValid()) {
                // Delete old receipt image if exists
                if ($purchase->payment_receipt_image && Storage::disk('public')->exists($purchase->payment_receipt_image)) {
                    Storage::disk('public')->delete($purchase->payment_receipt_image);
                }
                // Store in purchase-specific directory: receipts/purchase-{id}/
                $receiptDir = 'receipts/purchase-' . $purchase->id;
                $paymentReceiptPath = $receiptImage->store($receiptDir, 'public');
            }
        }

        // Form field "paid_amount" is incremental ("Pay this time"); persist cumulative total.
        $totalAmount = $purchase->total_amount ?? ($purchase->quantity * $purchase->unit_price);
        $oldPaidAmount = (float) ($purchase->paid_amount ?? 0);
        $increment = max(0, (float) ($validated['paid_amount'] ?? 0));
        $remaining = max(0, $totalAmount - $oldPaidAmount);
        $eps = 0.0001;

        if ($increment > $remaining + $eps) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['paid_amount' => 'Pay amount cannot exceed the remaining balance for this purchase.']);
        }

        $newPaidAmount = min($totalAmount, $oldPaidAmount + $increment);
        $paymentDifference = $newPaidAmount - $oldPaidAmount;

        $paymentStatus = $newPaidAmount >= $totalAmount - $eps ? 'paid' : ($newPaidAmount > $eps ? 'partial' : 'pending');

        $oldPaymentOption = $purchase->payment_option_id;
        $hasPaymentOptionInput = $request->has('payment_option_id');
        if ($hasPaymentOptionInput) {
            $newPaymentOptionId = $validated['payment_option_id'] ?? null;
            if ($newPaymentOptionId === '' || $newPaymentOptionId === false) {
                $newPaymentOptionId = null;
            } else {
                $newPaymentOptionId = (int) $newPaymentOptionId;
            }
        } else {
            $newPaymentOptionId = $oldPaymentOption !== null ? (int) $oldPaymentOption : null;
        }

        if ($increment > $eps && $newPaymentOptionId === null) {
            $defaultWatuChannelRaw = Setting::query()->where('key', 'default_watu_channel_id')->value('value');
            $defaultWatuChannelId = $defaultWatuChannelRaw !== null && $defaultWatuChannelRaw !== ''
                ? (int) $defaultWatuChannelRaw
                : null;
            if (! $defaultWatuChannelId) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['error' => 'Choose a default Watu channel in Store settings before recording payment.']);
            }
            $defaultWatuChannel = PaymentOption::visible()->whereKey($defaultWatuChannelId)->first();
            if (! $defaultWatuChannel) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['error' => 'Default Watu channel is invalid or hidden. Update Store settings.']);
            }
            $newPaymentOptionId = $defaultWatuChannel->id;
        }
        $newPaidDate = $validated['paid_date'] ?? null;

        // Payment channel balances: delta on same channel; refund old + charge full cumulative on switch; refund when channel removed
        $oldOptId = $oldPaymentOption !== null ? (int) $oldPaymentOption : null;
        $newOptId = $newPaymentOptionId;

        if ($newOptId === null && $oldOptId !== null && $oldPaidAmount > $eps) {
            $oldOption = PaymentOption::find($oldOptId);
            if ($oldOption) {
                $oldOption->increment('balance', $oldPaidAmount);
            }
        } elseif ($oldOptId !== null && $newOptId !== null && $oldOptId !== $newOptId) {
            if ($oldPaidAmount > $eps) {
                $oldOption = PaymentOption::find($oldOptId);
                if ($oldOption) {
                    $oldOption->increment('balance', $oldPaidAmount);
                }
            }
            if ($newPaidAmount > $eps) {
                $paymentOption = PaymentOption::find($newOptId);
                if ($paymentOption) {
                    if ($paymentOption->balance + $eps >= $newPaidAmount) {
                        $paymentOption->decrement('balance', $newPaidAmount);
                    } else {
                        return redirect()->back()
                            ->withInput()
                            ->withErrors(['payment_option_id' => 'Insufficient balance in selected payment channel.']);
                    }
                }
            }
        } elseif ($newOptId !== null) {
            $paymentOption = PaymentOption::find($newOptId);
            if ($paymentOption) {
                $deltaToApply = $paymentDifference;
                if ($oldOptId === null && $paymentDifference <= $eps && $oldPaidAmount > $eps) {
                    $deltaToApply = $oldPaidAmount;
                }
                if ($deltaToApply > $eps) {
                    if ($paymentOption->balance + $eps >= $deltaToApply) {
                        $paymentOption->decrement('balance', $deltaToApply);
                    } else {
                        return redirect()->back()
                            ->withInput()
                            ->withErrors(['paid_amount' => 'Insufficient balance in selected payment channel for this payment.']);
                    }
                } elseif ($deltaToApply < -$eps) {
                    $paymentOption->increment('balance', abs($deltaToApply));
                }
            }
        }

        // Prepare update data
        $updateData = [
            'name' => $validated['name'] ?? $purchase->name,
            'paid_date' => $newPaidDate,
            'paid_amount' => $newPaidAmount,
            'payment_status' => $paymentStatus,
            'payment_receipt_image' => $paymentReceiptPath,
        ];
        
        // Only add payment_option_id if the column exists (migration has been run)
        try {
            $columns = Schema::getColumnListing('purchases');
            if (in_array('payment_option_id', $columns)) {
                $updateData['payment_option_id'] = $newPaymentOptionId;
            }
        } catch (\Exception $e) {
            Log::warning('payment_option_id column not found in purchases table. Migration may need to be run.');
        }
        
        $purchase->update($updateData);

        // Record one history row per incremental payment (amount = delta for this save)
        if ($paymentDifference > $eps) {
            try {
                PurchasePayment::create([
                    'purchase_id' => $purchase->id,
                    'payment_option_id' => $newOptId,
                    'amount' => $paymentDifference,
                    'paid_date' => $newPaidDate ?? now()->toDateString(),
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to create purchase payment record: ' . $e->getMessage());
            }
        }

        // Update product price to use the latest purchase's sell_price (if available)
        // This ensures front page products show the correct sell_price instead of unit_price
        if ($purchase->product) {
            $latestPurchase = Purchase::where('product_id', $purchase->product_id)
                ->whereNotNull('sell_price')
                ->latest('date')
                ->latest('id')
                ->first();
            
            if ($latestPurchase && $latestPurchase->sell_price) {
                $purchase->product->update(['price' => $latestPurchase->sell_price]);
            }
        }

        return redirect()
            ->route('admin.stock.edit-purchase', $purchase->id)
            ->with('success', 'Purchase updated successfully.');
    }

    public function destroyPurchase($id)
    {
        $purchase = Purchase::with('product')->findOrFail($id);
        $product = $purchase->product;

        // Detach product list items linked to this purchase so they are not orphaned
        // in reports. Sold items keep their sold_at history; unsold items are freed.
        ProductListItem::where('purchase_id', $id)->update(['purchase_id' => null]);

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
        if ($request->filled('paid_amount') === false || trim((string) $request->input('paid_amount', '')) === '') {
            $request->merge(['paid_amount' => null]);
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'dealer_id' => 'nullable|exists:users,id',
            'dealer_name' => 'nullable|string|max:255',
            'seller_name' => 'nullable|string|max:255',
            'product_id' => 'required|exists:models,id',
            'quantity_sold' => 'required|integer|min:1',
            'selling_price' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
        ]);

        $service = app(\App\Services\DistributionSaleService::class);
        $buyPrice = $service->getBuyPriceForProduct($validated['product_id']); // Uses latest purchase unit_price as buy cost
        $validated['purchase_price'] = $buyPrice;
        $validated['total_selling_value'] = $validated['quantity_sold'] * $validated['selling_price'];
        $validated['total_purchase_value'] = $validated['quantity_sold'] * $buyPrice;
        $validated['commission'] = 0; // Manual entry: no referrer commission
        $validated['profit'] = $validated['total_selling_value'] - $validated['total_purchase_value'] - 0;
        $validated['paid_amount'] = $validated['paid_amount'] ?? 0;
        $validated['balance'] = $validated['total_selling_value'] - $validated['paid_amount'];
        $eps = 0.0001;
        $validated['status'] = $validated['paid_amount'] >= $validated['total_selling_value'] - $eps ? 'complete' : 'pending';
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
        $sale = DistributionSale::with(['product.category', 'dealer', 'payments.paymentOption'])->findOrFail($id);
        $paymentOptions = PaymentOption::visible()->orderBy('name')->get();

        return view('admin.stock.edit-distribution', compact('sale', 'paymentOptions'));
    }

    public function downloadDistributionInvoice($id)
    {
        $sale = DistributionSale::with(['product.category', 'dealer', 'payments.paymentOption'])->findOrFail($id);

        $invoiceNo = str_pad((string) $sale->id, 5, '0', STR_PAD_LEFT);
        $safeDate = ($sale->date ? Carbon::parse($sale->date)->format('Ymd') : now()->format('Ymd'));
        $filename = "distribution-invoice-{$invoiceNo}-{$safeDate}.pdf";

        return PdfDownload::fromView('admin.stock.distribution-invoice', compact('sale', 'invoiceNo'), $filename);
    }

    public function updateDistribution(Request $request, $id)
    {
        $sale = DistributionSale::findOrFail($id);

        $incrementPreview = max(0, (float) ($request->input('paid_amount') ?? 0));
        $eps = 0.0001;
        $paymentOptionRules = $incrementPreview > $eps
            ? 'required|exists:payment_options,id'
            : 'nullable|exists:payment_options,id';

        $validated = $request->validate([
            'paid_amount' => 'nullable|numeric|min:0',
            'collection_date' => 'nullable|date',
            'payment_option_id' => $paymentOptionRules,
        ]);

        // Form "paid_amount" is incremental ("Pay this time"); persist cumulative total (same as purchases).
        $totalSelling = (float) ($sale->total_selling_value ?? 0);
        $oldPaidAmount = (float) ($sale->paid_amount ?? 0);
        $increment = max(0, (float) ($validated['paid_amount'] ?? 0));
        $remaining = max(0, $totalSelling - $oldPaidAmount);

        if ($increment > $remaining + $eps) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['paid_amount' => 'Pay amount cannot exceed the remaining balance for this sale.']);
        }

        $newPaidAmount = min($totalSelling, $oldPaidAmount + $increment);
        $paymentDifference = $newPaidAmount - $oldPaidAmount;

        $newPaymentOptionId = $validated['payment_option_id'] ?? null;
        if ($newPaymentOptionId === '' || $newPaymentOptionId === false) {
            $newPaymentOptionId = null;
        } else {
            $newPaymentOptionId = (int) $newPaymentOptionId;
        }

        // Dealer payment received: credit the selected channel balance (purchases debit when paying out).
        if ($paymentDifference > $eps && $newPaymentOptionId !== null) {
            $paymentOption = PaymentOption::find($newPaymentOptionId);
            if ($paymentOption) {
                $paymentOption->increment('balance', $paymentDifference);
            }
        }

        $newStatus = $newPaidAmount >= $totalSelling - $eps ? 'complete' : 'pending';

        $update = [
            'paid_amount' => $newPaidAmount,
            'balance' => max(0, $totalSelling - $newPaidAmount),
            'collection_date' => $validated['collection_date'] ?? $sale->collection_date,
            'status' => $newStatus,
        ];

        if (Schema::hasColumn('distribution_sales', 'payment_option_id')) {
            $update['payment_option_id'] = $newPaymentOptionId;
        }

        $sale->update($update);

        if ($paymentDifference > $eps) {
            try {
                $paidDate = !empty($validated['collection_date'])
                    ? \Carbon\Carbon::parse($validated['collection_date'])->toDateString()
                    : now()->toDateString();
                DistributionSalePayment::create([
                    'distribution_sale_id' => $sale->id,
                    'payment_option_id' => $newPaymentOptionId,
                    'amount' => $paymentDifference,
                    'paid_date' => $paidDate,
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to create distribution sale payment record: ' . $e->getMessage());
            }
        }

        return redirect()
            ->route('admin.stock.edit-distribution', $sale->id)
            ->with('success', 'Distribution sale updated successfully.');
    }

    public function destroyDistribution($id)
    {
        $sale = DistributionSale::findOrFail($id);
        $product = $sale->product;
        $quantitySold = $sale->quantity_sold;
        
        $sale->delete();
        
        // Keep product.stock_quantity in sync
        if ($product) {
            $product->increment('stock_quantity', $quantitySold);
        }

        return redirect()->route('admin.stock.distribution')->with('success', 'Distribution sale deleted successfully.');
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
            'product_id' => 'required|exists:models,id',
            'quantity_sold' => 'required|integer|min:1',
            'selling_price' => 'required|numeric|min:0',
        ]);

        $service = app(\App\Services\DistributionSaleService::class);
        $buyPrice = $service->getBuyPriceForProduct($validated['product_id']);
        
        $validated['purchase_price'] = $buyPrice;
        $validated['total_selling_value'] = $validated['quantity_sold'] * $validated['selling_price'];
        $validated['total_purchase_value'] = $validated['quantity_sold'] * $buyPrice;
        $validated['profit'] = $validated['total_selling_value'] - $validated['total_purchase_value'];

        // Save to pending sales instead of agent_sales
        \App\Models\PendingSale::create($validated);

        // Keep product.stock_quantity in sync for Category Management / dashboards
        \App\Models\Product::where('id', $validated['product_id'])->decrement('stock_quantity', $validated['quantity_sold']);

        return redirect()->route('admin.stock.pending-sales')->with('success', 'Sale recorded successfully. Please select payment option and save.');
    }

    public function pendingSales()
    {
        $pendingSales = \App\Models\PendingSale::with(['product.category', 'paymentOption'])->latest('date')->get();
        $paymentOptions = \App\Models\PaymentOption::visible()->orderBy('name')->get();
        return view('admin.stock.pending-sales', compact('pendingSales', 'paymentOptions'));
    }

    public function savePendingSale(Request $request, $id)
    {
        $validated = $request->validate([
            'payment_option_id' => 'required|exists:payment_options,id',
        ]);

        $pendingSale = \App\Models\PendingSale::findOrFail($id);
        $pendingSale->update($validated);

        // Add amount to payment option balance
        if ($pendingSale->paymentOption) {
            $pendingSale->paymentOption->increment('balance', $pendingSale->total_selling_value);
        }

        // Move to agent_sales table
        $agentSaleAttrs = [
            'customer_name' => $pendingSale->customer_name,
            'seller_name' => $pendingSale->seller_name,
            'product_id' => $pendingSale->product_id,
            'quantity_sold' => $pendingSale->quantity_sold,
            'purchase_price' => $pendingSale->purchase_price,
            'selling_price' => $pendingSale->selling_price,
            'total_purchase_value' => $pendingSale->total_purchase_value,
            'total_selling_value' => $pendingSale->total_selling_value,
            'profit' => $pendingSale->profit,
            'balance' => 0, // Already paid via payment option
            'date' => $pendingSale->date,
        ];
        if (Schema::hasColumn('agent_sales', 'agent_id') && $pendingSale->seller_id) {
            $agentSaleAttrs['agent_id'] = $pendingSale->seller_id;
        }
        if (Schema::hasColumn('agent_sales', 'payment_option_id') && $pendingSale->payment_option_id) {
            $agentSaleAttrs['payment_option_id'] = $pendingSale->payment_option_id;
        }
        $agentSale = AgentSale::create($agentSaleAttrs);

        // Update product_list items linked to this pending sale
        \App\Models\ProductListItem::where('pending_sale_id', $pendingSale->id)
            ->update([
                'agent_sale_id' => $agentSale->id,
                'pending_sale_id' => null,
            ]);

        // Delete from pending sales
        $pendingSale->delete();

        return redirect()->route('admin.stock.agent-sales')->with('success', 'Sale saved successfully. Amount added to payment option balance.');
    }

    /**
     * Update all existing products to use sell_price from their latest purchase.
     * This ensures front page products show the correct sell_price instead of unit_price.
     */
    public function updateAllProductPrices()
    {
        $products = \App\Models\Product::all();
        $updatedCount = 0;

        foreach ($products as $product) {
            $latestPurchase = Purchase::where('product_id', $product->id)
                ->whereNotNull('sell_price')
                ->latest('date')
                ->latest('id')
                ->first();
            
            if ($latestPurchase && $latestPurchase->sell_price) {
                $product->update(['price' => $latestPurchase->sell_price]);
                $updatedCount++;
            }
        }

        return redirect()->route('admin.stock.purchases')
            ->with('success', "Updated {$updatedCount} product(s) to use sell_price from their latest purchase.");
    }

    /**
     * Search product_list by IMEI / serial (partial match). Detail: admin.stock.imei-item.
     */
    public function imeiSearch(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $normalized = $q === '' ? '' : preg_replace('/\s+/', '', $q);

        $results = collect();
        if ($normalized !== '' && strlen($normalized) >= 3) {
            $like = '%'.addcslashes($normalized, '%_\\').'%';
            $results = ProductListItem::query()
                ->with(['stock:id,name', 'category:id,name', 'product:id,name'])
                ->where('imei_number', 'like', $like)
                ->orderBy('imei_number')
                ->limit(100)
                ->get();
        }

        return view('admin.stock.imei-search', [
            'q' => $q,
            'normalized' => $normalized,
            'results' => $results,
        ]);
    }

    /**
     * Full admin view for one product_list row (all IMEI / sale / assignment context).
     */
    public function showImeiItem(ProductListItem $productListItem)
    {
        $item = $productListItem->load([
            'purchase.paymentOption',
            'stock',
            'category',
            'product',
            'agentProductListAssignment.agent',
            'agentCredit.agent',
            'agentCredit.paymentOption',
            'pendingSale',
            'agentSale.agent',
        ]);

        return view('admin.stock.imei-detail', compact('item'));
    }

    /**
     * Build a detailed error message categorized by failure reason.
     */
    private function buildDetailedErrorMessage(array $imeis, array $failureReasons): string
    {
        $duplicateCount = count($failureReasons['duplicates'] ?? []);
        $limitExhaustedCount = count($failureReasons['limit_exhausted'] ?? []);
        $totalParsed = count($imeis);
        $totalFailed = $duplicateCount + $limitExhaustedCount;

        $messages = [];
        $messages[] = "❌ No devices added. Parsed $totalParsed IMEI(s), but all failed.";

        if ($duplicateCount > 0) {
            $samples = array_slice($failureReasons['duplicates'], 0, 3);
            $sampleList = implode(', ', $samples);
            $more = $duplicateCount > 3 ? " (+ " . ($duplicateCount - 3) . " more)" : '';
            $messages[] = "• All duplicates: $duplicateCount IMEI(s) already exist in the system. Examples: $sampleList$more";
        }

        if ($limitExhaustedCount > 0) {
            $samples = array_slice($failureReasons['limit_exhausted'], 0, 3);
            $sampleList = implode(', ', $samples);
            $more = $limitExhaustedCount > 3 ? " (+ " . ($limitExhaustedCount - 3) . " more)" : '';
            $messages[] = "• Purchase limit exhausted: $limitExhaustedCount IMEI(s) could not be added because the purchase limit has been reached. Examples: $sampleList$more";
        }

        $messages[] = "\n💡 Solutions:";
        if ($duplicateCount > 0) {
            $messages[] = "  • Check if these IMEIs have already been added to the system";
        }
        if ($limitExhaustedCount > 0) {
            $messages[] = "  • Create a new purchase with additional quantity for this stock";
        }
        if ($duplicateCount === 0 && $limitExhaustedCount === 0) {
            $messages[] = "  • Verify you selected the correct stock and model";
            $messages[] = "  • Check that all IMEIs are properly formatted";
        }

        return implode("\n", $messages);
    }
}
