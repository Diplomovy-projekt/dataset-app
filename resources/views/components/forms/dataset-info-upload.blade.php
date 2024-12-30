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
    <div class="space-y-6">
        <div x-data="{ open: false }" class="rounded-lg border border-base-300">
            <button type="button" @click="open = !open" class="w-full flex justify-between items-center p-4 hover:bg-base-200 transition-colors">
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
                 class="border-t border-base-300">
                <div class="p-4 space-y-3">
                    @foreach($categories as $category)
                        <x-mary-checkbox
                            wire:model="selectedCategories"
                            value="{{ $category['id'] }}"
                            label="{{ $category['name'] }}"
                            checked
                        />
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    {{--Metadata Section with Dynamic Accordions--}}
    <div class="space-y-6">
        <h3 class="text-lg font-semibold text-base-content">Metadata</h3>

        @foreach($metadataTypes  as $index => $metadataType)
            <div x-data="{ open: false }" class="rounded-lg border border-base-300">
                <button type="button" @click="open = !open" class="w-full flex justify-between items-center p-4 hover:bg-base-200 transition-colors">
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
                     class="border-t border-base-300">
                    <div class="p-4 space-y-3">
                        @foreach($metadataType['metadataValues'] as $metadataValue)
                            <x-mary-checkbox
                                wire:model="selectedMetadata.{{ $metadataType['id'] }}.metadataValues"
                                value="{{ $metadataValue['id'] }}"
                                label="{{ $metadataValue['value'] }}"
                            />
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <x-mary-textarea
        label="Description"
        wire:model="description"
        rows="1"
        inline />
</div>
