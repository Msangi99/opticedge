<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page admin-prod-page--narrow">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-8">
            <div>
                <p class="admin-prod-eyebrow">Inventory</p>
                <h1 class="admin-prod-title">Add product (IMEI)</h1>
                <p class="admin-prod-subtitle">From photos (QR) or paste many codes. Pick stock and model.</p>
            </div>
            <a href="{{ route('admin.stock.stocks') }}" class="admin-prod-back shrink-0">Back to stocks</a>
        </div>

        @if(session('success'))
            <div class="admin-prod-alert admin-prod-alert--success mb-4" role="status">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="admin-prod-alert admin-prod-alert--warning mb-4" role="alert">{{ session('error') }}</div>
        @endif

        <div class="admin-clay-panel admin-prod-form-shell overflow-hidden space-y-0">
            <div class="admin-prod-form-body space-y-6">
                <div class="rounded-xl border border-slate-200/80 bg-slate-50/60 p-4">
                    <h2 class="text-sm font-semibold text-slate-900 mb-2">From barcode photos</h2>
                    <p class="text-xs text-slate-600 mb-3">Choose one or more images containing IMEI barcodes (Code 128, QR, EAN). Codes are read directly in your browser — no upload needed.</p>
                    <input type="file" id="barcode_photos" accept="image/*" multiple class="admin-prod-file">
                    <button type="button" id="btn_decode_photos" class="mt-3 bg-slate-800 text-white text-sm px-4 py-2 rounded-lg hover:bg-slate-700">Read codes from photos</button>
                    <p id="decode_status" class="text-xs text-slate-500 mt-2 min-h-[1rem]"></p>
                </div>

                <form action="{{ route('admin.stock.store-add-product') }}" method="POST" id="add-product-form">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="imei_numbers" class="admin-prod-label">IMEI / serial numbers</label>
                            <p class="text-xs text-slate-500 mb-1">Put <strong>one code per line</strong>, or separate with <strong>spaces</strong>, <strong>commas</strong>, or <strong>semicolons</strong>. Long runs of digits-only text are split every 15 digits (IMEI length) when needed.</p>
                            <textarea name="imei_numbers" id="imei_numbers" rows="8" required
                                class="admin-prod-textarea font-mono text-sm"
                                placeholder="Example:&#10;352123456789012&#10;352123456789013&#10;Or: 352123456789012, 352123456789013&#10;Or from photos: use “Read codes from photos” above">{{ old('imei_numbers') }}</textarea>
                            @error('imei_numbers') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="stock_id" class="admin-prod-label">Stock</label>
                            <select name="stock_id" id="stock_id" required class="admin-prod-select">
                                <option value="">Select stock</option>
                                @foreach($stocks as $s)
                                    <option value="{{ $s->id }}" {{ old('stock_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                @endforeach
                            </select>
                            @error('stock_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="model" class="admin-prod-label">Model (from selected stock)</label>
                            <select name="model" id="model" required class="admin-prod-select">
                                <option value="">Select stock first</option>
                            </select>
                            <input type="hidden" name="category_id" id="category_id" value="{{ old('category_id') }}">
                            @error('model') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            @error('category_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="admin-prod-form-footer !mt-6 !px-0 !border-0 !shadow-none">
                        <button type="submit" class="admin-prod-btn-primary px-8">Save all</button>
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
            var serverDecodeUrl = @json(route('admin.stock.decode-barcodes'));
            var fileInput = document.getElementById('barcode_photos');
            var btn = document.getElementById('btn_decode_photos');
            var statusEl = document.getElementById('decode_status');
            var ta = document.getElementById('imei_numbers');

            /**
             * Merge newly found codes into the textarea, return count added.
             */
            function mergeCodes(codes) {
                var existing = ta.value.replace(/\r\n/g, '\n').split('\n')
                    .map(function(s) { return s.trim(); }).filter(Boolean);
                var seen = {};
                existing.forEach(function(c) { seen[c] = true; });
                var added = [];
                codes.forEach(function(c) {
                    c = c.trim();
                    if (c && !seen[c]) { seen[c] = true; existing.push(c); added.push(c); }
                });
                ta.value = existing.join('\n');
                return added.length;
            }

            /**
             * Decode a single File using the browser-native BarcodeDetector API.
             * Supports Code128, QR, EAN-13, EAN-8, and more in Chrome/Edge 83+.
             * Returns an array of code strings (may be empty).
             */
            async function decodeFileNative(detector, file) {
                return new Promise(function(resolve) {
                    var img = new Image();
                    var url = URL.createObjectURL(file);
                    img.onload = function() {
                        detector.detect(img)
                            .then(function(results) {
                                URL.revokeObjectURL(url);
                                resolve(results.map(function(r) { return r.rawValue || ''; }).filter(Boolean));
                            })
                            .catch(function() { URL.revokeObjectURL(url); resolve([]); });
                    };
                    img.onerror = function() { URL.revokeObjectURL(url); resolve([]); };
                    img.src = url;
                });
            }

            /**
             * Fallback: POST images to server (QR only via PHP GD).
             */
            function decodeViaServer(files, callback) {
                var fd = new FormData();
                for (var i = 0; i < files.length; i++) fd.append('images[]', files[i]);
                if (token) fd.append('_token', token.getAttribute('content'));
                fetch(serverDecodeUrl, {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token ? token.getAttribute('content') : ''
                    },
                    credentials: 'same-origin'
                })
                .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, j: j }; }); })
                .then(function(res) { callback(null, res.j.codes || [], res.j.message); })
                .catch(function(err) { callback(err, [], null); });
            }

            btn.addEventListener('click', async function() {
                var files = fileInput.files;
                if (!files || !files.length) {
                    statusEl.textContent = 'Choose one or more photos first.';
                    return;
                }
                btn.disabled = true;
                statusEl.textContent = 'Reading barcodes…';

                var allCodes = [];

                // ── Native BarcodeDetector (Chrome/Edge, Android Chrome) ──
                if ('BarcodeDetector' in window) {
                    try {
                        var formats = ['code_128', 'qr_code', 'ean_13', 'ean_8', 'code_39', 'codabar', 'itf', 'data_matrix', 'pdf417', 'aztec'];
                        // Only use formats supported by this browser
                        var supported = await BarcodeDetector.getSupportedFormats();
                        var useFormats = formats.filter(function(f) { return supported.includes(f); });
                        if (!useFormats.length) useFormats = supported;
                        var detector = new BarcodeDetector({ formats: useFormats });
                        for (var i = 0; i < files.length; i++) {
                            var found = await decodeFileNative(detector, files[i]);
                            allCodes = allCodes.concat(found);
                        }
                    } catch(e) {
                        // BarcodeDetector may not be available on HTTPS-only or other restriction
                        allCodes = [];
                    }
                }

                if (allCodes.length) {
                    var added = mergeCodes(allCodes);
                    statusEl.textContent = 'Added ' + added + ' code(s) from photos.' +
                        (added < allCodes.length ? ' (some were already in the list.)' : '');
                    btn.disabled = false;
                    return;
                }

                // ── Server fallback (QR via PHP GD) ──────────────────────
                statusEl.textContent = 'Trying server QR decode (fallback)…';
                decodeViaServer(Array.from(files), function(err, codes, msg) {
                    btn.disabled = false;
                    if (err) { statusEl.textContent = 'Network error.'; return; }
                    if (!codes.length) {
                        statusEl.textContent = msg ||
                            'No barcode found. Tip: For IMEI labels (Code 128), use the OpticApp admin "Camera (scan live)" button for reliable results.';
                        return;
                    }
                    var added = mergeCodes(codes);
                    statusEl.textContent = 'Added ' + added + ' code(s) from photos (QR server decode).' +
                        (added < codes.length ? ' (some were already in the list.)' : '');
                });
            });
        })();
    </script>
</x-admin-layout>
