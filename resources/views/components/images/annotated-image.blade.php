@props([
    'image',
    'dataset' => null,
    'folder' => 'thumbnails',
    'filename' => null,
])


<div {{ $attributes->merge(['class' => 'relative']) }}>
    <x-images.img
        dataset="{{ $image['dataset_folder'] }}"
        folder="{{ $folder }}"
        filename="{{ $image['filename'] }}"
        id="annotated-img-{{ $image['id'] }}"
        fetchpriority="high"
        class="h-full w-full object-cover group-hover:opacity-80 transition-opacity duration-300"
    ></x-images.img>

    <x-annotation-overlay :image="$image"></x-annotation-overlay>
</div>
