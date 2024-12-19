<div x-data="{ hoveredIndex: null }" class="container mx-auto pt-10">
    <div class="flex flex-col gap-2 pb-5">
        <x-search-bar />
        <x-category-dropdown />
    </div>
    <div class="flex flex-wrap gap-10">
        {{-- Display images in a grid container --}}
        @foreach ($this->images as $image)
            <div x-data="{ imageId: {{ $image->id }} }"
                 @mouseenter="hoveredIndex = {{ $image->id }}"
                 @mouseleave="hoveredIndex = null" class="flex flex-col">
                <div class="relative w-36" wire:key="{{$image['img_filename']}}">
                    <div class="absolute w-36 h-36 inset-0 bg-gray-300 animate-pulse"></div>
                    <img id="{{$image['img_filename']}}"
                         class="w-full h-full object-cover opacity-0 transition-opacity duration-500 ease-in-out rounded-md" loading="lazy"
                         src="{{ asset('storage/datasets/'.$this->dataset->unique_name. '/full-images' . $image['img_filename']) }}" alt="Image"
                         onload="this.style.opacity=1; this.previousElementSibling.style.display='none';">
                    <svg wire:ignore id="svg-{{$image['img_filename']}}" width="100%" height="100%" viewBox="0 0 100 100" class="absolute top-0 left-0 w-full h-full pointer-events-none">
                        {{-- Draw annotations --}}
                        @foreach($image['annotations'] as $annotation)
                            @if(isset($annotation->segmentation))
                                <polygon points="{{ $annotation->segmentation }}"
                                         fill="{{ $annotation->class->color['fill'] }}"
                                         stroke="{{ $annotation->class->color['stroke'] }}"
                                         stroke-width="0.7"
                                         closed="true"
                                />
                            @else
                                <rect x="{{ $annotation->x }}"
                                      y="{{ $annotation->y }}"
                                      width="{{ $annotation->width }}"
                                      height="{{ $annotation->height }}"
                                      fill="{{ $annotation->class->color }}"
                                      stroke="{{ $annotation->class->color }}"
                                      stroke-width="0.1"
                                />
                            @endif
                        @endforeach
                    </svg>
                    <div x-show="hoveredIndex === {{ $image->id }}"  class="absolute -top-2 -right-2 ">
                        <x-mary-checkbox  />
                    </div>
                </div>
                <x-mary-popover position="top-start" offset="10">
                    <x-slot:trigger>
                        <div class="w-32 truncate">
                            <p class="text-sm">{{ $image['img_filename'] }}</p>
                        </div>
                    </x-slot:trigger>
                    <x-slot:content class="text-xs">
                        {{ $image['img_filename'] }}
                    </x-slot:content>
                </x-mary-popover>

            </div>
        @endforeach
    </div>
    {{ $this->images->links() }}

</div>
