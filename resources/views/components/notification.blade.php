@props([
    'type' => 'success', // success, error, warning, info
    'title' => '',
    'message' => '',
    'dismissible' => true,
])

@php
    $bgClass = match($type) {
        'success' => 'bg-green-50 text-green-800 border-green-200',
        'error' => 'bg-red-50 text-red-800 border-red-200',
        'warning' => 'bg-amber-50 text-amber-800 border-amber-200',
        'info' => 'bg-blue-50 text-blue-800 border-blue-200',
        default => 'bg-slate-50 text-slate-800 border-slate-200',
    };
    
    $iconClass = match($type) {
        'success' => '✓',
        'error' => '✕',
        'warning' => '!',
        'info' => 'ⓘ',
        default => '•',
    };
@endphp

<div class="rounded-lg border {{ $bgClass }} px-4 py-3 mb-4" role="alert" x-data="{ show: true }" x-show="show" @if($dismissible) x-transition @endif>
    <div class="flex items-start justify-between">
        <div class="flex items-start gap-3">
            <span class="text-lg font-bold">{{ $iconClass }}</span>
            <div>
                @if($title)
                    <p class="font-semibold">{{ $title }}</p>
                @endif
                <p class="text-sm">{{ $message }}</p>
            </div>
        </div>
        @if($dismissible)
            <button @click="show = false" type="button" class="text-lg font-bold opacity-70 hover:opacity-100">
                ✕
            </button>
        @endif
    </div>
</div>
