<x-admin-layout>
    <div class="mb-6 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.orders.index') }}" class="text-slate-500 hover:text-slate-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h2 class="text-2xl font-bold text-slate-800">Order #{{ $order->id }}</h2>
        </div>

        <!-- Status Update Form -->
        <form action="{{ route('admin.orders.update', $order) }}" method="POST" class="flex items-center gap-2">
            @csrf
            @method('PUT')
            <select name="status"
                class="rounded-md border-slate-300 shadow-sm focus:border-[#fa8900] focus:ring-[#fa8900] text-sm">
                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="processed" {{ $order->status == 'processed' ? 'selected' : '' }}>Processed</option>
                <option value="on the way" {{ $order->status == 'on the way' ? 'selected' : '' }}>On the way</option>
                <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <button type="submit"
                class="bg-[#232f3e] hover:bg-[#37475a] text-white px-4 py-2 rounded text-sm font-medium transition-colors">
                Update Status
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Items -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                    <h3 class="font-bold text-slate-800">Order Items</h3>
                </div>
                <div class="p-6">
                    <ul class="divide-y divide-slate-100">
                        @foreach($order->items as $item)
                            <li class="py-4 flex items-start gap-4">
                                <div class="w-16 h-16 bg-slate-100 rounded flex-shrink-0 overflow-hidden">
                                    @php
                                        $images = is_string($item->product->images) ? json_decode($item->product->images, true) : $item->product->images;
                                        $img = !empty($images) ? Storage::url($images[0]) : '';
                                    @endphp
                                    @if($img)
                                        <img src="{{ $img }}" class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-medium text-slate-900">{{ $item->product->name }}</h4>
                                    <p class="text-sm text-slate-500">Qty: {{ $item->quantity }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-slate-900">
                                        {{ number_format($item->price * $item->quantity, 0) }} TZS</p>
                                    <p class="text-xs text-slate-500">{{ number_format($item->price, 0) }} / each</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-6 pt-6 border-t border-slate-100 flex justify-between items-center">
                        <span class="font-medium text-slate-600">Total Amount</span>
                        <span class="text-2xl font-bold text-[#fa8900]">{{ number_format($order->total_price, 0) }}
                            TZS</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer & Additional Info -->
        <div class="space-y-6">
            <!-- Customer Details -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                    <h3 class="font-bold text-slate-800">Customer Details</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 font-bold">
                            {{ substr($order->user->name ?? 'G', 0, 1) }}
                        </div>
                        <div>
                            <p class="font-medium text-slate-900">{{ $order->user->name ?? 'Guest' }}</p>
                            <p class="text-sm text-slate-500">{{ $order->user->email ?? 'No email' }}</p>
                            <span
                                class="inline-block mt-1 text-[10px] font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-800 uppercase">
                                {{ $order->user->role ?? 'customer' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                    <h3 class="font-bold text-slate-800">Shipping Address / Location</h3>
                </div>
                <div class="p-6 text-sm text-slate-600 space-y-2">
                    @if($order->address)
                        <p><span class="font-semibold text-slate-900">Name:</span> {{ $order->address->first_name }}
                            {{ $order->address->last_name }}</p>
                        <p><span class="font-semibold text-slate-900">Phone:</span> {{ $order->address->phone_number }}</p>
                        <p><span class="font-semibold text-slate-900">Address:</span> {{ $order->address->street_address }}
                        </p>
                        <p><span class="font-semibold text-slate-900">City/State:</span> {{ $order->address->city }},
                            {{ $order->address->state }}</p>
                        <p><span class="font-semibold text-slate-900">Country:</span> {{ $order->address->country }}</p>
                        @if($order->address->latitude && $order->address->longitude)
                            <div class="mt-4 pt-4 border-t border-slate-100">
                                <p class="font-semibold text-slate-900 mb-2">Map Coordinates:</p>
                                <a href="https://maps.google.com/?q={{ $order->address->latitude }},{{ $order->address->longitude }}"
                                    target="_blank" class="text-blue-600 hover:underline flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    View on Map
                                </a>
                            </div>
                        @endif
                    @else
                        <p class="italic text-slate-400">No address information available.</p>
                    @endif
                </div>
            </div>

            <!-- Payment Info -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                    <h3 class="font-bold text-slate-800">Payment Information</h3>
                </div>
                <div class="p-6 text-sm">
                    <div class="flex justify-between py-2">
                        <span class="text-slate-600">Method</span>
                        <span class="font-medium text-slate-900 uppercase">{{ $order->payment_method ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-slate-600">Status</span>
                        <span
                            class="font-medium {{ $order->payment_status == 'paid' ? 'text-green-600' : 'text-yellow-600' }}">
                            {{ ucfirst($order->payment_status ?? 'pending') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>