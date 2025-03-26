@props(['image_stats'])

@php
    // Define default classes
    $defaultClasses = 'border-y border-slate-800 bg-slate-800 rounded-lg w-fit px-4 py-2';

    // If 'class' is passed through attributes, use it; otherwise, use the default.
    $classes = isset($attributes['class']) ? $attributes['class'] : $defaultClasses;
@endphp

{{-- Image Dimensions Section --}}
<div {{ $attributes->merge(['class' => $classes]) }}>
    <div class="flex flex-wrap gap-x-6 gap-y-2">

        <div class="flex flex-col flex-wrap sm:flex-row sm:items-center gap-4">
            <div class="flex flex-col items-center">
                <span class="text-xs text-slate-400">Median</span>
                <span class="text-sm font-medium text-slate-200">{{ $image_stats['median'] ?? 'N/A' }}</span>
            </div>
            <div class="flex flex-col items-center">
                <span class="text-xs text-slate-400">Min</span>
                <span class="text-sm font-medium text-slate-200">{{ $image_stats['min'] ?? 'N/A' }}</span>
            </div>
            <div class="flex flex-col items-center">
                <span class="text-xs text-slate-400">Max</span>
                <span class="text-sm font-medium text-slate-200">{{ $image_stats['max'] ?? 'N/A' }}</span>
            </div>
        </div>
    </div>
</div>
