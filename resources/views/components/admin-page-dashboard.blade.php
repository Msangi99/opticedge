@props(['label' => 'Summary'])

<div x-data="{ open: true }" {{ $attributes->merge(['class' => 'mb-6 admin-clay-panel overflow-hidden']) }}>
    <button type="button" @click="open = !open"
        class="w-full flex items-center justify-between px-4 py-3 text-left rounded-t-[1.5rem] bg-gradient-to-r from-white/40 to-slate-100/30 hover:from-white/60 hover:to-slate-100/50 transition-all">
        <span class="font-semibold text-slate-800">{{ $label }}</span>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500 transition-transform shrink-0" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>
    <div x-show="open" x-cloak class="border-t border-white/50 px-4 py-3 text-sm text-slate-600">
        {{ $slot }}
    </div>
</div>
