@props(
    [
        'image'
    ]
)
<div class="relative">
    <img src="{{asset($image['path'])}}"
         alt="{{ $image['filename'] }}"
         class="h-full w-full object-cover group-hover:opacity-80 transition-opacity duration-300">
    <x-annotation-overlay :image="$image"></x-annotation-overlay>
</div>
