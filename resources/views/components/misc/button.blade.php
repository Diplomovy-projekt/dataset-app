
@props([
    'color' => 'blue',
    'size' => 'md',
    'type' => 'button',
    'full' => false,
    'variant' => 'secondary',
    'icon' => null
])

@php
    $baseClasses = 'inline-flex items-center justify-center transition-colors disabled:cursor-not-allowed rounded-lg relative';

    $sizes = [
        'sm' => 'px-2.5 py-1.5 text-sm',
        'md' => 'px-3 py-1.5',
        'lg' => 'px-4 py-2 text-lg'
    ];

    $secondaryColors = [
        'blue' => 'bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 disabled:bg-blue-500/10',
        'red' => 'bg-red-500/10 text-red-400 hover:bg-red-500/20 disabled:bg-red-500/10',
        'green' => 'bg-green-500/10 text-green-400 hover:bg-green-500/20 disabled:bg-green-500/10',
        'yellow' => 'bg-yellow-500/10 text-yellow-400 hover:bg-yellow-500/20 disabled:bg-yellow-500/10',
        'purple' => 'bg-purple-500/10 text-purple-400 hover:bg-purple-500/20 disabled:bg-purple-500/10',
        'gray' => 'bg-gray-500/10 text-gray-400 hover:bg-gray-500/20 disabled:bg-gray-500/10'
    ];

    $primaryColors = [
        'blue' => 'bg-blue-500 text-white hover:bg-blue-600 disabled:bg-blue-500/70',
        'red' => 'bg-red-500 text-white hover:bg-red-600 disabled:bg-red-500/70',
        'green' => 'bg-green-500 text-white hover:bg-green-600 disabled:bg-green-500/70',
        'yellow' => 'bg-yellow-500 text-white hover:bg-yellow-600 disabled:bg-yellow-500/70',
        'purple' => 'bg-purple-500 text-white hover:bg-purple-600 disabled:bg-purple-500/70',
        'gray' => 'bg-gray-500 text-white hover:bg-gray-600 disabled:bg-gray-500/70'
    ];

    $colorClasses = $variant === 'primary'
        ? ($primaryColors[$color] ?? $primaryColors['blue'])
        : ($secondaryColors[$color] ?? $secondaryColors['blue']);

    $classes = $baseClasses . ' ' .
               ($sizes[$size] ?? $sizes['md']) . ' ' .
               $colorClasses . ' ' .
               ($full ? 'w-full' : '');

    // Check if we're using Livewire
    $hasLivewireAction = $attributes->wire('click')->value();

    // Check if we have an Alpine click handler
    $hasAlpineClick = collect($attributes->getAttributes())
        ->keys()
        ->contains(fn ($key) => str_starts_with($key, '@click'));
@endphp

<button x-data="{ loading: false }"
    {{ $attributes->merge(['type' => $type, 'class' => $classes]) }}
    @if($hasLivewireAction)
        wire:loading.attr="disabled"
    wire:target="{{ $hasLivewireAction }}"
    @endif
>
    <span
        class="inline-flex items-center gap-2"
        @if($hasLivewireAction)
            wire:loading.class="opacity-0"
        wire:target="{{ $hasLivewireAction }}"
        @endif
        @if($hasAlpineClick)
            x-bind:class="{ 'opacity-0': loading }"
        @endif
    >
        @if ($icon)
            <span class="w-5 h-5 mr-2">
                    {!! $icon !!}
                </span>
        @endif

        {{ $slot }}
    </span>

    <span
        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2"
        @if($hasLivewireAction)
            wire:loading.flex
        wire:target="{{ $hasLivewireAction }}"
        @else
            hidden
        @endif
    >
        <svg class="w-4 h-4 animate-spin text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </span>
</button>
