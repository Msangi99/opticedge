<div class="relative flex-grow mx-2 z-50 hidden lg:block">
    <div
        class="hidden lg:flex flex-grow h-10 rounded-md overflow-hidden ring-2 ring-transparent focus-within:ring-[#fa8900] transition-shadow duration-200">
        <div class="relative group">
            <select wire:model.live="category"
                class="h-full bg-slate-100 text-slate-600 text-xs px-3 pr-6 border-r border-slate-300 focus:outline-none cursor-pointer hover:bg-slate-200 hover:text-slate-900 transition-colors appearance-none text-center min-w-[60px]">
                <option value="All">All</option>
                <option value="Electronics">Electronics</option>
                <option value="Home">Home</option>
                <option value="Fashion">Fashion</option>
            </select>
            <div class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-slate-500">
                <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 20 20">
                    <path
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                </svg>
            </div>
        </div>

        <input type="text" wire:model.live.debounce.300ms="query" placeholder="Search for devices"
            class="flex-grow px-4 text-slate-900 bg-white placeholder:text-slate-500 focus:outline-none text-sm font-medium">

        <button
            class="bg-[#febd69] hover:bg-[#fa8900] text-[#131921] px-6 transition-colors duration-200 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </button>
    </div>

    @if(strlen($query) >= 2)
        <div class="absolute top-full left-0 w-full bg-white border border-gray-200 shadow-lg rounded-b-md mt-1 overflow-hidden z-50">
            @if(count($results) > 0)
                <ul>
                    @foreach($results as $product)
                        <li class="border-b border-gray-100 last:border-0">
                            <a href="{{ route('product.show', $product) }}" class="block px-4 py-2 hover:bg-gray-100 flex items-center gap-3">
                                <div class="w-10 h-10 bg-gray-200 flex-shrink-0 rounded overflow-hidden flex items-center justify-center">
                                     @if(is_array($product->images) && count($product->images) > 0)
                                        <img src="{{ asset('storage/' . $product->images[0]) }}" class="w-full h-full object-cover">
                                     @else
                                        <svg class="w-5 h-5 text-gray-400" fill="none" class="stroke-current" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                     @endif
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                    <div class="text-xs text-[#b12704] font-bold">
                                         @if($product->stock_quantity > 0)
                                            In Stock
                                         @else
                                            Out of Stock
                                         @endif
                                    </div>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="px-4 py-3 text-sm text-gray-500">No results found for "{{ $query }}"</div>
            @endif
        </div>
    @endif
</div>
