<div class="flex flex-wrap gap-5">
    @forelse($this->categories as $category)
        <div wire:key="category-stage-{{ $category['id'] }}" class="flex flex-col w-fit min-w-52">
            <!-- Category Card -->
            <label class="relative block bg-gray-700 w-full h-40 rounded-t-lg overflow-hidden shadow-md
                      cursor-pointer group">
                <!-- Checkbox that covers entire label -->
                <input
                    type="checkbox"
                    value="{{$category['id']}}"
                    wire:model="selectedCategories"
                    class="absolute z-20 top-2 right-2 h-5 w-5
                    accent-blue-400 bg-gray-700 border-gray-600">

                <!-- Image -->
                <x-images.annotated-image :image="$category['image']"/>

                <!-- Category name -->
                <div class="absolute bottom-0 left-0 right-0 p-2 bg-slate-800 bg-opacity-50">
                    <h3 class="text-lg font-semibold text-gray-200 truncate">
                        {{ $category['name'] }}
                    </h3>
                </div>
            </label>

            <!-- Stats Panel for this category -->
            {{--<x-dataset.dataset-stats :stats="$customStats" class="rounded-t-none text-base p-3 rounded-b-lg" svgSize="w-6 h-6"/>--}}

        </div>
    @empty
        <p class="text-gray-200">No categories found</p>
    @endforelse
</div>
