<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page admin-prod-form-wide">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-8">
            <div>
                <p class="admin-prod-eyebrow">Agents</p>
                <h1 class="admin-prod-title">Agent credit</h1>
                <p class="admin-prod-subtitle">Credit details.</p>
            </div>
            <a href="{{ route('admin.stock.agent-credits') }}" class="admin-prod-back shrink-0">Back to list</a>
        </div>

        @if(session('success'))
            <div class="admin-prod-alert admin-prod-alert--success mb-4" role="status">{{ session('success') }}</div>
        @endif

        <div class="admin-clay-panel admin-prod-form-shell overflow-hidden">
            <div class="admin-prod-form-head">
                <h2 class="admin-prod-form-title">Credit #{{ $credit->id }}</h2>
            </div>
            <form action="{{ route('admin.stock.update-agent-credit', $credit->id) }}" method="POST" class="admin-prod-form-body">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-2">
                            <h3 class="text-lg font-medium text-slate-900 mb-2">Loan</h3>
                        </div>
                        <div>
                            <label class="admin-prod-label">Date</label>
                            <input type="text" readonly class="admin-prod-input cursor-not-allowed" value="{{ $credit->date instanceof \Carbon\Carbon ? $credit->date->format('Y-m-d') : $credit->date }}">
                        </div>
                        <div>
                            <label class="admin-prod-label">Agent</label>
                            <input type="text" readonly class="admin-prod-input cursor-not-allowed" value="{{ $credit->agent?->name ?? '—' }}">
                        </div>
                        <div>
                            <label class="admin-prod-label">Customer</label>
                            <input type="text" readonly class="admin-prod-input cursor-not-allowed" value="{{ $credit->customer_name }}">
                        </div>
                        <div>
                            <label class="admin-prod-label">IMEI</label>
                            <input type="text" readonly class="admin-prod-input font-mono text-sm cursor-not-allowed" value="{{ $credit->productListItem?->imei_number ?? '—' }}">
                        </div>
                        <div class="col-span-2">
                            <label class="admin-prod-label">Product</label>
                            <input type="text" readonly class="admin-prod-input cursor-not-allowed" value="{{ $credit->product ? (($credit->product->category?->name ?? '—') . ' – ' . $credit->product->name) : 'N/A' }}">
                        </div>

                        @php
                            $creditTotal = (float) $credit->total_amount;
                        @endphp
                        <div class="col-span-2 border-t border-slate-100 pt-4">
                            <label class="admin-prod-label">Total credit</label>
                            <input type="text" readonly class="admin-prod-input font-bold cursor-not-allowed" value="{{ number_format($creditTotal, 2) }} TZS">
                        </div>

                        <div class="col-span-2 border-t border-slate-100 pt-4 mt-2">
                            <h3 class="text-lg font-medium text-slate-900 mb-4">Payment history</h3>
                            @if($credit->payments && $credit->payments->count() > 0)
                                <div class="overflow-x-auto border border-slate-200 rounded-lg">
                                    <table class="w-full text-sm">
                                        <thead class="bg-slate-100">
                                            <tr>
                                                <th class="px-4 py-2 text-left">Date</th>
                                                <th class="px-4 py-2 text-left">Channel</th>
                                                <th class="px-4 py-2 text-right">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($credit->payments as $pay)
                                                <tr class="border-t border-slate-100">
                                                    <td class="px-4 py-2">{{ $pay->paid_date?->format('Y-m-d') ?? ($pay->created_at?->format('Y-m-d') ?? '—') }}</td>
                                                    <td class="px-4 py-2">{{ $pay->paymentOption?->name ?? '—' }}</td>
                                                    <td class="px-4 py-2 text-right font-medium">{{ number_format((float) $pay->amount, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-slate-500">No payment rows yet.</p>
                            @endif
                        </div>

                        <div class="col-span-2 admin-prod-form-footer !mt-4">
                            <a href="{{ route('admin.stock.agent-credits') }}" class="admin-prod-btn-ghost">Cancel</a>
                            <button type="submit" class="admin-prod-btn-primary px-8">Update</button>
                        </div>
                    </div>
                </form>
        </div>
    </div>
</x-admin-layout>
