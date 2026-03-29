{{--
  Full inventory / sale context for one product_list row (keyed by IMEI).
  Expects $item as ProductListItem with relations eager-loaded where possible.
--}}
@php
    $imei = $item->imei_number ?? '—';
    $sold = $item->sold_at !== null;
@endphp
<div class="px-6 py-4 text-sm text-slate-700 space-y-3 border-l-4 border-[#fa8900]/40 bg-slate-50/80">
    <div class="flex flex-wrap items-baseline gap-2">
        <span class="font-mono font-semibold text-slate-900">{{ $imei }}</span>
        @if($sold)
            <span class="text-xs uppercase tracking-wide px-2 py-0.5 rounded bg-slate-200 text-slate-700">Sold</span>
            <span class="text-slate-500">{{ $item->sold_at instanceof \Carbon\Carbon ? $item->sold_at->format('Y-m-d H:i') : $item->sold_at }}</span>
        @else
            <span class="text-xs uppercase tracking-wide px-2 py-0.5 rounded bg-green-100 text-green-800">In stock</span>
        @endif
    </div>

    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2">
        @if($item->purchase)
            <div class="sm:col-span-2">
                <dt class="text-xs uppercase text-slate-500">Purchase / source</dt>
                <dd>
                    {{ $item->purchase->name ?? 'Purchase #' . $item->purchase->id }}
                    @if(!empty($item->purchase->distributor_name))
                        <span class="text-slate-600"> — Supplier / distributor: <span class="font-medium text-slate-800">{{ $item->purchase->distributor_name }}</span></span>
                    @endif
                </dd>
            </div>
        @endif

        @if($item->stock)
            <div>
                <dt class="text-xs uppercase text-slate-500">Stock</dt>
                <dd class="font-medium">{{ $item->stock->name ?? '—' }}</dd>
            </div>
        @endif

        @if(!$sold)
            @if($item->agentProductListAssignment && $item->agentProductListAssignment->agent)
                <div class="sm:col-span-2 rounded-md bg-amber-50 border border-amber-100 px-3 py-2">
                    <dt class="text-xs uppercase text-amber-800 font-semibold">Agent assignment</dt>
                    <dd class="mt-1">
                        Assigned to <strong>{{ $item->agentProductListAssignment->agent->name }}</strong>
                        @if($item->agentProductListAssignment->agent->email)
                            <span class="text-slate-600">({{ $item->agentProductListAssignment->agent->email }})</span>
                        @endif
                    </dd>
                </div>
            @else
                <div class="sm:col-span-2 text-slate-600">
                    <strong>Not assigned</strong> to an agent — available in warehouse / for assignment.
                </div>
            @endif
        @endif

        @if($sold)
            @if($item->agent_credit_id && $item->agentCredit)
                @php $ac = $item->agentCredit; @endphp
                <div class="sm:col-span-2 rounded-md bg-violet-50 border border-violet-100 px-3 py-2 space-y-1">
                    <div class="text-xs uppercase text-violet-800 font-semibold">Credit sale (agent)</div>
                    <div><span class="text-slate-500">Customer:</span> <strong>{{ $ac->customer_name ?? '—' }}</strong>
                        @if(!empty($ac->customer_phone)) <span class="text-slate-600">· {{ $ac->customer_phone }}</span> @endif
                    </div>
                    @if($ac->agent)
                        <div><span class="text-slate-500">Agent:</span> <strong>{{ $ac->agent->name }}</strong></div>
                    @endif
                    <div><span class="text-slate-500">Credit status:</span> <strong>{{ $ac->payment_status ?? '—' }}</strong>
                        — Paid {{ number_format((float) ($ac->paid_amount ?? 0), 2) }} / {{ number_format((float) ($ac->total_amount ?? 0), 2) }} TZS
                    </div>
                    @if($ac->paymentOption)
                        <div><span class="text-slate-500">Channel:</span> {{ $ac->paymentOption->name }}</div>
                    @endif
                </div>
            @elseif($item->pending_sale_id && $item->pendingSale)
                @php $ps = $item->pendingSale; @endphp
                <div class="sm:col-span-2 rounded-md bg-sky-50 border border-sky-100 px-3 py-2 space-y-1">
                    <div class="text-xs uppercase text-sky-800 font-semibold">Pending sale (awaiting payment option)</div>
                    <div><span class="text-slate-500">Customer:</span> <strong>{{ $ps->customer_name ?? '—' }}</strong></div>
                    <div><span class="text-slate-500">Seller / recorded by:</span> {{ $ps->seller_name ?? '—' }}</div>
                    <div><span class="text-slate-500">Sale amount:</span> {{ number_format((float) ($ps->selling_price ?? 0), 2) }} TZS</div>
                </div>
            @elseif($item->agent_sale_id && $item->agentSale)
                @php $as = $item->agentSale; @endphp
                <div class="sm:col-span-2 rounded-md bg-orange-50 border border-orange-100 px-3 py-2 space-y-1">
                    <div class="text-xs uppercase text-orange-800 font-semibold">Agent sale (recorded)</div>
                    <div><span class="text-slate-500">Customer:</span> <strong>{{ $as->customer_name ?? '—' }}</strong></div>
                    @if($as->agent)
                        <div><span class="text-slate-500">Agent:</span> <strong>{{ $as->agent->name }}</strong></div>
                    @endif
                    <div><span class="text-slate-500">Total selling value:</span> {{ number_format((float) ($as->total_selling_value ?? 0), 2) }} TZS</div>
                </div>
            @else
                <div class="sm:col-span-2 text-amber-800 bg-amber-50 border border-amber-100 px-3 py-2 rounded-md">
                    Sold — no linked credit, pending sale, or agent sale row on this device. Check data integrity if this is unexpected.
                </div>
            @endif
        @endif
    </dl>

    <p class="text-xs text-slate-400 pt-1 border-t border-slate-200">
        Product list ID: {{ $item->id }}
        @if($item->product) · Model record: {{ $item->product?->name }} @endif
    </p>
</div>
