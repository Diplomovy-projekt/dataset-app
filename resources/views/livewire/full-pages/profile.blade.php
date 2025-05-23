<div x-data="{ open: '' }">

    <div id="my-dataset-header" class="bg-gradient-to-r from-slate-800 to-slate-900 p-4 border-b border-slate-700 rounded-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="bg-blue-500 p-2 rounded-lg">
                    <x-icon name="o-folder" class="w-5 h-5 text-gray-200" />
                </div>
                <h2 class="text-xl font-bold text-gray-200">My Datasets</h2>
            </div>
            <button  @click="$dispatch('new-upload');
                                open = 'datasetUpload'"
                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                <div class="flex items-center gap-2">
                    <x-icon name="o-plus" class="w-4 h-4" />
                    <span>Upload Dataset</span>
                </div>
            </button>
        </div>
    </div>
    <livewire:forms.dataset-upload :mode="'new'" :editingDataset="'0195ee1c9d9f-0073-7328-bada-e7c938f0e20816ed20be'" {{--lazy="on-load"--}}/>

    <div class="relative flex flex-wrap sm:gap-5 pt-5">
        <x-misc.pagination-loading/>

    @forelse($this->paginatedDatasets as $dataset)
            <x-dataset.dataset-card :dataset="$dataset"></x-dataset.dataset-card>
        @empty
        @endforelse
    </div>
    <div class="flex-1 my-3">
        {{ $this->paginatedDatasets->links(data: ['scrollTo' => '#my-dataset-header']) }}
    </div>
</div>

