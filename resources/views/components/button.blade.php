@props([
    'type' => 'primary',
    'size' => 'md',  // Default size
    'text' => 'Button',
    'color' => null,     // Optional custom Tailwind background color
])

@php
    // Size options (small, medium, large)
    $sizes = [
        'xs' => 'px-4 py-2 text-xs',
        'sm' => 'px-4 py-2 text-sm',
        'md' => 'px-6 py-3 text-base',
        'lg' => 'px-8 py-4 text-lg',
    ];

    // Select the size class
    $sizeClass = $sizes[$size] ?? $sizes['md'];

    // Define button classes based on type
    if ($type === 'primary') {
        $baseClasses = 'text-white hover:opacity-90 active:opacity-80 border-0';
        // If a custom color is provided, use it, else use default primary color
        $bgClass = $color ? $color : 'bg-blue-500';
        $hoverClass = $color ? "hover:opacity-80" : 'hover:bg-blue-600';
        $activeClass = $color ? "active:opacity-70" : 'active:bg-blue-700';
        $combinedClasses = "$baseClasses $bgClass $hoverClass $activeClass";
    } else {
        // For secondary buttons, apply outline with provided or default color
        $bgClass = 'bg-transparent';  // Always transparent for secondary
        $borderClass = $color ? "border-2 border-$color" : 'border-2 border-blue-500';
        $textClass = $color ? "text-$color" : 'text-blue-500';

        // On hover, fill the background and change text to dark color
        $hoverClass = $color ? "hover:bg-{$color}-500 hover:text-gray-900" : 'hover:bg-blue-500 hover:text-gray-900';
        $activeClass = $color ? "active:bg-{$color}-700 active:border-{$color}-700" : 'active:bg-blue-700 active:border-blue-700';
        $combinedClasses = "$bgClass $borderClass $textClass $hoverClass $activeClass";
    }
@endphp

<button {{ $attributes->merge(['class' => "flex items-center justify-center font-semibold focus:outline-none $combinedClasses $sizeClass rounded transition-all duration-300 transform active:scale-95"]) }}>
    {{ $text }}
</button>
