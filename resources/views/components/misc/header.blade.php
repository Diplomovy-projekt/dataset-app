@props([
    'title' => '',
    'info' => '',
    'align' => 'left',
    'font' => 'text-2xl'
])

<div class="mb-6">
    @if ($align === 'left')
        <div class="flex items-center justify-between">
            <h1 class="{{$font}} font-bold text-gray-200">{{$title}}</h1>
            <div class="h-px flex-1 bg-gradient-to-r from-transparent via-slate-700 to-transparent ml-6"></div>
        </div>
    @elseif ($align === 'right')
        <div class="flex items-center justify-between">
            <div class="h-px flex-1 bg-gradient-to-l from-transparent via-slate-700 to-transparent mr-6"></div>
            <h1 class="{{$font}} font-bold text-gray-200">{{$title}}</h1>
        </div>
    @else
        <div class="flex items-center">
            <div class="h-px flex-1 bg-gradient-to-r from-transparent via-slate-700 to-transparent"></div>
            <h1 class="{{$font}} font-bold text-gray-200 mx-6">{{$title}}</h1>
            <div class="h-px flex-1 bg-gradient-to-l from-transparent via-slate-700 to-transparent"></div>
        </div>
    @endif
    <p class="mt-1 text-sm text-gray-400 {{ $align === 'center' ? 'text-center' : ($align === 'right' ? 'text-right' : '') }}">{{ $info }}</p>
</div>
