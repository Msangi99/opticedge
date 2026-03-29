<x-admin-layout>
    @include('admin.partials.catalog-styles')

    <div class="admin-prod-page">
        <div class="admin-prod-toolbar !mb-0">
            <div>
                <p class="admin-prod-eyebrow">Finance</p>
                <h1 class="admin-prod-title">Payables</h1>
                <p class="admin-prod-subtitle">Payable items and amounts.</p>
            </div>
        </div>

        <div class="mt-6 admin-clay-panel overflow-hidden">
            <div class="admin-prod-table-wrap admin-prod-table-wrap--flush overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th scope="col" class="admin-prod-th">Date</th>
                            <th scope="col" class="admin-prod-th">Item name</th>
                            <th scope="col" class="admin-prod-th">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payables as $payable)
                            <tr>
                                <td class="text-slate-600">{{ $payable->date }}</td>
                                <td class="font-medium text-[#232f3e]">{{ $payable->item_name }}</td>
                                <td class="font-bold font-variant-numeric text-slate-800">{{ number_format($payable->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-slate-500 py-10">No payables found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
