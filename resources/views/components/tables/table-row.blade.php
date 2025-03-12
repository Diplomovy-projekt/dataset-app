@props(['id' => null])

<tr {{ $attributes->merge(['class' => 'hover:bg-slate-750 transition-colors']) }}
    @if($id) wire:key="{{ $id }}" @endif>
    {{ $slot }}
</tr>
