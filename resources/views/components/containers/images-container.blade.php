<div>
    <div x-data="{
            hoveredImageIndex: null,
            open: ''
         }"
         class="grid sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6 justify-items-center">
        @foreach ($images as $image)
            <div wire:key="{{$image['filename']}}"
                 x-data="{ imageId: {{ $image->id }} }"
                 @mouseenter="hoveredImageIndex = {{ $image->id }}"
                 @mouseleave="hoveredImageIndex = null"
                 class="">
                <div class="relative w-full" wire:key="{{$image['filename']}}">
                    <x-images.annotated-image :image="$image" class="h-36 w-36"/>
                    <div
                        x-show="hoveredImageIndex === {{ $image->id }} || selectedImages.includes({{ strval($image->id) }})"
                        class="absolute -top-0 -right-0">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                class="peer sr-only"
                                @change="selectedImages.includes({{ $image->id }})
                                        ? selectedImages = selectedImages.filter(id => id !== {{ $image->id }})
                                        : selectedImages.push({{ $image->id }})"
                                :checked="selectedImages.includes({{ $image->id }})"
                            />
                            <div class="w-5 h-5 bg-black/30 rounded border-2 border-blue-500 peer-checked:bg-blue-500 peer-focus:ring-2 peer-focus:ring-blue-300"></div>
                        </label>
                    </div>
                </div>
                <x-misc.tooltip :filename="$image->filename" />
            </div>
        @endforeach
    </div>

    @if ($this->images instanceof \Illuminate\Pagination\Paginator || $this->images instanceof \Illuminate\Pagination\LengthAwarePaginator)
        {{ $this->images->links() }}
    @endif
    <x-images.full-screen-image/>
</div>
