<div class="flex flex-col ">
    <x-dataset.dataset-stats :stats="$this->finalDataset['stats']" class="text-base p-2 rounded-none rounded-t-md" svgSize="w-6 h-6"></x-dataset.dataset-stats>

    <div class="bg-slate-800 rounded-lg rounded-tl-none shadow-lg py-4">
        <x-containers.images-container :images="$this->paginatedImages" inputAction="exclude"/>
    </div>
</div>
