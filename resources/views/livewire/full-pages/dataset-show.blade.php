<div
     x-data="datasetShow(@this)"
     class="container mx-auto pt-3">

    <livewire:forms.edit-dataset :editingDataset="$dataset['unique_name']"/>
    <livewire:forms.extend-dataset :editingDataset="$dataset['unique_name']"/>
    <livewire:components.classes-sample :uniqueNames="$dataset['unique_name']"/>

    <x-modals.fixed-modal modalId="download-dataset" class="w-fit">
        <div class="max-w-md mx-auto p-6 bg-slate-800 rounded-lg space-y-4">
            <!-- Added Header -->
            <h2 class="text-xl font-semibold text-gray-100 text-center mb-4">
                Select Annotation Format
            </h2>

            <!-- Your Original Select with minor enhancements -->
            <div class="relative w-64 mx-auto">
                <select wire:model="exportFormat" class="w-full appearance-none px-3 py-1.5 pr-8 bg-slate-700 text-gray-300 text-sm rounded-lg border border-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500 hover:border-slate-500 transition-colors">
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
            <!-- Added Download Button -->
            <button wire:click="startDownload" id="download-btn"
                    class="w-64 mx-auto flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                <x-eva-download class="w-4 h-4"/>
                Download
            </button>
            {{--<div wire:poll.1500ms="updateProgress"> <!-- Poll every 500ms -->
                <span>{{ $this->progress ?? null }}</span>
            </div>--}}
        </div>
    </x-modals.fixed-modal>
    <div class=" mb-5 bg-slate-900/50">
        <x-dataset.dataset-header></x-dataset.dataset-header>
        <div class="flex flex-col sm:flex-row sm:items-center border-t border-slate-800   p-4 gap-4">
            <x-search-bar />
            <div class="flex">
                <x-class-dropdown />
                <x-dropdown-menu direction="left" class="w-50">
                    <x-dropdown-menu-item
                        @click.prevent="open = 'extend-dataset'"
                        :icon="@svg('eva-upload')->toHtml()">
                        Extend Dataset
                    </x-dropdown-menu-item>
                    <x-dropdown-menu-item
                        @click.prevent.stop="open = 'edit-dataset'"
                        :icon="@svg('eos-edit')->toHtml()">
                        Edit Dataset info
                    </x-dropdown-menu-item>

                    <x-dropdown-menu-item
                        @click.prevent.stop="open = 'download-dataset'"
                        :icon="@svg('eva-download')->toHtml()">
                        Download Dataset
                    </x-dropdown-menu-item>

                    <div class="border-t border-gray-300"></div>

                    <x-dropdown-menu-item
                        wire:click="deleteDataset({{ $dataset['id'] }})"
                        wire:confirm="This will permanently delete the dataset"
                        danger
                        :icon="@svg('mdi-trash-can-outline')->toHtml()">
                        Delete Dataset
                    </x-dropdown-menu-item>
                    <x-dropdown-menu-item
                        wire:click="deleteImages(selectedImages)"
                        wire:confirm="Are you sure you want to delete these images?"
                        danger
                        :icon="@svg('mdi-trash-can-outline')->toHtml()">
                        Delete Images
                    </x-dropdown-menu-item>
                </x-dropdown-menu>
            </div>
        </div>
    </div>
    <x-containers.images-container :images="$this->paginatedImages"/>
</div>



@script
<script>
    Alpine.data('datasetShow', (livewireComponent, filePath) =>({
        open: '',
        filePath: filePath,
        init() {
            console.log('Dataset Show Component Initialized', this.filePath);

            /*document.getElementById('download-btn').addEventListener('click', () => { // ✅ Arrow function keeps `this`
                console.log('Download button clicked', this.filePath);
                setTimeout(() => this.checkProgress(), 2000);
            });*/
        },
        checkProgress() {
            console.log("Inside checkProgress", this.filePath);
            //fetch('/download/progress?filePath={{ storage_path("app/public/datasets/{$this->exportDataset}") }}')
            fetch('/download/progress?filePath='+this.filePath)
                .then(response => response.json())
                .then(data => {
                    console.log("Inside fetch",data);
                    document.getElementById('progress-bar').value = data.progress;
                    document.getElementById('progress-text').innerText = data.progress + '%';
                    if (data.progress < 100) {
                        setTimeout(this.checkProgress, 1000);
                    }
                });
        }
    }));

</script>
@endscript
