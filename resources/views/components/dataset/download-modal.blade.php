@props([
    'datasetId' => ''
]
)

<x-modals.fixed-modal modalId="download-dataset" class="w-fit">
    <div class="max-w-md mx-auto p-6 bg-slate-800 rounded-lg space-y-4">
        <!-- Added Header -->
        <h2 class="text-xl font-semibold text-gray-100 text-center mb-4">
            Select Annotation Format
        </h2>

        <!-- Download modal -->
        <div class="relative w-64 mx-auto">
            <select wire:model="exportFormat" class="w-full appearance-none px-3 py-1.5 pr-8 bg-slate-700 text-gray-300 text-sm rounded-lg border border-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500 hover:border-slate-500 transition-colors">
                <option value="" disabled selected>Select Format</option>
                @foreach(App\Configs\AppConfig::ANNOTATION_FORMATS_INFO as $format)
                    <option wire:key="download-annot-formtat{{$format['name']}}" value="{{ $format['name'] }}">{{ $format['name'] }}</option>
                @endforeach
            </select>

            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>
        @error('exportFormat')
        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
        @enderror
        @if($this->failedDownload)
            <x-dataset.dataset-errors
                :errorMessage="$this->failedDownload['message']"
                :errorData="$this->failedDownload['data']">
            </x-dataset.dataset-errors>
        @endif
        <!-- Download Button -->
        <button wire:click="startDownload({{$datasetId}})" id="download-btn"
                class="w-64 mx-auto flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            <x-eva-download class="w-4 h-4"/>
            Download
        </button>
        <div wire:poll.1500ms="updateProgress"> <!-- Poll every 500ms -->
            <span>{{ $this->progress ?? null }}</span>
        </div>
    </div>
</x-modals.fixed-modal>
