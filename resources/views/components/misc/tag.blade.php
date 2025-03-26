@props([
    'text',
    'color' => 'gray'
])
@php
    $colors = [
        'green' => 'bg-green-500/10 text-green-400',
        'blue' => 'bg-blue-500/10 text-blue-400',
        'amber' => 'bg-amber-500/10 text-amber-400',
        'rose' => 'bg-rose-500/10 text-rose-400',
        'purple' => 'bg-purple-500/10 text-purple-400',
        'red' => 'bg-red-500/10 text-red-400',
        'yellow' => 'bg-yellow-500/10 text-yellow-400',
        'gray' => 'bg-gray-500/10 text-gray-400',
    ];
    $selColor = $colors[$color] ?? $colors['gray'];
@endphp
<div class="{{$selColor}} w-fit flex-shrink-0 grow-0 px-2 py-0.5 rounded-full text-xs whitespace-nowrap">
    {{ $text }}
</div>
