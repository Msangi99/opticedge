<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page">
        <div class="admin-prod-toolbar">
            <div>
                <p class="admin-prod-eyebrow">Agent transfer</p>
                <h1 class="admin-prod-title">Request #{{ $transfer->id }}</h1>
                <p class="admin-prod-subtitle">Full details for approval.</p>
            </div>
            <a href="{{ route('admin.stock.agent-transfers') }}" class="admin-prod-btn-ghost shrink-0">Back to list</a>
        </div>

        @if(session('success'))
            <div class="admin-prod-alert admin-prod-alert--success mb-4" role="status">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="admin-prod-alert admin-prod-alert--error mb-4" role="alert">{{ session('error') }}</div>
        @endif

        <div class="admin-clay-panel mb-6 space-y-4 p-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-500">Status</p>
                    <p class="text-lg font-medium text-slate-900">{{ $transfer->status }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-500">Created</p>
                    <p class="text-slate-900">{{ $transfer->created_at->format('Y-m-d H:i:s') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-500">From agent</p>
                    <p class="font-medium text-slate-900">{{ $transfer->fromAgent->name }}</p>
                    <p class="text-sm text-slate-600">{{ $transfer->fromAgent->email }}</p>
                    <p class="text-xs text-slate-500">User ID {{ $transfer->fromAgent->id }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-500">To agent</p>
                    <p class="font-medium text-slate-900">{{ $transfer->toAgent->name }}</p>
                    <p class="text-sm text-slate-600">{{ $transfer->toAgent->email }}</p>
                    <p class="text-xs text-slate-500">User ID {{ $transfer->toAgent->id }}</p>
                </div>
            </div>
            @if($transfer->message)
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-500">Agent message</p>
                    <p class="text-slate-800">{{ $transfer->message }}</p>
                </div>
            @endif
            @if($transfer->admin_note)
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-500">Admin note</p>
                    <p class="text-slate-800">{{ $transfer->admin_note }}</p>
                </div>
            @endif
            @if($transfer->decided_at)
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-500">Decided</p>
                    <p class="text-slate-800">{{ $transfer->decided_at->format('Y-m-d H:i:s') }}
                        @if($transfer->decidedByUser) by {{ $transfer->decidedByUser->name }} @endif
                    </p>
                </div>
            @endif
        </div>

        <div class="admin-clay-panel overflow-x-auto">
            <h2 class="border-b border-slate-200 px-4 py-3 text-sm font-semibold text-slate-900">Devices</h2>
            <table class="min-w-[1000px] w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="px-4 py-2 text-left font-semibold">IMEI</th>
                        <th class="px-4 py-2 text-left font-semibold">Model</th>
                        <th class="px-4 py-2 text-left font-semibold">Product</th>
                        <th class="px-4 py-2 text-left font-semibold">Category</th>
                        <th class="px-4 py-2 text-left font-semibold">Stock</th>
                        <th class="px-4 py-2 text-left font-semibold">Purchase</th>
                        <th class="px-4 py-2 text-left font-semibold">Branch (effective)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transfer->items as $ti)
                        @php $i = $ti->productListItem; @endphp
                        <tr class="border-b border-slate-100">
                            <td class="px-4 py-2 font-mono text-xs">{{ $i->imei_number }}</td>
                            <td class="px-4 py-2">{{ $i->model ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $i->product->name ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $i->product->category->name ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $i->stock->name ?? '—' }} @if($i->stock_id)<span class="text-xs text-slate-400">#{{ $i->stock_id }}</span>@endif</td>
                            <td class="px-4 py-2">
                                @if($i->purchase)
                                    {{ $i->purchase->name ?? 'Purchase #'.$i->purchase_id }}
                                    <span class="block text-xs text-slate-500">{{ $i->purchase->date ?? '' }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                @php
                                    $bid = $i->effectiveBranchId();
                                    $bname = $i->branch?->name ?? $i->purchase?->branch?->name;
                                @endphp
                                {{ $bname ?? ($bid ? 'Branch #'.$bid : 'Unassigned') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($transfer->status === 'pending')
            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <div class="admin-clay-panel p-6">
                    <h3 class="mb-3 text-sm font-semibold text-slate-900">Approve</h3>
                    <form method="POST" action="{{ route('admin.stock.agent-transfers.approve', $transfer) }}">
                        @csrf
                        <label class="admin-prod-label">Admin note (optional)</label>
                        <textarea name="admin_note" class="admin-prod-input mt-1 w-full" rows="2"></textarea>
                        <button type="submit" class="admin-prod-btn-primary mt-3">Approve transfer</button>
                    </form>
                </div>
                <div class="admin-clay-panel p-6">
                    <h3 class="mb-3 text-sm font-semibold text-slate-900">Reject</h3>
                    <form method="POST" action="{{ route('admin.stock.agent-transfers.reject', $transfer) }}">
                        @csrf
                        <label class="admin-prod-label">Admin note (optional)</label>
                        <textarea name="admin_note" class="admin-prod-input mt-1 w-full" rows="2"></textarea>
                        <button type="submit" class="mt-3 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-medium text-red-800 hover:bg-red-100">Reject transfer</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-admin-layout>
