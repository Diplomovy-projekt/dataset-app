<div class="bg-slate-800 rounded-lg shadow-lg border border-slate-700 p-4">
    <div class="flex flex-col gap-2">
        <!-- Dataset Stats Section -->
        <x-dataset.dataset-stats :stats="$this->finalDataset['stats']" class="text-base" svgSize="w-5 h-5"/>


        <!-- Metadata Section -->
        <div class="flex items-center gap-2 overflow-x-auto w-full max-w-full scrollbar-thin scrollbar-thumb-slate-600">
            <div class="flex-shrink-0 bg-slate-700/50 px-2 py-1 rounded text-sm text-gray-300 whitespace-nowrap">
                Resolution: 1920x1080
            </div>
            <div class="flex-shrink-0 bg-slate-700/50 px-2 py-1 rounded text-sm text-gray-300 whitespace-nowrap">
                Format: JPEG
            </div>
        </div>
    </div>

    <!-- Dropdown and Download Section -->
    <div class="mt-4 flex items-center gap-4">
        <div class="relative w-64">
            <select wire:model="exportFormat" class="w-full appearance-none px-3 py-1.5 pr-8 bg-slate-700 text-gray-300 text-sm rounded-lg border border-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="" disabled selected>Select Format</option>
                @foreach($this->availableFormats as $format)
                    <option wire:key="download-annot-formtat{{$format['name']}}" value="{{ $format['name'] }}">{{ $format['name'] }}</option>
                @endforeach
            </select>

            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                </svg>
            </div>
            @error('exportFormat')
            <span class="text-red-500">{{ $message }}</span>
            @enderror
        </div>

        <button wire:click="downloadCustomDataset"
                class="flex-shrink-0 px-3 py-1.5 bg-blue-500 text-gray-200 text-sm rounded-lg hover:bg-blue-600 transition-all duration-200 flex items-center space-x-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-9.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
            <span>Download</span>
        </button>
    </div>
</div>
