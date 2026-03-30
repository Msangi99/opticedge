<x-admin-layout>
    @include('admin.partials.catalog-styles')

    @php
        $purchaseContext = null;
        if ($item->purchase_id && $item->purchase) {
            $purchaseContext = $item->purchase;
        } elseif ($item->stock_id && $item->product_id) {
            $purchaseContext = \App\Models\Purchase::where('stock_id', $item->stock_id)
                ->where('product_id', $item->product_id)
                ->latest('date')
                ->first();
        }
    @endphp

    <div class="admin-prod-page">
        <a href="{{ route('admin.stock.imei-search', ['q' => $item->imei_number]) }}" class="admin-prod-back mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to IMEI search
        </a>

        <div class="admin-prod-toolbar !mb-6">
            <div>
                <p class="admin-prod-eyebrow">Device</p>
                <h1 class="admin-prod-title font-mono tracking-tight">{{ $item->imei_number ?? '—' }}</h1>
                <p class="admin-prod-subtitle">Inventory record and sale context.</p>
            </div>
            <div class="flex flex-wrap gap-2 shrink-0">
                @if($item->stock)
                    <a href="{{ route('admin.stock.stocks.show', $item->stock) }}" class="admin-prod-btn-ghost text-sm">Open stock</a>
                @endif
                @if($item->product)
                    <a href="{{ route('admin.products.imei', $item->product) }}" class="admin-prod-btn-ghost text-sm">All IMEIs for model</a>
                @endif
            </div>
        </div>

        <div class="admin-clay-panel p-6 mb-6">
            <h2 class="text-sm font-semibold text-slate-900 mb-3">Summary</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-sm">
                <div>
                    <dt class="text-xs uppercase text-slate-500">Model (line)</dt>
                    <dd class="font-medium text-slate-800">{{ $item->model ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-slate-500">Product</dt>
                    <dd class="font-medium text-slate-800">{{ $item->product?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-slate-500">Category</dt>
                    <dd class="font-medium text-slate-800">{{ $item->category?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-slate-500">Stock location</dt>
                    <dd class="font-medium text-slate-800">{{ $item->stock?->name ?? '—' }}</dd>
                </div>
                @if($purchaseContext)
                    <div>
                        <dt class="text-xs uppercase text-slate-500">Purchase unit price</dt>
                        <dd class="font-medium text-slate-800">{{ number_format((float) $purchaseContext->unit_price, 2) }} TZS</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-slate-500">Purchase sell price</dt>
                        <dd class="font-medium text-slate-800">
                            {{ $purchaseContext->sell_price !== null ? number_format((float) $purchaseContext->sell_price, 2).' TZS' : '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-slate-500">Purchase payment</dt>
                        <dd class="font-medium text-slate-800">{{ $purchaseContext->payment_status ?? '—' }}</dd>
                    </div>
                    @if($purchaseContext->paymentOption)
                        <div>
                            <dt class="text-xs uppercase text-slate-500">Purchase channel</dt>
                            <dd class="font-medium text-slate-800">{{ $purchaseContext->paymentOption->name }}</dd>
                        </div>
                    @endif
                @endif
            </dl>
        </div>

        <div class="admin-clay-panel overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100/80">
                <h2 class="text-sm font-semibold text-slate-900">Full record</h2>
                <p class="text-xs text-slate-500 mt-0.5">Assignment, sales, and credits linked to this IMEI.</p>
            </div>
            @include('admin.stock.partials.imei-full-info', ['item' => $item])
        </div>
    </div>
</x-admin-layout>
