<x-admin-layout>
    @include('admin.partials.catalog-styles')

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            .admin-branch-transfer-select2 .select2-container { width: 100% !important; }
        </style>
    @endpush

    <div class="admin-prod-page admin-branch-transfer-select2">
        <div class="admin-prod-toolbar">
            <div>
                <p class="admin-prod-eyebrow">Stock</p>
                <h1 class="admin-prod-title">Branch transfer</h1>
                <p class="admin-prod-subtitle">Move unsold devices between branches (sets location on each IMEI).</p>
            </div>
            <a href="{{ route('admin.stock.branch-transfer.logs') }}" class="admin-prod-btn-ghost shrink-0">Transfer history</a>
        </div>

        @if(session('success'))
            <div class="admin-prod-alert admin-prod-alert--success mb-4" role="status">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="admin-prod-alert admin-prod-alert--error mb-4" role="alert">{{ session('error') }}</div>
        @endif

        <div class="admin-clay-panel p-6">
            <form method="POST" action="{{ route('admin.stock.branch-transfer.store') }}" id="branch-transfer-form">
                @csrf
                <div class="space-y-5 max-w-3xl">
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="unassigned" id="unassigned" value="1" class="rounded border-slate-300">
                        <span>Unassigned devices only (no branch on unit or purchase)</span>
                    </label>

                    <div id="from-branch-wrap">
                        <label for="from_branch_id" class="admin-prod-label">Source branch</label>
                        <select id="from_branch_id" name="from_branch_id" class="admin-prod-select w-full">
                            <option value="">Select branch</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ old('from_branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="to_branch_id" class="admin-prod-label">Destination branch</label>
                        <select id="to_branch_id" name="to_branch_id" class="admin-prod-select w-full" required>
                            <option value="">Select branch</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ old('to_branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                        @error('to_branch_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="product_id" class="admin-prod-label">Filter by product (optional)</label>
                        <select id="product_id" name="product_id" class="admin-prod-select w-full">
                            <option value="">All products</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->category->name ?? '—' }} – {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="imei-wrap">
                        <label for="imei_select" class="admin-prod-label">Devices</label>
                        <select id="imei_select" name="product_list_ids[]" multiple="multiple" class="w-full"></select>
                        @error('product_list_ids')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="admin-prod-btn-primary">Move to destination branch</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            (function () {
                const itemsUrl = @json(route('admin.stock.branch-transfer.items'));
                const $un = jQuery('#unassigned');
                const $fromWrap = jQuery('#from-branch-wrap');
                const $from = jQuery('#from_branch_id');
                const $product = jQuery('#product_id');
                const $imei = jQuery('#imei_select');

                function loadImeis() {
                    const un = $un.is(':checked');
                    const branchId = $from.val();
                    const productId = $product.val();
                    if (!un && !branchId) {
                        if ($imei.data('select2')) $imei.select2('destroy');
                        $imei.empty();
                        return;
                    }
                    const params = new URLSearchParams();
                    if (un) params.set('unassigned', '1');
                    else params.set('branch_id', branchId);
                    if (productId) params.set('product_id', productId);
                    fetch(itemsUrl + '?' + params.toString(), {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    })
                        .then(function (r) { return r.json(); })
                        .then(function (json) {
                            const rows = (json && json.data) ? json.data : [];
                            if ($imei.data('select2')) $imei.select2('destroy');
                            $imei.empty();
                            rows.forEach(function (row) {
                                $imei.append(new Option(row.text, row.id, false, false));
                            });
                            $imei.select2({ placeholder: 'Select devices', width: '100%', closeOnSelect: false });
                        });
                }

                $un.on('change', function () {
                    if (this.checked) {
                        $fromWrap.addClass('opacity-50 pointer-events-none');
                        $from.prop('required', false);
                    } else {
                        $fromWrap.removeClass('opacity-50 pointer-events-none');
                        $from.prop('required', true);
                    }
                    loadImeis();
                });
                $from.on('change', loadImeis);
                $product.on('change', loadImeis);

                jQuery(document).ready(function () {
                    if ($un.is(':checked')) {
                        $fromWrap.addClass('opacity-50 pointer-events-none');
                        $from.prop('required', false);
                    } else {
                        $from.prop('required', true);
                    }
                    loadImeis();
                });
            })();
        </script>
    @endpush
</x-admin-layout>
