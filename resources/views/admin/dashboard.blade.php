<x-admin-layout>
    <div class="py-12 px-8">
        <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
        <p class="mt-2 text-slate-600">Overview of your store performance.</p>

        <!-- Stats Grid -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Customers -->
            <a href="{{ route('admin.customers.index') }}"
                class="group admin-clay-panel-interactive p-6 transition-all relative overflow-hidden">
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
                class="group admin-clay-panel-interactive p-6 transition-all relative overflow-hidden">
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
                class="group admin-clay-panel-interactive p-6 transition-all relative overflow-hidden">
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

        <!-- Sales Metrics Cards -->
        @if(isset($salesMetrics))
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Today Sales -->
            <div class="admin-clay-panel p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-medium text-slate-500 mb-1">Mauzo ya Leo</p>
                <p class="text-2xl font-bold text-slate-900 mb-2">{{ number_format($salesMetrics['today']['sales'], 0) }} TZS</p>
                @if($salesMetrics['today']['percentage_change'] !== null)
                    <div class="flex items-center gap-1">
                        @if($salesMetrics['today']['is_increase'])
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            <span class="text-sm font-medium text-green-600">{{ number_format(abs($salesMetrics['today']['percentage_change']), 1) }}%</span>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                            </svg>
                            <span class="text-sm font-medium text-red-600">{{ number_format(abs($salesMetrics['today']['percentage_change']), 1) }}%</span>
                        @endif
                        <span class="text-xs text-slate-500">vs jana</span>
                    </div>
                @else
                    <span class="text-xs text-slate-500">Hakuna data ya jana</span>
                @endif
            </div>

            <!-- WTD Sales -->
            <div class="admin-clay-panel p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-purple-50 text-purple-600 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-medium text-slate-500 mb-1">WTD (Weekly To Date)</p>
                <p class="text-2xl font-bold text-slate-900 mb-2">{{ number_format($salesMetrics['wtd']['sales'], 0) }} TZS</p>
                @if($salesMetrics['wtd']['percentage_change'] !== null)
                    <div class="flex items-center gap-1">
                        @if($salesMetrics['wtd']['is_increase'])
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            <span class="text-sm font-medium text-green-600">{{ number_format(abs($salesMetrics['wtd']['percentage_change']), 1) }}%</span>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                            </svg>
                            <span class="text-sm font-medium text-red-600">{{ number_format(abs($salesMetrics['wtd']['percentage_change']), 1) }}%</span>
                        @endif
                        <span class="text-xs text-slate-500">vs wiki iliyopita</span>
                    </div>
                @else
                    <span class="text-xs text-slate-500">Hakuna data ya wiki iliyopita</span>
                @endif
            </div>

            <!-- MTD Sales -->
            <div class="admin-clay-panel p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-green-50 text-green-600 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-medium text-slate-500 mb-1">MTD (Monthly To Date)</p>
                <p class="text-2xl font-bold text-slate-900 mb-2">{{ number_format($salesMetrics['mtd']['sales'], 0) }} TZS</p>
                @if($salesMetrics['mtd']['percentage_change'] !== null)
                    <div class="flex items-center gap-1">
                        @if($salesMetrics['mtd']['is_increase'])
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            <span class="text-sm font-medium text-green-600">{{ number_format(abs($salesMetrics['mtd']['percentage_change']), 1) }}%</span>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                            </svg>
                            <span class="text-sm font-medium text-red-600">{{ number_format(abs($salesMetrics['mtd']['percentage_change']), 1) }}%</span>
                        @endif
                        <span class="text-xs text-slate-500">vs mwezi uliopita</span>
                    </div>
                @else
                    <span class="text-xs text-slate-500">Hakuna data ya mwezi uliopita</span>
                @endif
            </div>

            <!-- YTD Sales -->
            <div class="admin-clay-panel p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-amber-50 text-amber-600 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-medium text-slate-500 mb-1">YTD (Yearly To Date)</p>
                <p class="text-2xl font-bold text-slate-900 mb-2">{{ number_format($salesMetrics['ytd']['sales'], 0) }} TZS</p>
                @if($salesMetrics['ytd']['percentage_change'] !== null)
                    <div class="flex items-center gap-1">
                        @if($salesMetrics['ytd']['is_increase'])
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            <span class="text-sm font-medium text-green-600">{{ number_format(abs($salesMetrics['ytd']['percentage_change']), 1) }}%</span>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                            </svg>
                            <span class="text-sm font-medium text-red-600">{{ number_format(abs($salesMetrics['ytd']['percentage_change']), 1) }}%</span>
                        @endif
                        <span class="text-xs text-slate-500">vs mwaka uliopita</span>
                    </div>
                @else
                    <span class="text-xs text-slate-500">Hakuna data ya mwaka uliopita</span>
                @endif
            </div>
        </div>
        @endif

        <!-- Financial Metrics -->
        @if(isset($financialMetrics))
        <div class="mt-8 admin-clay-panel overflow-hidden">
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
                        <p class="text-xl font-bold text-slate-900 mt-1">{{ number_format(isset($paymentOptions) ? $paymentOptions->sum('balance') : $financialMetrics['cash_in_hand'], 0) }} TZS</p>
                        <p class="text-xs text-slate-500 mt-1">Total amount in all payment options</p>
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
                    <div class="p-4 rounded-lg bg-indigo-50 border border-indigo-100">
                        <p class="text-sm font-medium text-slate-600">Total Purchase Buy Price</p>
                        <p class="text-xl font-bold text-slate-900 mt-1">{{ number_format($financialMetrics['total_purchase_buy_price'], 0) }} TZS</p>
                        <p class="text-xs text-slate-500 mt-1">Total buy price of all purchases</p>
                    </div>
                    <div class="p-4 rounded-lg bg-teal-50 border border-teal-100">
                        <p class="text-sm font-medium text-slate-600">Total Products in Purchases</p>
                        <p class="text-xl font-bold text-slate-900 mt-1">{{ number_format($financialMetrics['total_products_in_purchases'], 0) }}</p>
                        <p class="text-xs text-slate-500 mt-1">Total products in all purchases</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Cash in Hand Section -->
        @if(isset($paymentOptions) && $paymentOptions->count() > 0)
        <div class="mt-8 admin-clay-panel overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200">
                <h3 class="font-bold text-slate-800">Cash in Hand</h3>
                <p class="text-sm text-slate-500 mt-0.5">Payment options and their current balances</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($paymentOptions as $option)
                        @php
                            $currentBalance = $option->balance ?? 0;
                            $openingBalance = $option->opening_balance ?? 0;
                            $difference = $currentBalance - $openingBalance;
                            $percentageChange = $openingBalance > 0 ? (($difference / $openingBalance) * 100) : 0;
                            $isIncrease = $difference > 0;
                            $isDecrease = $difference < 0;
                        @endphp
                        <div class="p-4 rounded-lg border {{ $option->type === 'mobile' ? 'bg-blue-50 border-blue-100' : ($option->type === 'bank' ? 'bg-green-50 border-green-100' : 'bg-amber-50 border-amber-100') }}">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm font-medium text-slate-600">{{ $option->name }}</p>
                                <span class="px-2 py-1 rounded text-xs font-medium {{ $option->type === 'mobile' ? 'bg-blue-100 text-blue-800' : ($option->type === 'bank' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ ucfirst($option->type) }}
                                </span>
                            </div>
                            <p class="text-2xl font-bold text-slate-900">{{ number_format($currentBalance, 0) }} TZS</p>
                            <div class="mt-2 pt-2 border-t border-slate-200 space-y-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-medium text-slate-600">Opening Balance:</p>
                                    <p class="text-xs font-semibold text-slate-800">{{ number_format($openingBalance, 0) }} TZS</p>
                                </div>
                                @if($difference != 0)
                                    <div class="flex items-center gap-1">
                                        @if($isIncrease)
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                            </svg>
                                            <span class="text-xs font-semibold text-green-600">Imepanda {{ number_format(abs($difference), 0) }} TZS ({{ number_format(abs($percentageChange), 1) }}%)</span>
                                        @elseif($isDecrease)
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                            </svg>
                                            <span class="text-xs font-semibold text-red-600">Imeshuka {{ number_format(abs($difference), 0) }} TZS ({{ number_format(abs($percentageChange), 1) }}%)</span>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-xs text-slate-500">Hakuna mabadiliko</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 pt-4 border-t border-slate-200">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-600">Total Cash in Hand</p>
                        <p class="text-xl font-bold text-slate-900">{{ number_format($paymentOptions->sum('balance'), 0) }} TZS</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Top Selling Products Chart -->
        <div class="mt-8 admin-clay-panel overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-slate-800">Top Selling Products (Models)</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Products sold by quantity</p>
                </div>
                <form method="GET" action="{{ route('admin.dashboard') }}" class="flex gap-3 items-end">
                    <div>
                        <label for="start_date" class="block text-xs font-medium text-slate-600 mb-1">Start Date</label>
                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}" class="rounded-md border-slate-300 shadow-sm text-sm">
                    </div>
                    <div>
                        <label for="end_date" class="block text-xs font-medium text-slate-600 mb-1">End Date</label>
                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}" class="rounded-md border-slate-300 shadow-sm text-sm">
                    </div>
                    <button type="submit" class="bg-[#fa8900] text-white px-4 py-2 rounded-lg hover:bg-[#fa8900]/90 transition-colors text-sm font-medium">
                        Filter
                    </button>
                </form>
            </div>
            <div class="p-6">
                @if(count($topProducts) > 0)
                    <canvas id="topProductsChart" style="max-height: 400px;"></canvas>
                @else
                    <div class="text-center py-8 text-slate-500">
                        <p>No sales data found for the selected date range.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="mt-8 admin-clay-panel overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                <h3 class="font-bold text-slate-800">Recent Orders</h3>
                <a href="{{ route('admin.orders.index') }}"
                    class="text-sm text-[#007185] hover:text-[#c7511f] font-medium hover:underline">View All</a>
            </div>
            <table class="admin-clay-table w-full text-left">
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

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        @if(count($topProducts) > 0)
        const ctx = document.getElementById('topProductsChart');
        if (ctx) {
            const chartData = @json($topProducts);
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.map(item => item.model),
                    datasets: [{
                        label: 'Quantity Sold',
                        data: chartData.map(item => item.total_quantity),
                        backgroundColor: '#fa8900',
                        borderColor: '#e67d00',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Quantity: ' + context.parsed.y;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    }
                }
            });
        }
        @endif
    </script>
</x-admin-layout>