<x-admin-layout>
    <div class="py-12 px-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">All Payment Receipts</h1>
                <p class="mt-2 text-slate-600">View payment receipts for all purchases.</p>
            </div>
            <a href="{{ route('admin.stock.purchases') }}" class="text-slate-600 hover:text-slate-900">Back to Purchases</a>
        </div>

        @if($purchases->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($purchases as $purchase)
                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition-shadow">
                        <div class="p-4 border-b border-slate-100">
                            <h3 class="font-semibold text-slate-900">{{ $purchase->name ?? 'Purchase #' . $purchase->id }}</h3>
                            <div class="mt-2 text-sm text-slate-600 space-y-1">
                                <p><span class="font-medium">Date:</span> {{ $purchase->date }}</p>
                                <p><span class="font-medium">Product:</span> {{ $purchase->product->name ?? 'N/A' }}</p>
                                <p><span class="font-medium">Distributor:</span> {{ $purchase->distributor_name ?? 'N/A' }}</p>
                                <p><span class="font-medium">Amount:</span> {{ number_format($purchase->paid_amount, 2) }}</p>
                                <p>
                                    <span class="font-medium">Status:</span>
                                    <span class="px-2 py-1 rounded text-xs font-medium 
                                        {{ $purchase->payment_status === 'paid' ? 'bg-green-100 text-green-800' : 
                                           ($purchase->payment_status === 'partial' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                        {{ ucfirst($purchase->payment_status) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="p-4">
                            <a href="{{ asset('storage/' . $purchase->payment_receipt_image) }}" target="_blank" class="block">
                                <img src="{{ asset('storage/' . $purchase->payment_receipt_image) }}" 
                                     alt="Receipt for {{ $purchase->name }}"
                                     class="w-full h-48 object-contain bg-slate-50 rounded border border-slate-200 hover:border-[#fa8900] transition-colors">
                            </a>
                            <div class="mt-3 flex gap-2">
                                <a href="{{ asset('storage/' . $purchase->payment_receipt_image) }}" 
                                   target="_blank"
                                   class="flex-1 text-center px-3 py-2 bg-[#fa8900] text-white rounded-lg hover:bg-[#e67d00] transition-colors text-sm font-medium">
                                    View Full Size
                                </a>
                                <a href="{{ route('admin.stock.edit-purchase', $purchase->id) }}" 
                                   class="flex-1 text-center px-3 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors text-sm font-medium">
                                    Edit Purchase
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-12 text-center">
                <svg class="w-16 h-16 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="text-lg font-medium text-slate-900 mb-2">No Receipts Found</h3>
                <p class="text-slate-600 mb-4">No payment receipts have been uploaded yet.</p>
                <a href="{{ route('admin.stock.create-purchase') }}" class="inline-block bg-[#fa8900] text-white px-4 py-2 rounded-lg hover:bg-[#fa8900]/90 transition-colors font-medium">
                    Create Purchase with Receipt
                </a>
            </div>
        @endif
    </div>
</x-admin-layout>
