<x-admin-layout>
    <div class="py-12 px-8">
        <div class="max-w-2xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Add Product (IMEI)</h1>
                    <p class="mt-2 text-slate-600">Add IMEIs from photos (QR) or type/paste many at once. Pick stock and model, then save.</p>
                </div>
                <a href="{{ route('admin.stock.stocks') }}" class="text-slate-600 hover:text-slate-900">Back to Stocks</a>
            </div>

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-md">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-amber-50 border border-amber-200 text-amber-900 rounded-md">{{ session('error') }}</div>
            @endif

            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 space-y-6">
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <h2 class="text-sm font-semibold text-slate-900 mb-2">From barcode photos</h2>
                    <p class="text-xs text-slate-600 mb-3">Choose one or more images (camera or gallery). QR codes on labels are read here; dense 1D barcodes work best in the OpticApp admin screen.</p>
                    <input type="file" id="barcode_photos" accept="image/*" multiple
                        class="block w-full text-sm text-slate-600 file:mr-3 file:rounded file:border-0 file:bg-[#fa8900] file:px-4 file:py-2 file:text-white file:font-medium hover:file:bg-[#e67d00]">
                    <button type="button" id="btn_decode_photos" class="mt-3 bg-slate-800 text-white text-sm px-4 py-2 rounded-lg hover:bg-slate-700">Read codes from photos</button>
                    <p id="decode_status" class="text-xs text-slate-500 mt-2 min-h-[1rem]"></p>
                </div>

                <form action="{{ route('admin.stock.store-add-product') }}" method="POST" id="add-product-form">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="imei_numbers" class="block text-sm font-medium text-slate-700 mb-1">IMEI numbers (one per line)</label>
                            <textarea name="imei_numbers" id="imei_numbers" rows="6" required
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm"
                                placeholder="Paste or type IMEIs, one per line&#10;Or use “Read codes from photos” above">{{ old('imei_numbers') }}</textarea>
                            @error('imei_numbers') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
                        <button type="submit" class="bg-[#fa8900] text-white px-6 py-2 rounded-lg hover:bg-[#fa8900]/90 font-medium">Save all</button>
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

        (function() {
            var token = document.querySelector('meta[name="csrf-token"]');
            var decodeUrl = @json(route('admin.stock.decode-barcodes'));
            var fileInput = document.getElementById('barcode_photos');
            var btn = document.getElementById('btn_decode_photos');
            var statusEl = document.getElementById('decode_status');
            var ta = document.getElementById('imei_numbers');
            btn.addEventListener('click', function() {
                if (!fileInput.files || !fileInput.files.length) {
                    statusEl.textContent = 'Choose one or more photos first.';
                    return;
                }
                statusEl.textContent = 'Reading…';
                var fd = new FormData();
                for (var i = 0; i < fileInput.files.length; i++) {
                    fd.append('images[]', fileInput.files[i]);
                }
                if (token) fd.append('_token', token.getAttribute('content'));
                fetch(decodeUrl, {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token ? token.getAttribute('content') : ''
                    },
                    credentials: 'same-origin'
                }).then(function(r) { return r.json().then(function(j) { return { ok: r.ok, j: j }; }); })
                .then(function(res) {
                    var codes = res.j.codes || [];
                    if (!res.ok) {
                        statusEl.textContent = res.j.message || 'Decode failed.';
                        return;
                    }
                    if (!codes.length) {
                        statusEl.textContent = res.j.message || 'No codes found.';
                        return;
                    }
                    var existing = ta.value.replace(/\r\n/g, '\n').split('\n').map(function(s) { return s.trim(); }).filter(Boolean);
                    var seen = {};
                    existing.forEach(function(c) { seen[c] = true; });
                    var added = [];
                    codes.forEach(function(c) {
                        if (!seen[c]) { seen[c] = true; existing.push(c); added.push(c); }
                    });
                    ta.value = existing.join('\n');
                    statusEl.textContent = 'Added ' + added.length + ' code(s) from photos.' + (added.length < codes.length ? ' (some were already in the list.)' : '');
                }).catch(function() {
                    statusEl.textContent = 'Network error.';
                });
            });
        })();
    </script>
</x-admin-layout>
