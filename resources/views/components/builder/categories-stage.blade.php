@props([
    'categories'
])
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 p-4">
    @forelse($categories as $category)
        <label class="relative block bg-gray-700 w-40 h-40 rounded-lg overflow-hidden shadow-md
                      hover:shadow-xl hover:bg-gray-600
                      transition-all duration-300 ease-in-out
                      cursor-pointer group">
            {{-- Checkbox that covers entire label --}}
            <input
                type="checkbox"
                value="{{$category->id}}"
                wire:model="selectedCategories"
                class="absolute z-20 top-2 right-2 form-checkbox h-5 w-5 text-indigo-600
                       bg-gray-700 border-transparent
                       focus:border-transparent focus:ring-2 focus:ring-indigo-500"
            >

            <img
                src="https://picsum.photos/seed/{{ $category->name }}/400/300"
                alt="{{ $category->name }}"
                class="w-full h-full object-cover
                       group-hover:opacity-80
                       transition-opacity duration-300"
            >

            <div class="absolute bottom-0 left-0 right-0 p-2 bg-gray-700 bg-opacity-70">
                <h3 class="text-lg font-semibold text-gray-200 truncate">
                    {{ $category->name }}
                </h3>
            </div>
        </label>
    @empty
        <p class="text-gray-200">No categories found</p>
    @endforelse
</div>
