@props(
    [
        'image'
    ]
)
<div {{$attributes->merge(['class' => 'relative'])}}>
    <img id="{{$image['filename']}}"
         src="{{asset($image['path'])}}"
         alt="{{ $image['filename'] }}"
         class="h-full w-full object-cover group-hover:opacity-80 transition-opacity duration-300"
         @click="
                            const imgSrc = $event.target.src.replace('/thumbnails/', '/full-images/');
                            $dispatch('open-full-screen-image', { src: imgSrc, overlayId: `svg-{{ $image['filename'] }}` })">
    <x-annotation-overlay :image="$image"></x-annotation-overlay>
</div>
