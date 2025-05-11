@props(
    [
        'categories' => [],
        'metadataTypes' => [],
        'selectedCategories' => [],
        'selectedMetadata' => [],
        'description' => ''
    ]
)
<div class="space-y-6">
    <!-- Categories Accordion -->
    <div class="space-y-6">
        <div x-data="{ open: false }" class="rounded-lg border border-slate-900">
            <button type="button" @click="open = !open" class="w-full flex justify-between items-center p-4 hover:bg-slate-900/50 transition-colors">
                <span class="font-medium">Categories</span>
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-5 w-5 transform transition-transform"
                     :class="{ 'rotate-180': open }"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="open"
                 x-collapse
                 class="border-t border-slate-900">
                <div class="p-4 grid grid-cols-2 md:grid-cols-3 gap-3 items-start text-gray-100">
                    @foreach($categories as $category)
                        <label class="flex items-center cursor-pointer space-x-2">
                            <div class="relative h-5 w-5">
                                <input type="checkbox"
                                       wire:model="selectedCategories"
                                       value="{{ $category['id'] }}"
                                       class="peer h-5 w-5 cursor-pointer appearance-none rounded-lg border border-blue-500 checked:bg-blue-500 checked:border-blue-600" />

                                <!-- Fade-in SVG -->
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="absolute h-3.5 w-3.5 text-white left-1/2 top-1/2 opacity-0 transform -translate-x-1/2 -translate-y-1/2 transition-opacity duration-300 ease-in-out
            peer-checked:opacity-100"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="3"
                                     stroke-linecap="round"
                                     stroke-linejoin="round">
                                    <polyline points="4 12 10 18 20 6" />
                                </svg>
                            </div>
                            <span class="text-sm text-white">{{ $category['name'] }}</span>
                        </label>
                    @endforeach
                </div>
                @error('categories')
                <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <!-- Metadata Accordions -->
    <div class="space-y-6">
        <h3 class="text-lg font-semibold text-gray-400">Metadata</h3>

        @foreach($metadataTypes as $index => $metadataType)
            <div x-data="{ open: false }" class="rounded-lg border border-slate-900">
                <button type="button" @click="open = !open" class="w-full flex justify-between items-center p-4 hover:bg-slate-900/50 transition-colors">
                    <span class="font-medium">{{ $metadataType['name'] }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5 transform transition-transform"
                         :class="{ 'rotate-180': open }"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open"
                     x-collapse
                     class="border-t border-slate-900">
                    <div class="p-4 grid grid-cols-2 md:grid-cols-3 gap-3 items-start">
                        @foreach($metadataType['metadataValues'] as $metadataValue)
                            <label class="flex items-center cursor-pointer space-x-2">
                                <div class="relative h-5 w-5">
                                    <input type="checkbox"
                                           wire:model="selectedMetadata.{{ $metadataType['id'] }}.metadataValues"
                                           value="{{ $metadataValue['id'] }}"
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
                                <span class="text-sm">{{ $metadataValue['value'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Description -->
    <textarea
        wire:model="description"
        placeholder="Description"
        class="w-full bg-slate-900/50 border border-blue-500 rounded-lg p-3 text-white focus:outline-none  focus:ring-2 focus:ring-blue-500"
        rows="1"
    ></textarea>
</div>

