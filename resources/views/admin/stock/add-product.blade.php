<x-admin-layout>
    <div class="py-12 px-8">
        <div class="max-w-2xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Add Product (IMEI)</h1>
                    <p class="mt-2 text-slate-600">Scan IMEI, select stock, select model from stock, then save.</p>
                </div>
                <a href="{{ route('admin.stock.stocks') }}" class="text-slate-600 hover:text-slate-900">Back to Stocks</a>
            </div>

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-md">{{ session('success') }}</div>
            @endif

            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                <form action="{{ route('admin.stock.store-add-product') }}" method="POST" id="add-product-form">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="imei_number" class="block text-sm font-medium text-slate-700 mb-1">IMEI (scan or type)</label>
                            <input type="text" name="imei_number" id="imei_number" value="{{ old('imei_number') }}" required
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Scan barcode or enter IMEI">
                            @error('imei_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="stock_id" class="block text-sm font-medium text-slate-700 mb-1">Stock</label>
                            <select name="stock_id" id="stock_id" required
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select stock</option>
                                @foreach($stocks as $s)
                                    <option value="{{ $s->id }}" {{ old('stock_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                @endforeach
                            </select>
                            @error('stock_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="model" class="block text-sm font-medium text-slate-700 mb-1">Model (from selected stock)</label>
                            <select name="model" id="model" required
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select stock first</option>
                            </select>
                            <input type="hidden" name="category_id" id="category_id" value="{{ old('category_id') }}">
                            @error('model') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            @error('category_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-[#fa8900] text-white px-6 py-2 rounded-lg hover:bg-[#fa8900]/90 font-medium">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('stock_id').addEventListener('change', function() {
            const stockId = this.value;
            const modelSelect = document.getElementById('model');
            const categoryInput = document.getElementById('category_id');
            modelSelect.innerHTML = '<option value="">Loading…</option>';
            categoryInput.value = '';
            if (!stockId) {
                modelSelect.innerHTML = '<option value="">Select stock first</option>';
                return;
            }
            fetch('{{ url("admin/stock/stocks") }}/' + stockId + '/models', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            }).then(r => r.json()).then(data => {
                const list = data.data || [];
                modelSelect.innerHTML = '<option value="">Select model</option>';
                list.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.model;
                    opt.textContent = item.model;
                    opt.dataset.categoryId = item.category_id || '';
                    modelSelect.appendChild(opt);
                });
            }).catch(() => {
                modelSelect.innerHTML = '<option value="">Error loading models</option>';
            });
        });
        document.getElementById('model').addEventListener('change', function() {
            const opt = this.selectedOptions[0];
            document.getElementById('category_id').value = opt && opt.dataset.categoryId ? opt.dataset.categoryId : '';
        });
        @if(old('stock_id'))
            document.getElementById('stock_id').dispatchEvent(new Event('change'));
            setTimeout(function() {
                const modelSelect = document.getElementById('model');
                const m = '{{ old('model') }}';
                if (m && modelSelect.options.length) {
                    for (let i = 0; i < modelSelect.options.length; i++) {
                        if (modelSelect.options[i].value === m) {
                            modelSelect.selectedIndex = i;
                            modelSelect.dispatchEvent(new Event('change'));
                            break;
                        }
                    }
                }
            }, 500);
        @endif
    </script>
</x-admin-layout>
