@props([
    'title' => '',
    'description' => '',
    'count' => 0,
    'icon' => null,
    'color' => 'default'
])

@php
    // Define color mappings for dark theme background
    $colorMap = [
        'default' => [
            'bg' => 'bg-slate-800',
            'border' => 'border-slate-700',
            'text' => 'text-slate-100',
            'icon' => 'text-slate-400',
            'countBg' => 'bg-slate-700',
            'descText' => 'text-slate-400'
        ],
        'yellow' => [
            'bg' => 'bg-yellow-900/40',
            'border' => 'border-yellow-600',
            'text' => 'text-yellow-300',
            'icon' => 'text-yellow-500',
            'countBg' => 'bg-yellow-800',
            'descText' => 'text-yellow-300/70'
        ],
        'blue' => [
            'bg' => 'bg-blue-900/40',
            'border' => 'border-blue-600',
            'text' => 'text-blue-300',
            'icon' => 'text-blue-500',
            'countBg' => 'bg-blue-800',
            'descText' => 'text-blue-300/70'
        ],
        'red' => [
            'bg' => 'bg-red-900/40',
            'border' => 'border-red-600',
            'text' => 'text-red-300',
            'icon' => 'text-red-500',
            'countBg' => 'bg-red-800',
            'descText' => 'text-red-300/70'
        ],
        'green' => [
            'bg' => 'bg-green-900/40',
            'border' => 'border-green-600',
            'text' => 'text-green-300',
            'icon' => 'text-green-500',
            'countBg' => 'bg-green-800',
            'descText' => 'text-green-300/70'
        ],
        'purple' => [
            'bg' => 'bg-purple-900/40',
            'border' => 'border-purple-600',
            'text' => 'text-purple-300',
            'icon' => 'text-purple-500',
            'countBg' => 'bg-purple-800',
            'descText' => 'text-purple-300/70'
        ],
        'orange' => [
            'bg' => 'bg-orange-900/40',
            'border' => 'border-orange-600',
            'text' => 'text-orange-300',
            'icon' => 'text-orange-500',
            'countBg' => 'bg-orange-800',
            'descText' => 'text-orange-300/70'
        ],
        'teal' => [
            'bg' => 'bg-teal-900/40',
            'border' => 'border-teal-600',
            'text' => 'text-teal-300',
            'icon' => 'text-teal-500',
            'countBg' => 'bg-teal-800',
            'descText' => 'text-teal-300/70'
        ],
        'pink' => [
            'bg' => 'bg-pink-900/40',
            'border' => 'border-pink-600',
            'text' => 'text-pink-300',
            'icon' => 'text-pink-500',
            'countBg' => 'bg-pink-800',
            'descText' => 'text-pink-300/70'
        ]
    ];

    // Get the color scheme from the map or use default if not found
    $colors = $colorMap[$color] ?? $colorMap['default'];
@endphp

<div {{ $attributes->merge(['class' => "w-full rounded-md p-4 flex items-center {$colors['bg']} {$colors['border']} border mb-6 shadow-md"]) }}>
    @if (isset($icon))
        <div class="mr-4 {{ $colors['icon'] }}">
            {{ $icon }}
        </div>
    @endif

    <div class="flex-1">
        <h3 class="font-semibold {{ $colors['text'] }} text-lg">{{ $title }}</h3>
        <p class="{{ $colors['descText'] }} mt-1">{{ $description }}</p>
    </div>

    <div>
        {{$slot}}
    </div>
</div>
