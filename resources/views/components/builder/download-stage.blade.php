<div class="w-full">
    <!-- Main Container -->
    <div class="bg-slate-800 rounded-lg shadow-lg border border-slate-700 p-6 space-y-6">
        <!-- Dataset Stats Section -->
        <div class="dataset-stats">
            <x-dataset.dataset-stats :stats="$this->finalDataset['stats']" class="text-base ml-2" svgSize="w-6 h-6"/>
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
            <!-- Format Selector -->
            <div class="relative">
                <select wire:model="exportFormat"
                        class="w-64 appearance-none px-4 py-2 pr-10 bg-slate-700 text-gray-300 text-sm rounded-lg border border-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="" disabled selected>Select Format</option>
                    @foreach($this->availableFormats as $format)
                        <option wire:key="download-annot-formtat{{$format['name']}}"
                                value="{{ $format['name'] }}">{{ $format['name'] }}</option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                @error('exportFormat')
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            <!-- Download Button -->
            <button wire:click="downloadCustomDataset"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-gray-200 text-sm rounded-lg transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-9.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
                <span>Download</span>
            </button>
        </div>

        <!-- Error Message -->
        @if($this->failedDownload)
            <x-dataset.dataset-errors
                    :errorMessage="$this->failedDownload['message']"
                    :errorData="$this->failedDownload['data']">
            </x-dataset.dataset-errors>
        @endif
    </div>
</div>
