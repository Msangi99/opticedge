@props(['label' => 'Summary'])

<div x-data="{ open: true }" {{ $attributes->merge(['class' => 'box mb-5 p-0']) }}>
    <button type="button" @click="open = !open"
        class="button is-fullwidth is-justify-content-space-between has-background-light"
        style="border: none; border-radius: 6px 6px 0 0;">
        <span class="has-text-weight-semibold">{{ $label }}</span>
        <span class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="{ 'rotate-180': open }" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" style="transition: transform 0.2s;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </span>
    </button>
    <div x-show="open" x-cloak class="px-4 pb-4 pt-0 is-size-7 has-text-grey">
        {{ $slot }}
    </div>
</div>
