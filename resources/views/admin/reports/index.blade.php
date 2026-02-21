<x-admin-layout>
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-slate-800">Business Reports</h2>
        <button
            class="bg-white border border-slate-300 text-slate-700 px-4 py-2 rounded shadow-sm text-sm font-medium hover:bg-slate-50">
            Export Data
        </button>
    </div>

    <!-- Overview Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">
            <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider mb-2">Total Sales</h3>
            <p class="text-3xl font-bold text-slate-900">{{ number_format($totalSales, 0) }} TZS</p>
            <div class="mt-2 text-sm text-green-600 font-medium">
                +12% from last month
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">
            <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider mb-2">Total Orders</h3>
            <p class="text-3xl font-bold text-slate-900">{{ number_format($totalOrders) }}</p>
            <div class="mt-2 text-sm text-green-600 font-medium">
                +5% from last month
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">
            <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider mb-2">Total Customers</h3>
            <p class="text-3xl font-bold text-slate-900">{{ number_format($totalCustomers) }}</p>
            <div class="mt-2 text-sm text-slate-500 font-medium">
                Active users
            </div>
        </div>
    </div>

    <!-- Recent Sales Chart (Placeholder) -->
    <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200 mb-8">
        <h3 class="font-bold text-lg text-slate-800 mb-4">Sales Overview (Last 7 Days)</h3>
        <div class="h-64 flex items-end justify-between gap-2">
            @foreach($salesData as $date => $amount)
                @php
                    $max = max($salesData) > 0 ? max($salesData) : 1;
                    $height = ($amount / $max) * 100;
                @endphp
                <div class="flex-1 flex flex-col items-center group">
                    <div class="w-full bg-[#fa8900] rounded-t opacity-80 group-hover:opacity-100 transition-opacity relative"
                        style="height: {{ $height > 0 ? $height : 1 }}%">
                        <div
                            class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">
                            {{ number_format($amount) }} TZS
                        </div>
                    </div>
                    <span
                        class="text-xs text-slate-500 mt-2 rotate-45 origin-left sm:rotate-0">{{ \Carbon\Carbon::parse($date)->format('M d') }}</span>
                </div>
            @endforeach
        </div>
    </div>

</x-admin-layout>