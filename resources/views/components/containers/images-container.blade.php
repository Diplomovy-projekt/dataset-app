@props(
    [
        'inputAction' => 'add',
    ]
)
<div>
    <div x-data="{
            open: ''
         }"
         class="grid sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6 justify-items-center">
        @foreach ($this->paginatedImages as $image)
            <div wire:key="builder-img-grid-container-{{$image['filename']}}"
                 x-data="{ imageId: {{ $image->id }} }"
                 @mouseenter="hoveredImageIndex = {{ $image->id }}"
                 @mouseleave="hoveredImageIndex = null"
                 class="">
                <div class="relative w-full group">
                    <x-images.annotated-image :image="$image" class="h-36 w-36"/>
                    <input
                        type="checkbox"
                        value="{{ $image->id }}"
                        wire:model="selectedImages"
                        class="peer appearance-none absolute top-1 right-1 w-5 h-5 rounded-sm  bg-gray-500 border-gray-400 border-2 opacity-0
                                group-hover:opacity-100 checked:opacity-100 transition-opacity cursor-pointer {{ $inputAction === 'add'
                                ? 'checked:bg-blue-500 checked:border-blue-600'
                                : 'checked:bg-red-500 checked:border-red-600'}}"
                    />
                    <div class="absolute top-1 right-1 w-5 h-5 opacity-0 group-hover:opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none">
                        @if($inputAction === 'add')
                            <!-- Checkmark SVG -->
                            <svg class="w-5 h-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                            </svg>
                        @else
                            <!-- X mark SVG -->
                            <svg class="w-5 h-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        @endif
                    </div>
                </div>
                <x-misc.tooltip :filename="$image->filename" />
            </div>
        @endforeach
    </div>

    @if ($this->paginatedImages instanceof \Illuminate\Pagination\Paginator || $this->paginatedImages instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="flex items-center justify-between my-4 gap-3">
            <div class="flex items-center space-x-2">
                <label for="per-page" class="text-sm text-gray-600">Per page:</label>
                <select
                    id="per-page"
                    wire:model.live="perPage"
                    class="border border-gray-300 rounded-md text-sm py-1 px-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    @foreach(App\Configs\AppConfig::PER_PAGE_OPTIONS as $key => $option)
                        <option value="{{ $key }}">{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1">
                {{ $this->paginatedImages->links() }}
            </div>
        </div>
    @endif
    <x-images.full-screen-image/>
</div>
