<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-slate-900 mb-8">Checkout</h1>

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6" x-data="{ show: true }" x-show="show">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-red-800">
                            {{ session('error') }}
                        </p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button @click="show = false"
                            class="inline-flex rounded-md bg-red-50 p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 focus:ring-offset-red-50">
                            <span class="sr-only">Dismiss</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6" x-data="{ show: true }" x-show="show">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-green-800">
                            {{ session('success') }}
                        </p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button @click="show = false"
                            class="inline-flex rounded-md bg-green-50 p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2 focus:ring-offset-green-50">
                            <span class="sr-only">Dismiss</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-8">
                <form id="checkout-form" action="{{ route('checkout.store') }}" method="POST">
                    @csrf

                    <div class="bg-white shadow-sm sm:rounded-lg border border-slate-200 p-6 mb-6">
                        <h2 class="text-lg font-medium text-slate-900 mb-4">1. Shipping Address</h2>

                        @if($addresses->isEmpty())
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            You need to add a shipping address before you can checkout.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach($addresses as $address)
                                    <div
                                        class="relative flex items-start p-4 border rounded-lg hover:bg-slate-50 cursor-pointer">
                                        <div class="flex items-center h-5">
                                            <input id="address-{{ $address->id }}" name="address_id" type="radio"
                                                value="{{ $address->id }}" {{ $loop->first ? 'checked' : '' }}
                                                class="focus:ring-[#fa8900] h-4 w-4 text-[#fa8900] border-gray-300">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="address-{{ $address->id }}" class="font-medium text-slate-900 block">
                                                {{ $address->type }}
                                                @if($address->is_default)
                                                    <span
                                                        class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Default</span>
                                                @endif
                                            </label>
                                            <span class="block text-slate-500">{{ $address->address }}, {{ $address->city }},
                                                {{ $address->state }} {{ $address->zip }}</span>
                                            <span class="block text-slate-500">{{ $address->country }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-6">
                            <a href="{{ route('addresses.create') }}"
                                class="inline-flex items-center text-sm font-medium text-[#fa8900] hover:text-orange-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Add a new address (with map location)
                            </a>
                        </div>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg border border-slate-200 p-6 mb-6"
                        x-data="{ paymentMethod: 'cod' }">
                        <h2 class="text-lg font-medium text-slate-900 mb-4">2. Payment Method</h2>

                        <div class="space-y-4">
                            <!-- COD Option -->
                            <div class="flex items-center p-4 border rounded-lg hover:bg-slate-50 cursor-pointer"
                                :class="{ 'bg-orange-50 ring-1 ring-[#fa8900] border-[#fa8900]': paymentMethod === 'cod' }">
                                <input id="payment-cod" name="payment_method" type="radio" value="cod"
                                    x-model="paymentMethod"
                                    class="focus:ring-[#fa8900] h-4 w-4 text-[#fa8900] border-gray-300">
                                <label for="payment-cod"
                                    class="ml-3 block text-sm font-medium text-slate-900 w-full cursor-pointer">
                                    Cash on Delivery (COD)
                                </label>
                            </div>

                            <!-- Selcom Option -->
                            <div class="flex items-start p-4 border rounded-lg hover:bg-slate-50 cursor-pointer"
                                :class="{ 'bg-orange-50 ring-1 ring-[#fa8900] border-[#fa8900]': paymentMethod === 'selcom' }">
                                <div class="flex items-center h-5">
                                    <input id="payment-selcom" name="payment_method" type="radio" value="selcom"
                                        x-model="paymentMethod"
                                        class="focus:ring-[#fa8900] h-4 w-4 text-[#fa8900] border-gray-300">
                                </div>
                                <div class="ml-3 text-sm w-full">
                                    <label for="payment-selcom" class="font-medium text-slate-900 block cursor-pointer">
                                        Selcom / Mobile Money
                                    </label>
                                    <p class="text-slate-500">Pay securely with M-Pesa, Tigo Pesa, Airtel Money, or
                                        HaloPesa.</p>

                                    <!-- Phone Number Input (Only shown if Selcom is selected) -->
                                    <div x-show="paymentMethod === 'selcom'" x-transition class="mt-4">
                                        <label for="payment_phone"
                                            class="block text-sm font-medium text-slate-700">Phone Number for
                                            Payment</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">
                                                    +255
                                                </span>
                                            </div>
                                            <input type="text" name="payment_phone" id="payment_phone"
                                                class="focus:ring-[#fa8900] focus:border-[#fa8900] block w-full pl-12 sm:text-sm border-gray-300 rounded-md"
                                                placeholder="7XXXXXXXX">
                                        </div>
                                        <p class="mt-2 text-xs text-slate-500">Enter the number you want to pay with
                                            (format: 7XXXXXXXX).</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="lg:col-span-4">
                <div class="bg-white shadow-sm sm:rounded-lg border border-slate-200 p-6 sticky top-4">
                    <h2 class="text-lg font-medium text-slate-900 mb-4">Order Summary</h2>

                    @php
                        $subtotal = $cart->items->sum(function ($item) {
                            return $item->product->price * $item->quantity;
                        });
                        $tax = $subtotal * 0.18;
                        $total = $subtotal + $tax;
                    @endphp

                    <div class="flow-root">
                        <dl class="-my-4 text-sm divide-y divide-slate-200">
                            @foreach($cart->items as $item)
                                <div class="py-4 flex items-center justify-between">
                                    <dt class="text-slate-600 w-2/3 truncate">{{ $item->product->name }}
                                        (x{{ $item->quantity }})</dt>
                                    <dd class="font-medium text-slate-900">TZS
                                        {{ number_format($item->product->price * $item->quantity, 0) }}
                                    </dd>
                                </div>
                            @endforeach

                            <div class="py-4 flex items-center justify-between border-t border-slate-200 mt-4">
                                <dt class="text-slate-600">Subtotal</dt>
                                <dd class="font-medium text-slate-900">TZS {{ number_format($subtotal, 0) }}</dd>
                            </div>
                            <div class="py-4 flex items-center justify-between">
                                <dt class="text-slate-600">Tax estimate</dt>
                                <dd class="font-medium text-slate-900">TZS {{ number_format($tax, 0) }}</dd>
                            </div>
                            <div class="py-4 flex items-center justify-between">
                                <dt class="text-base font-medium text-slate-900">Order total</dt>
                                <dd class="text-base font-bold text-[#fa8900]">TZS {{ number_format($total, 0) }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="mt-6">
                        <button type="submit" form="checkout-form" @if($addresses->isEmpty()) disabled @endif
                            class="w-full bg-[#fa8900] border border-transparent rounded-full shadow-sm py-3 px-4 text-base font-medium text-white hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#fa8900] shadow-md transition-transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                            Place Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>