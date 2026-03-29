<x-admin-layout>
    <div class="py-12 px-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Agent Credit</h1>
                <p class="mt-2 text-slate-600">Loans from agents to customers. Record repayments on each credit (like purchases).</p>
            </div>
        </div>

        @if(session('success'))
            <p class="mt-4 rounded-lg bg-green-50 px-4 py-2 text-sm text-green-800">{{ session('success') }}</p>
        @endif
        @if($errors->any())
            <p class="mt-4 rounded-lg bg-red-50 px-4 py-2 text-sm text-red-800">{{ $errors->first() }}</p>
        @endif

        <x-admin-page-dashboard label="Summary (current filter)" class="mt-8">
            <dl class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <dt class="text-xs uppercase text-slate-500">Credits</dt>
                    <dd class="text-lg font-semibold text-slate-900">{{ number_format($agentCreditsDashboard['count']) }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-slate-500">Total credit</dt>
                    <dd class="text-lg font-semibold text-slate-900">{{ number_format($agentCreditsDashboard['total_credit'], 2) }} TZS</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-slate-500">Total paid</dt>
                    <dd class="text-lg font-semibold text-green-700">{{ number_format($agentCreditsDashboard['total_paid'], 2) }} TZS</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-slate-500">Pending</dt>
                    <dd class="text-lg font-semibold text-amber-700">{{ number_format($agentCreditsDashboard['total_pending'], 2) }} TZS</dd>
                </div>
            </dl>
        </x-admin-page-dashboard>

        <div class="mt-8 bg-white rounded-lg shadow-sm border border-slate-200 p-4">
            <form method="GET" action="{{ route('admin.stock.agent-credits') }}" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label for="date_from" class="block text-sm font-medium text-slate-700 mb-1">From Date</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-slate-700 mb-1">To Date</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-[#fa8900] text-white px-4 py-2 rounded-lg hover:bg-[#fa8900]/90 transition-colors font-medium">Filter</button>
                    @if(request('date_from') || request('date_to'))
                        <a href="{{ route('admin.stock.agent-credits') }}" class="bg-slate-100 text-slate-700 px-4 py-2 rounded-lg hover:bg-slate-200 transition-colors font-medium">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="mt-8 bg-white rounded-lg shadow-sm border border-slate-200 overflow-x-auto">
            <table class="w-full text-left min-w-[960px]">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="px-6 py-3 bg-gray-100">Date</th>
                        <th class="px-6 py-3 bg-gray-100">Agent</th>
                        <th class="px-6 py-3 bg-gray-100">Customer</th>
                        <th class="px-6 py-3 bg-gray-100">Product</th>
                        <th class="px-6 py-3 bg-gray-100">IMEI</th>
                        <th class="px-6 py-3 bg-gray-100">Total</th>
                        <th class="px-6 py-3 bg-gray-100 min-w-[200px]">Channel</th>
                        <th class="px-6 py-3 bg-gray-100">Pending</th>
                        <th class="px-6 py-3 bg-gray-100">Status</th>
                        <th class="px-6 py-3 bg-gray-100">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($credits as $credit)
                        @php
                            $t = (float) $credit->total_amount;
                            $pend = max(0, $t - (float) ($credit->paid_amount ?? 0));
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3">{{ $credit->date instanceof \Carbon\Carbon ? $credit->date->format('Y-m-d') : $credit->date }}</td>
                            <td class="px-6 py-3">{{ $credit->agent?->name ?? '—' }}</td>
                            <td class="px-6 py-3 font-medium">{{ $credit->customer_name }}</td>
                            <td class="px-6 py-3">{{ $credit->product ? (($credit->product->category?->name ?? '—') . ' – ' . $credit->product->name) : 'N/A' }}</td>
                            <td class="px-6 py-3 font-mono text-xs">{{ $credit->productListItem?->imei_number ?? '—' }}</td>
                            <td class="px-6 py-3">{{ number_format($t, 2) }}</td>
                            <td class="px-6 py-3 align-middle">
                                @if($paymentOptions->isEmpty())
                                    <span class="text-slate-500">{{ $credit->paymentOption?->name ?? '—' }}</span>
                                @else
                                    <form method="POST" action="{{ route('admin.stock.agent-credit-payment-channel', $credit->id) }}" class="inline-block min-w-[180px]">
                                        @csrf
                                        @method('PATCH')
                                        <select
                                            name="payment_option_id"
                                            class="w-full max-w-[220px] rounded-md border-slate-300 text-sm shadow-sm focus:border-[#fa8900] focus:ring-[#fa8900]"
                                            onchange="this.form.submit()"
                                            title="Bank / payment channel"
                                        >
                                            <option value="">— None —</option>
                                            @foreach($paymentOptions as $option)
                                                <option value="{{ $option->id }}" @selected((int) ($credit->payment_option_id ?? 0) === (int) $option->id)>
                                                    {{ $option->name }} ({{ number_format((float) $option->balance, 2) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                @endif
                            </td>
                            <td class="px-6 py-3 font-medium text-amber-800">{{ number_format($pend, 2) }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-1 rounded text-xs font-bold uppercase
                                    {{ $credit->payment_status === 'paid' ? 'bg-green-100 text-green-800' :
                                       ($credit->payment_status === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $credit->payment_status }}
                                </span>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                <a href="{{ route('admin.stock.edit-agent-credit', $credit->id) }}" class="text-slate-700 hover:text-slate-900 font-medium hover:underline">Edit</a>
                                <span class="mx-2 text-slate-300">·</span>
                                <a href="{{ route('admin.stock.edit-agent-credit', $credit->id) }}#repayment" class="text-[#fa8900] hover:underline font-medium">Pay</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-8 text-center text-slate-500">No agent credits yet. Credits appear when an agent sells on credit from the app.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($credits->hasPages())
            <div class="mt-4">{{ $credits->links() }}</div>
        @endif
    </div>
</x-admin-layout>
