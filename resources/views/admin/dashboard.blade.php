<x-admin-layout>
    <div class="px-2">
        <h1 class="title is-3">Dashboard</h1>
        <p class="subtitle">Overview of your store performance.</p>

        <div class="columns is-multiline mt-5">
            <div class="column is-12-mobile is-4-tablet">
                <a href="{{ route('admin.customers.index') }}" class="box block admin-stat-link">
                    <div class="is-flex is-align-items-center" style="gap: 1rem;">
                        <span class="icon is-large">
                            <span class="icon has-background-info-light has-text-info"
                                style="width: 3rem; height: 3rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </span>
                        </span>
                        <div>
                            <p class="is-size-7 has-text-grey">Total Customers</p>
                            <p class="title is-4 mb-0">{{ number_format($totalCustomers) }}</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="column is-12-mobile is-4-tablet">
                <a href="{{ route('admin.orders.index') }}" class="box block admin-stat-link">
                    <div class="is-flex is-align-items-center" style="gap: 1rem;">
                        <span class="icon is-large">
                            <span class="has-background-link-light has-text-link"
                                style="width: 3rem; height: 3rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </span>
                        </span>
                        <div>
                            <p class="is-size-7 has-text-grey">Total Orders</p>
                            <p class="title is-4 mb-0">{{ number_format($totalOrders) }}</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="column is-12-mobile is-4-tablet">
                <a href="{{ route('admin.products.index') }}" class="box block admin-stat-link">
                    <div class="is-flex is-align-items-center" style="gap: 1rem;">
                        <span class="icon is-large">
                            <span class="has-background-success-light has-text-success"
                                style="width: 3rem; height: 3rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </span>
                        </span>
                        <div>
                            <p class="is-size-7 has-text-grey">Total Products</p>
                            <p class="title is-4 mb-0">{{ number_format($totalProducts) }}</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        @if(isset($salesMetrics))
            <div class="columns is-multiline mt-5">
                <div class="column is-12-mobile is-6-tablet is-3-desktop">
                    <div class="box">
                        <div class="is-flex is-justify-content-flex-end mb-3">
                            <span class="has-background-info-light has-text-info"
                                style="width: 3rem; height: 3rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                        </div>
                        <p class="is-size-7 has-text-grey mb-1">Mauzo ya Leo</p>
                        <p class="title is-4 mb-2">{{ number_format($salesMetrics['today']['sales'], 0) }} TZS</p>
                        @if($salesMetrics['today']['percentage_change'] !== null)
                            <div class="is-flex is-align-items-center" style="gap: 0.25rem;">
                                @if($salesMetrics['today']['is_increase'])
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 has-text-success" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                    <span class="is-size-7 has-text-success has-text-weight-semibold">{{ number_format(abs($salesMetrics['today']['percentage_change']), 1) }}%</span>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 has-text-danger" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                    </svg>
                                    <span class="is-size-7 has-text-danger has-text-weight-semibold">{{ number_format(abs($salesMetrics['today']['percentage_change']), 1) }}%</span>
                                @endif
                                <span class="is-size-7 has-text-grey">vs jana</span>
                            </div>
                        @else
                            <span class="is-size-7 has-text-grey">Hakuna data ya jana</span>
                        @endif
                    </div>
                </div>
                <div class="column is-12-mobile is-6-tablet is-3-desktop">
                    <div class="box">
                        <div class="is-flex is-justify-content-flex-end mb-3">
                            <span class="has-background-link-light has-text-link"
                                style="width: 3rem; height: 3rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </span>
                        </div>
                        <p class="is-size-7 has-text-grey mb-1">WTD (Weekly To Date)</p>
                        <p class="title is-4 mb-2">{{ number_format($salesMetrics['wtd']['sales'], 0) }} TZS</p>
                        @if($salesMetrics['wtd']['percentage_change'] !== null)
                            <div class="is-flex is-align-items-center" style="gap: 0.25rem;">
                                @if($salesMetrics['wtd']['is_increase'])
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 has-text-success" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                    <span class="is-size-7 has-text-success has-text-weight-semibold">{{ number_format(abs($salesMetrics['wtd']['percentage_change']), 1) }}%</span>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 has-text-danger" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                    </svg>
                                    <span class="is-size-7 has-text-danger has-text-weight-semibold">{{ number_format(abs($salesMetrics['wtd']['percentage_change']), 1) }}%</span>
                                @endif
                                <span class="is-size-7 has-text-grey">vs wiki iliyopita</span>
                            </div>
                        @else
                            <span class="is-size-7 has-text-grey">Hakuna data ya wiki iliyopita</span>
                        @endif
                    </div>
                </div>
                <div class="column is-12-mobile is-6-tablet is-3-desktop">
                    <div class="box">
                        <div class="is-flex is-justify-content-flex-end mb-3">
                            <span class="has-background-success-light has-text-success"
                                style="width: 3rem; height: 3rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </span>
                        </div>
                        <p class="is-size-7 has-text-grey mb-1">MTD (Monthly To Date)</p>
                        <p class="title is-4 mb-2">{{ number_format($salesMetrics['mtd']['sales'], 0) }} TZS</p>
                        @if($salesMetrics['mtd']['percentage_change'] !== null)
                            <div class="is-flex is-align-items-center" style="gap: 0.25rem;">
                                @if($salesMetrics['mtd']['is_increase'])
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 has-text-success" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                    <span class="is-size-7 has-text-success has-text-weight-semibold">{{ number_format(abs($salesMetrics['mtd']['percentage_change']), 1) }}%</span>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 has-text-danger" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                    </svg>
                                    <span class="is-size-7 has-text-danger has-text-weight-semibold">{{ number_format(abs($salesMetrics['mtd']['percentage_change']), 1) }}%</span>
                                @endif
                                <span class="is-size-7 has-text-grey">vs mwezi uliopita</span>
                            </div>
                        @else
                            <span class="is-size-7 has-text-grey">Hakuna data ya mwezi uliopita</span>
                        @endif
                    </div>
                </div>
                <div class="column is-12-mobile is-6-tablet is-3-desktop">
                    <div class="box">
                        <div class="is-flex is-justify-content-flex-end mb-3">
                            <span class="has-background-warning-light has-text-warning"
                                style="width: 3rem; height: 3rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                        </div>
                        <p class="is-size-7 has-text-grey mb-1">YTD (Yearly To Date)</p>
                        <p class="title is-4 mb-2">{{ number_format($salesMetrics['ytd']['sales'], 0) }} TZS</p>
                        @if($salesMetrics['ytd']['percentage_change'] !== null)
                            <div class="is-flex is-align-items-center" style="gap: 0.25rem;">
                                @if($salesMetrics['ytd']['is_increase'])
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 has-text-success" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                    <span class="is-size-7 has-text-success has-text-weight-semibold">{{ number_format(abs($salesMetrics['ytd']['percentage_change']), 1) }}%</span>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 has-text-danger" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                    </svg>
                                    <span class="is-size-7 has-text-danger has-text-weight-semibold">{{ number_format(abs($salesMetrics['ytd']['percentage_change']), 1) }}%</span>
                                @endif
                                <span class="is-size-7 has-text-grey">vs mwaka uliopita</span>
                            </div>
                        @else
                            <span class="is-size-7 has-text-grey">Hakuna data ya mwaka uliopita</span>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if(isset($financialMetrics))
            <div class="box mt-5 p-0">
                <div class="px-5 py-4" style="border-bottom: 1px solid #ededed;">
                    <h2 class="title is-5 mb-1">Financial Summary</h2>
                    <p class="is-size-7 has-text-grey">Payables, receivables, stock value, and profit overview.</p>
                </div>
                <div class="p-5">
                    <div class="columns is-multiline is-variable is-2">
                        <div class="column is-12-mobile is-6-tablet is-3-desktop">
                            <div class="notification is-warning is-light">
                                <p class="heading">Payables</p>
                                <p class="title is-5">{{ number_format($financialMetrics['payables'], 0) }} TZS</p>
                                <p class="is-size-7">Total pending (not paid) from purchases</p>
                            </div>
                        </div>
                        <div class="column is-12-mobile is-6-tablet is-3-desktop">
                            <div class="notification is-info is-light">
                                <p class="heading">Receivables</p>
                                <p class="title is-5">{{ number_format($financialMetrics['receivables'], 0) }} TZS</p>
                                <p class="is-size-7">Pending from Distribution Sales</p>
                            </div>
                        </div>
                        <div class="column is-12-mobile is-6-tablet is-3-desktop">
                            <div class="notification is-success is-light">
                                <p class="heading">Stock in Hand Value</p>
                                <p class="title is-5">{{ number_format($financialMetrics['stock_in_hand_value'], 0) }} TZS</p>
                                <p class="is-size-7">Total value of our stock</p>
                            </div>
                        </div>
                        <div class="column is-12-mobile is-6-tablet is-3-desktop">
                            <div class="notification is-link is-light">
                                <p class="heading">Cash in Hand</p>
                                <p class="title is-5">{{ number_format(isset($paymentOptions) ? $paymentOptions->sum('balance') : $financialMetrics['cash_in_hand'], 0) }} TZS</p>
                                <p class="is-size-7">Total amount in all payment options</p>
                            </div>
                        </div>
                    </div>
                    <div class="columns is-multiline is-variable is-2 mt-4 pt-4" style="border-top: 1px solid #ededed;">
                        <div class="column is-12-mobile is-6-tablet is-3-desktop">
                            <div class="box mb-0">
                                <p class="heading">Total Value</p>
                                <p class="title is-5">{{ number_format($financialMetrics['total_value'], 0) }} TZS</p>
                                <p class="is-size-7 has-text-grey">Receivables + Stock in Hand + Cash in Hand</p>
                            </div>
                        </div>
                        <div class="column is-12-mobile is-6-tablet is-3-desktop">
                            <div class="notification is-success is-light">
                                <p class="heading">Gross Profit</p>
                                <p class="title is-5 has-text-success">{{ number_format($financialMetrics['gross_profit'], 0) }} TZS</p>
                                <p class="is-size-7">Distribution Sales + Agent Sales profit</p>
                            </div>
                        </div>
                        <div class="column is-12-mobile is-6-tablet is-3-desktop">
                            <div class="notification is-danger is-light">
                                <p class="heading">Total Expenses</p>
                                <p class="title is-5 has-text-danger">{{ number_format($financialMetrics['total_expenses'], 0) }} TZS</p>
                                <p class="is-size-7">From Expenses section</p>
                            </div>
                        </div>
                        <div class="column is-12-mobile is-6-tablet is-3-desktop">
                            <div class="notification {{ $financialMetrics['net_profit'] >= 0 ? 'is-success' : 'is-danger' }} is-light">
                                <p class="heading">Net Profit</p>
                                <p class="title is-5 {{ $financialMetrics['net_profit'] >= 0 ? 'has-text-success' : 'has-text-danger' }}">{{ number_format($financialMetrics['net_profit'], 0) }} TZS</p>
                                <p class="is-size-7">Gross profit − Total expenses</p>
                            </div>
                        </div>
                        <div class="column is-12-mobile is-6-tablet is-3-desktop">
                            <div class="notification is-info is-light">
                                <p class="heading">Total Purchase Buy Price</p>
                                <p class="title is-5">{{ number_format($financialMetrics['total_purchase_buy_price'], 0) }} TZS</p>
                                <p class="is-size-7">Total buy price of all purchases</p>
                            </div>
                        </div>
                        <div class="column is-12-mobile is-6-tablet is-3-desktop">
                            <div class="notification is-primary is-light">
                                <p class="heading">Total Products in Purchases</p>
                                <p class="title is-5">{{ number_format($financialMetrics['total_products_in_purchases'], 0) }}</p>
                                <p class="is-size-7">Total products in all purchases</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($paymentOptions) && $paymentOptions->count() > 0)
            <div class="box mt-5 p-0">
                <div class="px-5 py-4" style="border-bottom: 1px solid #ededed;">
                    <h2 class="title is-5 mb-1">Cash in Hand</h2>
                    <p class="is-size-7 has-text-grey">Payment options and their current balances</p>
                </div>
                <div class="p-5">
                    <div class="columns is-multiline is-variable is-2">
                        @foreach($paymentOptions as $option)
                            @php
                                $currentBalance = $option->balance ?? 0;
                                $openingBalance = $option->opening_balance ?? 0;
                                $difference = $currentBalance - $openingBalance;
                                $percentageChange = $openingBalance > 0 ? (($difference / $openingBalance) * 100) : 0;
                                $isIncrease = $difference > 0;
                                $isDecrease = $difference < 0;
                                $typeClass = $option->type === 'mobile' ? 'is-info' : ($option->type === 'bank' ? 'is-success' : 'is-warning');
                            @endphp
                            <div class="column is-12-mobile is-6-tablet is-4-desktop">
                                <div class="box">
                                    <div class="is-flex is-justify-content-space-between is-align-items-center mb-2">
                                        <p class="has-text-weight-semibold">{{ $option->name }}</p>
                                        <span class="tag {{ $typeClass }} is-light">{{ ucfirst($option->type) }}</span>
                                    </div>
                                    <p class="title is-4">{{ number_format($currentBalance, 0) }} TZS</p>
                                    <div class="mt-3 pt-3" style="border-top: 1px solid #ededed;">
                                        <div class="is-flex is-justify-content-space-between is-size-7 mb-1">
                                            <span class="has-text-grey">Opening Balance:</span>
                                            <span class="has-text-weight-semibold">{{ number_format($openingBalance, 0) }} TZS</span>
                                        </div>
                                        @if($difference != 0)
                                            <div class="is-flex is-align-items-center" style="gap: 0.25rem;">
                                                @if($isIncrease)
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 has-text-success" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                                    </svg>
                                                    <span class="is-size-7 has-text-success has-text-weight-semibold">Imepanda {{ number_format(abs($difference), 0) }} TZS ({{ number_format(abs($percentageChange), 1) }}%)</span>
                                                @elseif($isDecrease)
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 has-text-danger" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                                    </svg>
                                                    <span class="is-size-7 has-text-danger has-text-weight-semibold">Imeshuka {{ number_format(abs($difference), 0) }} TZS ({{ number_format(abs($percentageChange), 1) }}%)</span>
                                                @endif
                                            </div>
                                        @else
                                            <p class="is-size-7 has-text-grey">Hakuna mabadiliko</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 pt-4 is-flex is-justify-content-space-between is-align-items-center"
                        style="border-top: 1px solid #ededed;">
                        <span class="has-text-weight-semibold">Total Cash in Hand</span>
                        <span class="title is-5 mb-0">{{ number_format($paymentOptions->sum('balance'), 0) }} TZS</span>
                    </div>
                </div>
            </div>
        @endif

        <div class="box mt-5 p-0">
            <div class="px-5 py-4 is-flex is-flex-wrap is-justify-content-space-between is-align-items-flex-end"
                style="border-bottom: 1px solid #ededed; gap: 1rem;">
                <div>
                    <h2 class="title is-5 mb-1">Top Selling Products (Models)</h2>
                    <p class="is-size-7 has-text-grey">Products sold by quantity</p>
                </div>
                <form method="GET" action="{{ route('admin.dashboard') }}" class="is-flex is-flex-wrap is-align-items-flex-end"
                    style="gap: 0.75rem;">
                    <div class="field mb-0">
                        <label class="label is-size-7" for="start_date">Start Date</label>
                        <div class="control">
                            <input class="input is-small" type="date" name="start_date" id="start_date"
                                value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="field mb-0">
                        <label class="label is-size-7" for="end_date">End Date</label>
                        <div class="control">
                            <input class="input is-small" type="date" name="end_date" id="end_date"
                                value="{{ request('end_date', $endDate->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="field mb-0">
                        <div class="control">
                            <button type="submit" class="button is-brand is-small">Filter</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="p-5">
                @if(count($topProducts) > 0)
                    <canvas id="topProductsChart" style="max-height: 400px;"></canvas>
                @else
                    <p class="has-text-centered has-text-grey py-6">No sales data found for the selected date range.</p>
                @endif
            </div>
        </div>

        <div class="box mt-5 p-0">
            <div class="px-5 py-4 is-flex is-justify-content-space-between is-align-items-center"
                style="border-bottom: 1px solid #ededed;">
                <h2 class="title is-5 mb-0">Recent Orders</h2>
                <a href="{{ route('admin.orders.index') }}" class="is-size-7 has-text-link">View All</a>
            </div>
            <div class="table-container">
                <table class="table is-fullwidth is-striped is-hoverable mb-0">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th class="has-text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            <tr>
                                <td class="has-text-weight-semibold">#{{ $order->id }}</td>
                                <td>{{ $order->user->name ?? 'Guest' }}</td>
                                <td class="has-text-weight-bold">{{ number_format($order->total_price, 0) }} TZS</td>
                                <td>
                                    <span class="tag is-light {{ $order->status == 'completed' ? 'is-success' : 'is-warning' }}">{{ $order->status }}</span>
                                </td>
                                <td class="has-text-right">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="has-text-grey">Details</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="has-text-centered has-text-grey">No recent orders.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

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
