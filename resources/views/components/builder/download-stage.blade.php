<div x-data="{open: ''}" class="w-full">
    <livewire:components.download-dataset :key="'admin-datasets-download-dataset'" />

    {{-- Main Container with Improved Styling --}}
    <div class="rounded-xl  space-y-6">

        {{-- Header with Dataset Stats - Now with better layout and spacing --}}
        <div class="flex flex-col gap-4 pb-5 border-b border-slate-700/40">
            <h3 class="text-lg font-medium text-white mb-2">Dataset Overview</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-slate-800/60 rounded-lg p-4 backdrop-blur-sm">
                    <x-dataset.dataset-stats
                        :stats="$this->finalDataset['stats']"
                        class="text-base"
                        svgSize="w-6 h-6"/>
                </div>
                <div class="bg-slate-800/60 rounded-lg p-4 backdrop-blur-sm">
                    <x-dataset.image-stats
                        :image_stats="$this->finalDataset['image_stats']"
                        class="p-0" />
                </div>
            </div>
        </div>

        {{-- Categories and Metadata with improved styling --}}
        <div class="space-y-4 pb-5 border-b border-slate-700/40">
            <h4 class="text-slate-300 font-medium mb-3">Categories & Metadata</h4>
            <div class="flex items-center gap-3 overflow-x-auto pb-2 scrollbar-thin scrollbar-thumb-slate-600 scrollbar-track-slate-800/30">
                {{-- Categories with improved styling --}}
                @foreach($this->finalDataset['categories'] as $category)
                    <div class="flex-shrink-0 bg-blue-600 bg-opacity-80  px-3 py-1.5 rounded-full text-sm font-medium text-white whitespace-nowrap">
                        {{ $category['name'] }}
                    </div>
                @endforeach

                {{-- Metadata with improved styling --}}
                @forelse($this->finalDataset['metadataValues'] as $metadata)
                    <div class="flex-shrink-0 bg-slate-700  px-3 py-1.5 rounded-full text-sm text-gray-200 whitespace-nowrap ">
                        {{ $metadata['value'] }}
                    </div>
                @empty
                    <div class="flex-shrink-0 bg-slate-700/30 px-3 py-1.5 rounded-full text-sm text-gray-400">
                        No metadata
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Export Controls with improved styling --}}
        <div class="flex flex-wrap items-center gap-4 pt-2">
            <h4 class="text-slate-300 font-medium w-full mb-2">Export Options</h4>

            {{-- Download Button with improved styling --}}
            <button @click="$wire.cacheQuery; open = 'download-dataset'"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white font-medium text-sm rounded-lg transition-all duration-200 shadow-md hover:shadow-blue-500/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Export Options</span>
            </button>
        </div>
    </div>
</div>
