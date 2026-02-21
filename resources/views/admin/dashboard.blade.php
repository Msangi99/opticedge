<x-admin-layout>
    <div class="py-12 px-8">
        <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
        <p class="mt-2 text-slate-600">Overview of your store performance.</p>

        <!-- Stats Grid -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Customers -->
            <a href="{{ route('admin.customers.index') }}"
                class="group bg-white p-6 rounded-lg shadow-sm border border-slate-200 hover:border-[#fa8900] transition-colors relative overflow-hidden">
                <div class="flex items-center gap-4 relative z-10">
                    <div
                        class="p-3 bg-blue-50 text-blue-600 rounded-full group-hover:bg-[#fa8900] group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Customers</p>
                        <p class="text-2xl font-bold text-slate-900">{{ number_format($totalCustomers) }}</p>
                    </div>
                </div>
            </a>

            <!-- Orders -->
            <a href="{{ route('admin.orders.index') }}"
                class="group bg-white p-6 rounded-lg shadow-sm border border-slate-200 hover:border-[#fa8900] transition-colors relative overflow-hidden">
                <div class="flex items-center gap-4 relative z-10">
                    <div
                        class="p-3 bg-purple-50 text-purple-600 rounded-full group-hover:bg-[#fa8900] group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Orders</p>
                        <p class="text-2xl font-bold text-slate-900">{{ number_format($totalOrders) }}</p>
                    </div>
                </div>
            </a>

            <!-- Products -->
            <a href="{{ route('admin.products.index') }}"
                class="group bg-white p-6 rounded-lg shadow-sm border border-slate-200 hover:border-[#fa8900] transition-colors relative overflow-hidden">
                <div class="flex items-center gap-4 relative z-10">
                    <div
                        class="p-3 bg-green-50 text-green-600 rounded-full group-hover:bg-[#fa8900] group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Products</p>
                        <p class="text-2xl font-bold text-slate-900">{{ number_format($totalProducts) }}</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Financial Metrics -->
        @if(isset($financialMetrics))
        <div class="mt-8 bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200">
                <h3 class="font-bold text-slate-800">Financial Summary</h3>
                <p class="text-sm text-slate-500 mt-0.5">Payables, receivables, stock value, and profit overview.</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="p-4 rounded-lg bg-amber-50 border border-amber-100">
                        <p class="text-sm font-medium text-slate-600">Payables</p>
                        <p class="text-xl font-bold text-slate-900 mt-1">{{ number_format($financialMetrics['payables'], 0) }} TZS</p>
                        <p class="text-xs text-slate-500 mt-1">Total pending (not paid) from purchases</p>
                    </div>
                    <div class="p-4 rounded-lg bg-blue-50 border border-blue-100">
                        <p class="text-sm font-medium text-slate-600">Receivables</p>
                        <p class="text-xl font-bold text-slate-900 mt-1">{{ number_format($financialMetrics['receivables'], 0) }} TZS</p>
                        <p class="text-xs text-slate-500 mt-1">Pending from Distribution Sales</p>
                    </div>
                    <div class="p-4 rounded-lg bg-emerald-50 border border-emerald-100">
                        <p class="text-sm font-medium text-slate-600">Stock in Hand Value</p>
                        <p class="text-xl font-bold text-slate-900 mt-1">{{ number_format($financialMetrics['stock_in_hand_value'], 0) }} TZS</p>
                        <p class="text-xs text-slate-500 mt-1">Total value of our stock</p>
                    </div>
                    <div class="p-4 rounded-lg bg-violet-50 border border-violet-100">
                        <p class="text-sm font-medium text-slate-600">Cash in Hand</p>
                        <p class="text-xl font-bold text-slate-900 mt-1">{{ number_format($financialMetrics['cash_in_hand'], 0) }} TZS</p>
                        <p class="text-xs text-slate-500 mt-1">Total value of stocks given to agents</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-slate-200 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="p-4 rounded-lg bg-slate-50 border border-slate-200">
                        <p class="text-sm font-medium text-slate-600">Total Value</p>
                        <p class="text-xl font-bold text-slate-900 mt-1">{{ number_format($financialMetrics['total_value'], 0) }} TZS</p>
                        <p class="text-xs text-slate-500 mt-1">Receivables + Stock in Hand + Cash in Hand</p>
                    </div>
                    <div class="p-4 rounded-lg bg-green-50 border border-green-100">
                        <p class="text-sm font-medium text-slate-600">Gross Profit</p>
                        <p class="text-xl font-bold text-green-800 mt-1">{{ number_format($financialMetrics['gross_profit'], 0) }} TZS</p>
                        <p class="text-xs text-slate-500 mt-1">Distribution Sales + Agent Sales profit</p>
                    </div>
                    <div class="p-4 rounded-lg bg-red-50 border border-red-100">
                        <p class="text-sm font-medium text-slate-600">Total Expenses</p>
                        <p class="text-xl font-bold text-red-800 mt-1">{{ number_format($financialMetrics['total_expenses'], 0) }} TZS</p>
                        <p class="text-xs text-slate-500 mt-1">From Expenses section</p>
                    </div>
                    <div class="p-4 rounded-lg {{ $financialMetrics['net_profit'] >= 0 ? 'bg-green-50 border-green-100' : 'bg-red-50 border-red-100' }} border">
                        <p class="text-sm font-medium text-slate-600">Net Profit</p>
                        <p class="text-xl font-bold mt-1 {{ $financialMetrics['net_profit'] >= 0 ? 'text-green-800' : 'text-red-800' }}">{{ number_format($financialMetrics['net_profit'], 0) }} TZS</p>
                        <p class="text-xs text-slate-500 mt-1">Gross profit − Total expenses</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Recent Orders -->
        <div class="mt-8 bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                <h3 class="font-bold text-slate-800">Recent Orders</h3>
                <a href="{{ route('admin.orders.index') }}"
                    class="text-sm text-[#007185] hover:text-[#c7511f] font-medium hover:underline">View All</a>
            </div>
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="px-6 py-3">Order ID</th>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3">Total</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3 font-medium">#{{ $order->id }}</td>
                            <td class="px-6 py-3">{{ $order->user->name ?? 'Guest' }}</td>
                            <td class="px-6 py-3 font-bold">{{ number_format($order->total_price, 0) }} TZS</td>
                            <td class="px-6 py-3">
                                <span
                                    class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $order->status == 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $order->status }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('admin.orders.show', $order) }}"
                                    class="text-slate-400 hover:text-[#fa8900]">
                                    Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-slate-500">No recent orders.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>