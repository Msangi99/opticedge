<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page">
        <div class="admin-prod-toolbar !mb-6 flex-wrap gap-y-4">
            <div class="flex items-center gap-4 min-w-0">
                <a href="{{ route('admin.orders.index') }}" class="admin-prod-back shrink-0 !mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Orders
                </a>
                <div>
                    <p class="admin-prod-eyebrow">Storefront</p>
                    <h1 class="admin-prod-title">Order #{{ $order->id }}</h1>
                </div>
            </div>

            <form action="{{ route('admin.orders.update', $order) }}" method="POST"
                class="flex flex-wrap items-center gap-2 shrink-0">
                @csrf
                @method('PUT')
                <select name="status" class="admin-prod-select text-sm py-2 w-auto min-w-[9rem]">
                    <option value="pending" @selected($order->status == 'pending')>Pending</option>
                    <option value="processed" @selected($order->status == 'processed')>Processed</option>
                    <option value="on the way" @selected($order->status == 'on the way')>On the way</option>
                    <option value="delivered" @selected($order->status == 'delivered')>Delivered</option>
                    <option value="cancelled" @selected($order->status == 'cancelled')>Cancelled</option>
                </select>
                <select name="payment_option_id" class="admin-prod-select text-sm py-2 w-auto min-w-[10rem]">
                    <option value="">Select channel</option>
                    @foreach($paymentOptions as $option)
                        <option value="{{ $option->id }}" @selected($order->payment_option_id == $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-lg bg-[#232f3e] px-4 py-2 text-sm font-semibold text-white hover:bg-[#37475a]">
                    Update
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <div class="admin-clay-panel overflow-hidden">
                    <div class="admin-prod-form-head">
                        <h2 class="admin-prod-form-title">Order items</h2>
                    </div>
                    <div class="admin-prod-form-body !pt-4">
                        <ul class="divide-y divide-slate-100/80">
                            @foreach($order->items as $item)
                                <li class="py-4 flex items-start gap-4">
                                    <div class="w-16 h-16 bg-slate-100/80 rounded-lg flex-shrink-0 overflow-hidden border border-white/80">
                                        @php
                                            $images = is_string($item->product->images) ? json_decode($item->product->images, true) : $item->product->images;
                                            $img = !empty($images) ? Storage::url($images[0]) : '';
                                        @endphp
                                        @if($img)
                                            <img src="{{ $img }}" class="w-full h-full object-cover" alt="">
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-semibold text-[#232f3e]">{{ $item->product->name }}</h4>
                                        <p class="text-sm text-slate-500">Qty: {{ $item->quantity }}</p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="font-bold text-slate-900 font-variant-numeric">
                                            {{ number_format($item->price * $item->quantity, 0) }} TZS</p>
                                        <p class="text-xs text-slate-500 font-variant-numeric">{{ number_format($item->price, 0) }} / each</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-6 pt-6 border-t border-slate-200/60 flex justify-between items-center">
                            <span class="font-semibold text-slate-600">Total</span>
                            <span class="text-2xl font-bold text-[#fa8900] font-variant-numeric">{{ number_format($order->total_price, 0) }} TZS</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="admin-clay-panel overflow-hidden">
                    <div class="admin-prod-form-head">
                        <h2 class="admin-prod-form-title">Customer</h2>
                    </div>
                    <div class="admin-prod-form-body !pt-4">
                        <div class="flex items-center gap-3">
                            <div class="admin-prod-avatar w-11 h-11 text-base">
                                {{ strtoupper(substr($order->user->name ?? 'G', 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-[#232f3e]">{{ $order->user->name ?? 'Guest' }}</p>
                                <p class="text-sm text-slate-500">{{ $order->user->email ?? 'No email' }}</p>
                                <span class="admin-prod-role-pill admin-prod-role-pill--customer mt-1">{{ $order->user->role ?? 'customer' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="admin-clay-panel overflow-hidden">
                    <div class="admin-prod-form-head">
                        <h2 class="admin-prod-form-title">Shipping / location</h2>
                    </div>
                    <div class="admin-prod-form-body text-sm text-slate-600 space-y-2 !pt-4">
                        @if($order->address)
                            <p><span class="font-semibold text-slate-800">Name:</span> {{ $order->address->first_name }}
                                {{ $order->address->last_name }}</p>
                            <p><span class="font-semibold text-slate-800">Phone:</span> {{ $order->address->phone_number }}</p>
                            <p><span class="font-semibold text-slate-800">Address:</span> {{ $order->address->street_address }}</p>
                            <p><span class="font-semibold text-slate-800">City / state:</span> {{ $order->address->city }},
                                {{ $order->address->state }}</p>
                            <p><span class="font-semibold text-slate-800">Country:</span> {{ $order->address->country }}</p>
                            @if($order->address->latitude && $order->address->longitude)
                                <div class="mt-4 pt-4 border-t border-slate-200/60">
                                    <p class="font-semibold text-slate-800 mb-2">Map</p>
                                    <a href="https://maps.google.com/?q={{ $order->address->latitude }},{{ $order->address->longitude }}"
                                        target="_blank" rel="noopener noreferrer" class="admin-prod-link inline-flex items-center gap-1">
                                        View on map
                                    </a>
                                </div>
                            @endif
                        @else
                            <p class="italic text-slate-400">No address on file.</p>
                        @endif
                    </div>
                </div>

                <div class="admin-clay-panel overflow-hidden">
                    <div class="admin-prod-form-head">
                        <h2 class="admin-prod-form-title">Payment</h2>
                    </div>
                    <div class="admin-prod-form-body text-sm !pt-4 space-y-2">
                        <div class="flex justify-between py-1.5">
                            <span class="text-slate-600">Method</span>
                            <span class="font-medium text-slate-900 uppercase">{{ $order->payment_method ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between py-1.5">
                            <span class="text-slate-600">Channel</span>
                            <span class="font-medium text-slate-900">{{ $order->paymentOption?->name ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between py-1.5">
                            <span class="text-slate-600">Status</span>
                            <span class="font-medium {{ $order->payment_status == 'paid' ? 'text-green-600' : 'text-amber-600' }}">
                                {{ ucfirst($order->payment_status ?? 'pending') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
