<div x-data="{open: ''}"
     class="w-full">
    <livewire:components.download-dataset :key="'admin-datasets-download-dataset'" />

    <!-- Main Container -->
    <div class="bg-slate-800 rounded-lg shadow-lg border border-slate-700 p-6 space-y-6">
        <!-- Dataset Stats Section -->
        <div class="flex flex-col gap-3">
            <x-dataset.dataset-stats :stats="$this->finalDataset['stats']" class="text-base ml-2" svgSize="w-6 h-6"/>
            <x-dataset.image-stats :image_stats="$this->finalDataset['image_stats']" class="p-0" />
        </div>

        <!-- Categories and Metadata -->
        <div class="space-y-4">
            <div class="flex items-center gap-3 overflow-x-auto pb-2 scrollbar-thin scrollbar-thumb-slate-600">
                <!-- Categories -->
                @foreach($this->finalDataset['categories'] as $category)
                    <div wire:key="dataset-categories-{{ $category['id'] }}"
                         class="flex-shrink-0 bg-blue-500/80 px-3 py-1.5 rounded-md text-sm text-blue-200 whitespace-nowrap">
                        {{ $category['name'] }}
                    </div>
                @endforeach

                <!-- Metadata -->
                @forelse($this->finalDataset['metadataValues'] as $metadata)
                    <div wire:key="dataset-metadata-{{ $metadata['id'] }}"
                         class="flex-shrink-0 bg-slate-700/50 px-3 py-1.5 rounded-md text-sm text-gray-300 whitespace-nowrap">
                        {{ $metadata['value'] }}
                    </div>
                @empty
                    <div class="flex-shrink-0 bg-slate-700/50 px-3 py-1.5 rounded-md text-sm text-gray-400">
                        No metadata
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Export Controls -->
        <div class="flex flex-wrap items-center gap-4">

            <!-- Download Button -->
            <button @click="$wire.cacheQuery; open = 'download-dataset'"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-gray-200 text-sm rounded-lg transition-colors duration-200">
                <span>Select format</span>
            </button>
        </div>
    </div>
</div>
