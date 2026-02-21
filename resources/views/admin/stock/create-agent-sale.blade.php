<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Agent Sale') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.stock.store-agent-sale') }}" class="space-y-6">
                        @csrf

                        <!-- Date -->
                        <div>
                            <x-input-label for="date" :value="__('Date')" />
                            <x-text-input id="date" class="block mt-1 w-full" type="date" name="date" :value="old('date', date('Y-m-d'))" required autofocus />
                            <x-input-error :messages="$errors->get('date')" class="mt-2" />
                        </div>

                        <!-- Customer Name -->
                        <div>
                            <x-input-label for="customer_name" :value="__('Customer Name')" />
                             <x-text-input id="customer_name" class="block mt-1 w-full" type="text" name="customer_name" :value="old('customer_name')" placeholder="Enter Customer Name" required />
                            <x-input-error :messages="$errors->get('customer_name')" class="mt-2" />
                        </div>

                        <!-- Seller Name -->
                        <div>
                            <x-input-label for="seller_name" :value="__('Seller Name')" />
                            <x-text-input id="seller_name" class="block mt-1 w-full" type="text" name="seller_name" :value="old('seller_name', auth()->user()->name)" />
                            <x-input-error :messages="$errors->get('seller_name')" class="mt-2" />
                        </div>

                        <!-- Product (Fetched from Purchases) -->
                        <div>
                            <x-input-label for="product_id" :value="__('Product (Stock Items)')" />
                            <select id="product_id" name="product_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }} ({{ $product->category->name ?? 'No Category' }})
                                    </option>
                                @endforeach
                            </select>
                             <x-input-error :messages="$errors->get('product_id')" class="mt-2" />
                        </div>

                        <!-- Quantity Sold -->
                        <div>
                            <x-input-label for="quantity_sold" :value="__('Quantity Sold')" />
                            <x-text-input id="quantity_sold" class="block mt-1 w-full" type="number" name="quantity_sold" :value="old('quantity_sold')" required min="1" />
                            <x-input-error :messages="$errors->get('quantity_sold')" class="mt-2" />
                        </div>

                        <!-- Selling Price -->
                        <div>
                            <x-input-label for="selling_price" :value="__('Selling Price (Unit)')" />
                            <x-text-input id="selling_price" class="block mt-1 w-full" type="number" step="0.01" name="selling_price" :value="old('selling_price')" required min="0" />
                            <x-input-error :messages="$errors->get('selling_price')" class="mt-2" />
                        </div>

                         <!-- Paid Amount (Optional for Agent Sales??) -->
                         <!-- Schema for agent_sales has 'balance', 'total_selling_value'. Doesn't explicitly have 'paid_amount' but logic implies keeping track. 
                              Use 'paid_amount' to calculate balance initially. -->
                         <div>
                            <x-input-label for="paid_amount" :value="__('Initial Payment (if any)')" />
                            <x-text-input id="paid_amount" class="block mt-1 w-full" type="number" step="0.01" name="paid_amount" :value="old('paid_amount', 0)" min="0" />
                            <x-input-error :messages="$errors->get('paid_amount')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Record Sale') }}</x-primary-button>
                            <a href="{{ route('admin.stock.agent-sales') }}" class="text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
