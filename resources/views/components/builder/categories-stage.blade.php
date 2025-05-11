<div class="flex flex-wrap gap-5">
    @forelse($this->categories as $category)
        <div wire:key="category-stage-{{ $category['id'] }}" class="flex flex-col w-fit min-w-52">
            {{--Category Card--}}
            <label class="relative block bg-gray-700 w-full h-40 rounded-t-lg overflow-hidden shadow-md
                      cursor-pointer group">
                <div class="absolute z-20 top-2 right-2 h-5 w-5">
                    <input type="checkbox"
                           value="{{$category['id']}}"
                           wire:model="selectedCategories"
                           class="peer h-5 w-5 cursor-pointer appearance-none rounded-lg border border-blue-500 checked:bg-blue-500 checked:border-blue-600" />

                    <!-- Fade-in SVG -->
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="absolute h-3.5 w-3.5 text-white left-1/2 top-1/2 opacity-0 transform -translate-x-1/2 -translate-y-1/2 transition-opacity duration-300 ease-in-out peer-checked:opacity-100"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="3"
                         stroke-linecap="round"
                         stroke-linejoin="round">
                        <polyline points="4 12 10 18 20 6" />
                    </svg>
                </div>

                {{--Image--}}
                @if(empty($category['image']))
                    <img src="{{ asset('placeholder-image.jpg') }}" alt="Placeholder Image"/>
                @else
                    <x-images.annotated-image :image="$category['image']"/>
                @endif


                {{--Category name--}}
                <div class="absolute bottom-0 left-0 right-0 p-2 bg-slate-800 bg-opacity-50">
                    <h3 class="text-lg font-semibold text-gray-200 truncate">
                        {{ $category['name'] }}
                    </h3>
                </div>
            </label>

            {{--Stats Panel for this category--}}
            {{--<x-dataset.dataset-stats :stats="$customStats" class="rounded-t-none text-base p-3 rounded-b-lg" svgSize="w-6 h-6"/>--}}

        </div>
    @empty
        <p class="text-gray-200">No categories found</p>
    @endforelse
</div>
