<div>
    {{--Search Bar--}}
    <div id="searchBar" class="bg-gradient-to-r from-slate-800 to-slate-900 p-4 border-b border-slate-700 rounded-lg">
        <div class="flex flex-col sm:flex-row  sm:items-center gap-3 ">
            <div class="flex items-center gap-3 ">
                <div class="bg-blue-500 p-2 rounded-lg">
                    <x-icon name="o-folder" class="w-5 h-5 text-gray-200" />
                </div>
                <h2 class="text-xl font-bold text-gray-200 whitespace-nowrap">Datasets</h2>
            </div>
            <x-search-bar searchTitle="Search Datasets..." searchModel="searchTerm" searchMethod="search" />

        </div>
    </div>

    {{--Dataset Card Container--}}
    <div class="relative flex flex-wrap sm:gap-5 pt-5">
        <x-misc.pagination-loading/>
        @foreach($this->paginatedDatasets as $dataset)
            <x-dataset.dataset-card :dataset="$dataset"></x-dataset.dataset-card>
        @endforeach
    </div>
    <div class="flex-1 my-3">
        {{ $this->paginatedDatasets->links(data: ['scrollTo' => '#searchBar']) }}
    </div>
</div>
