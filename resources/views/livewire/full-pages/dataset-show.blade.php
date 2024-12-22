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
                         src="{{ asset('storage/datasets/'.$this->dataset->unique_name. '/thumbnails/' . $image['img_filename']) }}" alt="Image"
                         onload="this.style.opacity=1; this.previousElementSibling.style.display='none';">
                    <svg wire:ignore id="svg-{{$image['img_filename']}}" width="100%" height="100%" viewBox="0 0 {{$image->viewDims['width']}} {{$image->viewDims['height']}}" class="absolute top-0 left-0 w-full h-full pointer-events-none">
                        <defs>
                            <mask id="annotation-mask-{{$image['img_filename']}}">
                                <!-- Darken the background (everything will be darkened except the annotations) -->
                                <rect width="100%" height="100%" fill="white" />

                                @foreach($image['annotations'] as $annotation)
                                    @if(isset($annotation->polygonString))
                                        <!-- Keep the area inside annotations visible by making it transparent in the mask -->
                                        <polygon points="{{ $annotation->polygonString }}" fill="black" />
                                    @else
                                        <rect x="{{ $annotation->bbox['x'] }}"
                                              y="{{ $annotation->bbox['y'] }}"
                                              width="{{ $annotation->bbox['width'] }}"
                                              height="{{ $annotation->bbox['height'] }}"
                                              fill="black" />
                                    @endif
                                @endforeach
                            </mask>
                        </defs>

                        <!-- Background black overlay with reduced opacity, with the mask applied to darken the outside of annotations -->
                        <rect width="100%" height="100%" fill="black" fill-opacity="0.6" mask="url(#annotation-mask-{{$image['img_filename']}})" />

                        <!-- Annotations with original fill colors and full opacity for the current image -->
                        @foreach($image['annotations'] as $annotation)
                            @if(isset($annotation->polygonString))
                                <polygon points="{{ $annotation->polygonString }}"
                                         fill="transparent"
                                         stroke="{{ $annotation->class->color['stroke'] }}"
                                         stroke-width="1"
                                         opacity="1"
                                         closed="true"
                                />
                            @else
                                <rect x="{{ $annotation->bbox['x'] }}"
                                      y="{{ $annotation->bbox['y'] }}"
                                      width="{{ $annotation->bbox['width'] }}"
                                      height="{{ $annotation->bbox['height'] }}"
                                      fill="transparent"
                                      stroke="{{ $annotation->class->color['stroke'] }}"
                                      stroke-width="1"
                                      opacity="1"
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
