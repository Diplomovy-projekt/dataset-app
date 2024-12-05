@props([
    'size' => "small",    // Default for large screens
])

@php
    // Initialize variables to avoid undefined variable errors
    $cols1 = $colsSm = $colsMd = $colsLg = $colsXl = 6;  // Default values

    switch($size) {
        case "small":
            $cols1 = 1;
            $colsSm = 2;
            $colsMd = 3;
            $colsLg = 4;
            $colsXl = 5;
            break;
        case "medium":
            $cols1 = 2;
            $colsSm = 3;
            $colsMd = 4;
            $colsLg = 5;
            $colsXl = 6;
            break;
        case "large":
            $cols1 = 3;
            $colsSm = 4;
            $colsMd = 5;
            $colsLg = 6;
            $colsXl = 9;
            break;
    }

    $gridClasses = "grid grid-cols-{$cols1} sm:grid-cols-{$colsSm} md:grid-cols-{$colsMd} lg:grid-cols-{$colsLg} xl:grid-cols-{$colsXl} gap-6 p-4";
@endphp

<div {{ $attributes->merge(['class' => $gridClasses]) }}>
    {{ $slot }}
</div>
