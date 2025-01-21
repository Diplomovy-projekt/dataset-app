@props([
    'categories'
])
<div class="flex flex-wrap gap-5">
    @forelse($categories as $category)
        <label wire:key="{{ $category['id'] }}"
            class="relative block bg-gray-700 w-40 h-40 rounded-lg overflow-hidden shadow-md
                      hover:shadow-xl hover:bg-gray-600
                      transition-all duration-300 ease-in-out
                      cursor-pointer group">
            {{-- Checkbox that covers entire label --}}
            <input
                type="checkbox"
                value="{{$category['id']}}"
                wire:model="selectedCategories"
                class="absolute z-20 top-2 right-2 form-checkbox h-5 w-5 text-indigo-600
                       bg-gray-700 border-transparent
                       focus:border-transparent focus:ring-2 focus:ring-indigo-500">
            {{-- Image --}}
            <div class="relative">
                <img src="{{asset($category['image']['path'])}}"
                     alt="{{ $category['name'] }}"
                     class="h-full w-full object-cover group-hover:opacity-80 transition-opacity duration-300">
                <x-annotation-overlay :image="$category['image']"></x-annotation-overlay>
            </div>
            {{-- Category name --}}
            <div class="absolute bottom-0 left-0 right-0 p-2 bg-slate-800 bg-opacity-80">
                <h3 class="text-lg font-semibold text-gray-200 truncate">
                    {{ $category['name'] }}
                </h3>
            </div>
        </label>
    @empty
        <p class="text-gray-200">No categories found</p>
    @endforelse
</div>
