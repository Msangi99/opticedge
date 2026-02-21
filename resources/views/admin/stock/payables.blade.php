<x-admin-layout>
    <div class="py-12 px-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Payables</h1>
                <p class="mt-2 text-slate-600">Track payable items and amounts.</p>
            </div>
        </div>

        <div class="mt-8 bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Item Name</th>
                        <th class="px-6 py-3">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($payables as $payable)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3">{{ $payable->date }}</td>
                            <td class="px-6 py-3 font-medium">{{ $payable->item_name }}</td>
                            <td class="px-6 py-3 font-bold">{{ number_format($payable->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-slate-500">No payables found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
