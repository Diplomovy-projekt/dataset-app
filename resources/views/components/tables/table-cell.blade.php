@props(['align' => 'left'])

<td {{ $attributes->merge(['class' => "px-6 py-3 text-$align"]) }}>
    {{ $slot }}
</td>
