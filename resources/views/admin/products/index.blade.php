<x-admin-layout>
    @include('admin.products.partials.styles')

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <style>
        #admin-products-table_wrapper .dataTables_filter input {
            margin-left: 0.5rem;
            border-radius: 0.5rem;
            border: 1px solid rgb(226 232 240);
            padding: 0.35rem 0.65rem;
            font-size: 0.875rem;
        }

        #admin-products-table_wrapper .dataTables_length select {
            border-radius: 0.5rem;
            border: 1px solid rgb(226 232 240);
            padding: 0.25rem 0.5rem;
            margin: 0 0.35rem;
        }

        #admin-products-table_wrapper .dataTables_info,
        #admin-products-table_wrapper .dataTables_paginate {
            font-size: 0.8125rem;
            padding-top: 0.75rem;
        }
    </style>

    <div class="admin-prod-page">
        <div class="admin-prod-toolbar">
            <div>
                <p class="admin-prod-eyebrow">Catalog</p>
                <h1 class="admin-prod-title">Products</h1>
                <p class="admin-prod-subtitle">Models, media, and quick actions for your inventory.</p>
            </div>
            <a href="{{ route('admin.products.create') }}" class="admin-prod-btn-primary shrink-0">
                Add product
            </a>
        </div>

        @if(session('success'))
            <div class="admin-prod-alert admin-prod-alert--success" role="status">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="admin-prod-alert admin-prod-alert--error" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <div class="admin-clay-panel overflow-hidden">
            <div class="admin-prod-table-wrap admin-prod-table-wrap--flush">
                <table id="admin-products-table" class="display compact stripe" style="width:100%">
                    <thead>
                        <tr>
                            <th scope="col" class="admin-prod-th admin-prod-th--index">ID</th>
                            <th scope="col" class="admin-prod-th">Image</th>
                            <th scope="col" class="admin-prod-th">Name</th>
                            <th scope="col" class="admin-prod-th">Category</th>
                            <th scope="col" class="admin-prod-th admin-prod-th--desc">Description</th>
                            <th scope="col" class="admin-prod-th admin-prod-th--end">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script>
            (function ($) {
                function esc(s) {
                    return $('<div>').text(s == null ? '' : String(s)).html();
                }

                $('#admin-products-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: @json(route('admin.products.datatable')),
                    pageLength: 25,
                    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                    order: [[0, 'desc']],
                    columns: [
                        { data: 'id', className: 'text-slate-500 font-mono text-xs' },
                        {
                            data: 'image_url',
                            orderable: false,
                            searchable: false,
                            render: function (url) {
                                if (!url) {
                                    return '<div class="admin-prod-thumb"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></div>';
                                }
                                return '<div class="admin-prod-thumb"><img src="' + esc(url) + '" alt="" class="w-full h-full object-cover"></div>';
                            }
                        },
                        {
                            data: 'name',
                            render: function (d, t, row) {
                                var u = esc(row.imei_url || '');
                                var n = esc(d);
                                return '<a href="' + u + '" class="admin-prod-link font-semibold text-[#232f3e]">' + n + '</a>';
                            }
                        },
                        { data: 'category_name', render: function (d) { return esc(d); } },
                        {
                            data: 'description_plain',
                            orderable: false,
                            render: function (d) {
                                if (!d) {
                                    return '<span class="text-slate-400">—</span>';
                                }
                                return '<p class="line-clamp-3 text-sm m-0" title="' + esc(d) + '">' + esc(d) + '</p>';
                            }
                        },
                        {
                            data: null,
                            orderable: false,
                            searchable: false,
                            className: 'admin-prod-cell-actions',
                            render: function (_, t, row) {
                                var edit = esc(row.edit_url || '');
                                var del = esc(row.destroy_url || '');
                                var token = esc($('meta[name="csrf-token"]').attr('content') || '');
                                return '<div class="admin-prod-actions">' +
                                    '<a href="' + edit + '" class="admin-prod-link">Edit</a>' +
                                    '<form action="' + del + '" method="POST" class="inline" onsubmit="return confirm(\'Delete this product? This cannot be undone.\');">' +
                                    '<input type="hidden" name="_token" value="' + token + '">' +
                                    '<input type="hidden" name="_method" value="DELETE">' +
                                    '<button type="submit" class="admin-prod-link admin-prod-link--danger bg-transparent border-0 cursor-pointer p-0 font-inherit">Delete</button>' +
                                    '</form></div>';
                            }
                        }
                    ]
                });
            })(jQuery);
        </script>
    @endpush
</x-admin-layout>
