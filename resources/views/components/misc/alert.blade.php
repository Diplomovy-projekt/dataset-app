@props(['type' => 'success'])

@php
    $colors = [
        'success' => 'bg-green-500 text-white',
        'error' => 'bg-red-500 text-white',
        'warning' => 'bg-yellow-500 text-black',
        'info' => 'bg-blue-500 text-white',
    ];
@endphp

@if (session()->has($type))
    <div class="{{ $colors[$type] }} px-4 py-2 rounded-lg">
        {{ session($type) }}
    </div>
@endif
