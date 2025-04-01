@props(
    [
        'inputAction' => 'add',
    ]
)
<div id="paginatedImages" class="relative">

    {{-- Loading indicator for switching pages --}}
    <x-misc.pagination-loading/>

    <div x-data="{
            open: ''
         }"
         class="grid sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6 justify-items-center">
        @foreach ($this->paginatedImages as $image)
            <div wire:key="builder-img-grid-container-{{$image['filename']}}"
                 x-data="{ imageId: {{ $image['id'] }} }"
                 @mouseenter="hoveredImageIndex = {{ $image['id'] }}"
                 @mouseleave="hoveredImageIndex = null"
                 class="">
                <div class="relative w-full group">
                    <x-images.annotated-image :image="$image"
                                              class="h-36 w-36"
                                              @click="$dispatch('open-full-screen-image', {
                                                src: $event.target.src,
                                                overlayId: `svg-{{ $image['filename'] }}`
                                                })"/>
                    <input
                        type="checkbox"
                        value="{{ $image['id'] }}"
                        wire:model="selectedImages"
                        class="peer appearance-none absolute top-1 right-1 w-5 h-5 rounded-sm  bg-gray-500 border-gray-400 border-2 opacity-0
                                group-hover:opacity-100 checked:opacity-100 transition-opacity cursor-pointer {{ $inputAction === 'add'
                                ? 'checked:bg-blue-500 checked:border-blue-600'
                                : 'checked:bg-red-500 checked:border-red-600'}}"
                    />
                    <div class="absolute top-1 right-1 w-5 h-5 opacity-0 group-hover:opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none">
                        @if($inputAction === 'add')
                            {{--Checkmark SVG--}}
                            <svg class="w-5 h-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                            </svg>
                        @else
                            {{--X mark SVG--}}
                            <svg class="w-5 h-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        @endif
                    </div>
                </div>
                <x-misc.tooltip :filename="$image['filename']" />
            </div>
        @endforeach
    </div>

    @if ($this->paginatedImages instanceof \Illuminate\Pagination\Paginator || $this->paginatedImages instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="flex items-center justify-between my-4 gap-3">
            <div class="flex items-center space-x-3">
                <label for="per-page" class="text-sm font-medium text-slate-400">Per page:</label>
                <div class="relative">
                    <select
                        id="per-page"
                        wire:model.live="perPage"
                        class="appearance-none bg-slate-800 border border-slate-700 text-slate-200 rounded-lg text-sm py-2 pl-3 pr-10 focus:ring-2 focus:ring-blue-500 focus:border-blue-600 transition-colors duration-200"
                    >
                        @foreach(App\Configs\AppConfig::PER_PAGE_OPTIONS as $key => $option)
                            <option value="{{ $key }}">{{ $option }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="flex-1 mt-3 overflow-x-auto">
                <div class="inline-block min-w-full">
                    {{ $this->paginatedImages->links(data: ['scrollTo' => '#paginatedImages']) }}
                </div>
            </div>
        </div>
    @endif
    {{--<x-images.full-screen-image/>--}}
</div>
