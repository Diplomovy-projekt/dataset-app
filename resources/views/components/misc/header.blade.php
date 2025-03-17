@props([
    'title' => '',
    'info' => '',
    'align' => 'left',
    'font' => 'text-2xl',
    'color' => 'default'
])

@php
    $colorMap = [
        'default' => 'from-transparent via-slate-700 to-transparent',
        'yellow' => 'from-transparent via-yellow-500 to-transparent',
        'blue' => 'from-transparent via-blue-500 to-transparent',
        'red' => 'from-transparent via-red-500 to-transparent',
        'green' => 'from-transparent via-green-500 to-transparent',
        'purple' => 'from-transparent via-purple-500 to-transparent',
        'orange' => 'from-transparent via-orange-500 to-transparent',
        'teal' => 'from-transparent via-teal-500 to-transparent',
        'pink' => 'from-transparent via-pink-500 to-transparent',
    ];

    $gradientClass = $colorMap[$color] ?? $colorMap['default'];
@endphp

<div class="mb-6">
    @if ($align === 'left')
        <div class="flex items-center justify-between">
            <h1 class="{{$font}} font-bold text-gray-200">{{$title}}</h1>
            <div class="h-px flex-1 bg-gradient-to-r {{$gradientClass}} ml-6"></div>
        </div>
    @elseif ($align === 'right')
        <div class="flex items-center justify-between">
            <div class="h-px flex-1 bg-gradient-to-l {{$gradientClass}} mr-6"></div>
            <h1 class="{{$font}} font-bold text-gray-200">{{$title}}</h1>
        </div>
    @else
        <div class="flex items-center">
            <div class="h-px flex-1 bg-gradient-to-r {{$gradientClass}}"></div>
            <h1 class="{{$font}} font-bold text-gray-200 mx-6">{{$title}}</h1>
            <div class="h-px flex-1 bg-gradient-to-l {{$gradientClass}}"></div>
        </div>
    @endif
    <p class="mt-1 text-sm text-gray-400 {{ $align === 'center' ? 'text-center' : ($align === 'right' ? 'text-right' : '') }}">{{ $info }}</p>
</div>
