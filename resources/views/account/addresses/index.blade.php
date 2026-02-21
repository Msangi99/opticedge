<x-account-layout>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Your Addresses</h2>
            <p class="text-sm text-gray-500">Manage your shipping addresses.</p>
        </div>
        <a href="{{ route('addresses.create') }}"
            class="text-sm font-medium text-blue-600 hover:text-blue-500 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Address
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">
                        {{ session('success') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Add Address Card (Big Button) -->
        <a href="{{ route('addresses.create') }}"
            class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-gray-300 rounded-lg hover:border-gray-400 hover:bg-gray-50 transition-colors h-64 text-center cursor-pointer group">
            <div class="mb-2">
                <svg class="mx-auto h-10 w-10 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </div>
            <span class="block text-lg font-medium text-gray-900">Add Address</span>
        </a>

        @foreach($addresses as $address)
            <div
                class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 relative h-64 flex flex-col justify-between">
                <div>
                    @if($address->is_default)
                        <span class="absolute top-4 right-4 bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded">Default</span>
                    @endif
                    <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2 mb-2">{{ $address->type }}</h3>
                    <div class="text-sm text-gray-600 space-y-1">
                        <p class="font-medium text-gray-900">{{ Auth::user()->name }}</p>
                        <p>{{ $address->address }}</p>
                        <p>{{ $address->city }}, {{ $address->state }} {{ $address->zip }}</p>
                        <p>{{ $address->country }}</p>
                    </div>
                </div>

                <div class="mt-4 flex gap-3 text-sm font-medium border-t border-gray-100 pt-4">
                    <a href="{{ route('addresses.edit', $address->id) }}"
                        class="text-blue-600 hover:text-blue-800 hover:underline">Edit</a>
                    <span class="text-gray-300">|</span>
                    <form action="{{ route('addresses.destroy', $address->id) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this address?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-blue-600 hover:text-blue-800 hover:underline">Remove</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</x-account-layout>