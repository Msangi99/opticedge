<x-admin-layout>
    <div class="py-12 px-8">
        <div class="max-w-4xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Add New Purchase</h1>
                    <p class="mt-2 text-slate-600">Record a new stock purchase.</p>
                </div>
                <a href="{{ route('admin.stock.purchases') }}" class="text-slate-600 hover:text-slate-900">Back to List</a>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                <form action="{{ route('admin.stock.store-purchase') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if($fromStock)
                        <input type="hidden" name="stock_id" value="{{ $fromStock->id }}">
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if($fromStock)
                            <!-- Stock name (from stock – read-only) -->
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Stock</label>
                                <div class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700 font-medium">{{ $fromStock->name }}</div>
                                <p class="text-xs text-slate-500 mt-1">Category and model from products in this stock (as added in the app). Quantity = stock limit.</p>
                            </div>
                        @endif

                        <!-- Date -->
                        <div class="col-span-1">
                            <label for="date" class="block text-sm font-medium text-slate-700 mb-1">Date of Purchase</label>
                            <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}" required class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Distributor -->
                        <div class="col-span-1">
                            <label for="distributor_name" class="block text-sm font-medium text-slate-700 mb-1">Distributor Name</label>
                            <input list="distributors" name="distributor_name" id="distributor_name" value="{{ old('distributor_name') }}" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Select or type new...">
                            <datalist id="distributors">
                                @foreach($distributors as $distributor)
                                    <option value="{{ $distributor }}">
                                @endforeach
                            </datalist>
                            @error('distributor_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Category: from stock (read-only) or editable -->
                        <div class="col-span-1">
                            <label for="category_id" class="block text-sm font-medium text-slate-700 mb-1">Category</label>
                            @if($fromStock)
                                <div class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700">{{ $fromStock->purchase_category_name ?? '–' }}</div>
                                <input type="hidden" name="category_id" value="{{ $fromStock->purchase_category_id }}">
                            @else
                                <select name="category_id" id="category_id" required class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                            @error('category_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Model: from stock (read-only) or editable -->
                        <div class="col-span-1">
                            <label for="model" class="block text-sm font-medium text-slate-700 mb-1">Model (Product Name)</label>
                            @if($fromStock)
                                <div class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700">{{ $fromStock->purchase_model }}</div>
                                <input type="hidden" name="model" value="{{ $fromStock->purchase_model }}">
                            @else
                                <input type="text" name="model" id="model" value="{{ old('model') }}" required placeholder="Type model name..." class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @endif
                            @error('model') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Quantity: from stock limit (read-only) or editable -->
                        <div class="col-span-1">
                            <label for="quantity" class="block text-sm font-medium text-slate-700 mb-1">Quantity</label>
                            @if($fromStock)
                                <div class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700">{{ $fromStock->purchase_quantity }}</div>
                                <input type="hidden" name="quantity" id="quantity" value="{{ $fromStock->purchase_quantity }}">
                            @else
                                <input type="number" name="quantity" id="quantity" value="{{ old('quantity') }}" required min="1" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" oninput="calculateTotal()">
                            @endif
                            @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Unit Price -->
                        <div class="col-span-1">
                            <label for="unit_price" class="block text-sm font-medium text-slate-700 mb-1">Unit Price</label>
                            <input type="number" step="0.01" name="unit_price" id="unit_price" value="{{ old('unit_price') }}" required min="0" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" oninput="calculateTotal()">
                            @error('unit_price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Sell Price -->
                        <div class="col-span-1">
                            <label for="sell_price" class="block text-sm font-medium text-slate-700 mb-1">Sell Price</label>
                            <input type="number" step="0.01" name="sell_price" id="sell_price" value="{{ old('sell_price') }}" min="0" placeholder="Optional" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('sell_price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Total Value (Read Only) -->
                        <div class="col-span-2">
                            <label for="total_amount" class="block text-sm font-medium text-slate-700 mb-1">Total Purchase Value</label>
                            <input type="text" id="total_amount" readonly class="w-full rounded-md border-slate-300 bg-slate-100 shadow-sm cursor-not-allowed font-bold text-gray-700">
                        </div>

                        <!-- Product Images (for home page & product details) -->
                        <div class="col-span-2">
                            <label for="images" class="block text-sm font-medium text-slate-700 mb-1">Product Images (min 3)</label>
                            <input type="file" name="images[]" id="images" multiple accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-[#fa8900] file:text-white file:font-medium hover:file:bg-[#e67d00]">
                            <p class="text-xs text-slate-500 mt-1">Upload at least 3 images for product cards and product details. Formats: JPG, PNG, GIF, WebP. Max 5MB each.</p>
                            @error('images')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                            @error('images.*')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-span-2 border-t border-slate-100 pt-4 mt-2">
                            <h3 class="text-lg font-medium text-slate-900 mb-4">Payment Details</h3>
                        </div>

                        <!-- Paid Date -->
                        <div class="col-span-1">
                            <label for="paid_date" class="block text-sm font-medium text-slate-700 mb-1">Paid Date</label>
                            <input type="date" name="paid_date" id="paid_date" value="{{ old('paid_date') }}" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Paid Amount -->
                        <div class="col-span-1">
                            <label for="paid_amount" class="block text-sm font-medium text-slate-700 mb-1">Paid Amount</label>
                            <input type="number" step="0.01" name="paid_amount" id="paid_amount" value="{{ old('paid_amount') }}" min="0" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('paid_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Payment Status -->
                        <div class="col-span-2">
                            <label for="payment_status" class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                            <select name="payment_status" id="payment_status" required class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="pending" {{ old('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="partial" {{ old('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                                <option value="paid" {{ old('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            </select>
                            @error('payment_status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-[#fa8900] text-white px-6 py-2 rounded-lg hover:bg-[#fa8900]/90 transition-colors font-medium">
                            Save Purchase
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function calculateTotal() {
            const qty = parseFloat(document.getElementById('quantity')?.value) || 0;
            const price = parseFloat(document.getElementById('unit_price')?.value) || 0;
            const total = qty * price;
            const el = document.getElementById('total_amount');
            if (el) el.value = total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
        document.addEventListener('DOMContentLoaded', calculateTotal);
    </script>
</x-admin-layout>
