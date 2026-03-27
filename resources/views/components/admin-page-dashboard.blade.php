@props(['label' => 'Summary'])

<div x-data="{ open: true }" {{ $attributes->merge(['class' => 'mb-6 rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden']) }}>
    <button type="button" @click="open = !open"
        class="w-full flex items-center justify-between px-4 py-3 text-left bg-slate-50 hover:bg-slate-100 transition-colors">
        <span class="font-semibold text-slate-800">{{ $label }}</span>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500 transition-transform shrink-0" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>
    <div x-show="open" x-cloak class="border-t border-slate-100 px-4 py-3 text-sm text-slate-600">
        {{ $slot }}
    </div>
</div>
