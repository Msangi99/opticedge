<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page admin-prod-form-wide">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-8">
            <div>
                <p class="admin-prod-eyebrow">Dealers</p>
                <h1 class="admin-prod-title">Create distribution sale</h1>
                <p class="admin-prod-subtitle">Record a manual sale to a dealer.</p>
            </div>
            <a href="{{ route('admin.stock.distribution') }}" class="admin-prod-back shrink-0">Back to list</a>
        </div>

        <div class="admin-clay-panel admin-prod-form-shell overflow-hidden">
            <div class="admin-prod-form-head">
                <h2 class="admin-prod-form-title">Sale details</h2>
            </div>
            <form method="POST" action="{{ route('admin.stock.store-distribution') }}" class="admin-prod-form-body space-y-6">
                @csrf

                <div>
                    <label for="date" class="admin-prod-label">Date</label>
                    <input id="date" type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required autofocus class="admin-prod-input">
                    @error('date')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="dealer_id" class="admin-prod-label">Dealer</label>
                    <select id="dealer_id" name="dealer_id" class="admin-prod-select">
                        <option value="">Select dealer</option>
                        @foreach($dealers as $dealer)
                            <option value="{{ $dealer->id }}" @selected(old('dealer_id') == $dealer->id)>
                                {{ $dealer->name }} ({{ $dealer->business_name ?? 'No business name' }})
                            </option>
                        @endforeach
                    </select>
                    @error('dealer_id')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="seller_name" class="admin-prod-label">Seller name</label>
                    <input id="seller_name" type="text" name="seller_name" value="{{ old('seller_name', auth()->user()->name) }}" class="admin-prod-input">
                    @error('seller_name')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="product_id" class="admin-prod-label">Product (stock items)</label>
                    <select id="product_id" name="product_id" required class="admin-prod-select">
                        <option value="">Select product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                                {{ $product->name }} ({{ $product->category->name ?? 'No category' }})
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="quantity_sold" class="admin-prod-label">Quantity sold</label>
                    <input id="quantity_sold" type="number" name="quantity_sold" value="{{ old('quantity_sold') }}" required min="1" class="admin-prod-input">
                    @error('quantity_sold')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="selling_price" class="admin-prod-label">Selling price (unit)</label>
                    <input id="selling_price" type="number" step="0.01" name="selling_price" value="{{ old('selling_price') }}" required min="0" class="admin-prod-input">
                    @error('selling_price')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="paid_amount" class="admin-prod-label">Paid amount</label>
                    <input id="paid_amount" type="number" step="0.01" name="paid_amount" value="{{ old('paid_amount', 0) }}" min="0" class="admin-prod-input">
                    @error('paid_amount')
                        <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="admin-prod-form-footer !mt-0 !pt-0 !border-0 !shadow-none">
                    <a href="{{ route('admin.stock.distribution') }}" class="admin-prod-btn-ghost">Cancel</a>
                    <button type="submit" class="admin-prod-btn-primary px-8">Record sale</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
