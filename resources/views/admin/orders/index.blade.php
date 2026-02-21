<x-admin-layout>
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-slate-800">Orders</h2>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500 font-semibold">
                    <th class="p-4">Order ID</th>
                    <th class="p-4">Customer</th>
                    <th class="p-4">Location</th>
                    <th class="p-4">Total</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Date</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($orders as $order)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="p-4 font-medium text-slate-900">#{{ $order->id }}</td>
                                <td class="p-4">
                                    <div class="font-medium text-slate-900">{{ $order->user->name ?? 'Guest' }}</div>
                                    <div class="text-xs text-slate-500">{{ $order->user->email ?? '-' }}</div>
                                </td>
                                <td class="p-4 text-slate-600">
                                    {{ $order->address->city ?? 'N/A' }}, {{ $order->address->country ?? '' }}
                                </td>
                                <td class="p-4 font-bold text-slate-900">
                                    {{ number_format($order->total_price, 0) }} TZS
                                </td>
                                <td class="p-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                         {{ $order->status === 'delivered' ? 'bg-green-100 text-green-800' :
                    ($order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                        ($order->status === 'processed' ? 'bg-blue-100 text-blue-800' :
                            ($order->status === 'on the way' ? 'bg-indigo-100 text-indigo-800' :
                                ($order->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')))) }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="p-4 text-slate-500">
                                    {{ $order->created_at->format('M d, Y') }}
                                </td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('admin.orders.show', $order) }}"
                                        class="text-[#007185] hover:text-[#c7511f] font-medium hover:underline">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-500">
                            No orders found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($orders->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>