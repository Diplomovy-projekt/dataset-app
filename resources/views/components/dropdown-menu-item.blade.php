@props([
    'href' => null,
    'danger' => false,
    'icon' => null
])

@php
    $classes = ' flex items-center w-full px-4 py-2 text-sm leading-5 text-left transition duration-150 ease-in-out ' .
               ($danger
                   ? 'text-red-600 hover:bg-red-400/30'
                   : 'text-gray-300 hover:bg-gray-800') .
               ($href ? ' block' : '');
@endphp
<div class=" relative">
    @if ($href)
        <a wire:navigate href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
            @if ($icon)
                <span class="w-5 h-5 mr-2">
                    {!! $icon !!}
                </span>
            @endif
            {{ $slot }}
        </a>
    @else
        <button {{ $attributes->merge(['class' => $classes]) }}>
            @if ($icon)
                <span class="w-5 h-5 mr-2">
                    {!! $icon !!}
                </span>
            @endif
            {{ $slot }}
        </button>
    @endif
</div>
