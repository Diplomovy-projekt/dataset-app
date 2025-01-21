@props(['filename'])

<div x-data="{ showTooltip: false }"
     class="relative w-36"
     x-on:mouseover="showTooltip = true"
     x-on:mouseleave="showTooltip = false">
    <!-- Text field with truncation -->
    <p class="text-sm w-full truncate">
        {{ $filename }}
    </p>

    <!-- Tooltip for full filename when hovered -->
    <div x-show="showTooltip" x-transition
         class=" text-base whitespace-nowrap absolute z-50 left-1/2 transform -translate-x-1/2 top-full mt-1 bg-slate-700 text-white p-1 rounded shadow-lg w-max max-w-none overflow-visible">
        <span>{{ $filename }}</span>
    </div>
</div>
