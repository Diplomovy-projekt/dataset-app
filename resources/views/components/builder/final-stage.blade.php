<div class="flex flex-col">
    <div class="flex flex-col bg-slate-800 w-fit rounded-lg p-1 sm:p-3">
        <x-dataset.dataset-stats :stats="$this->finalDataset['stats']" class="text-base p-2 rounded-none rounded-t-md" svgSize="w-6 h-6"></x-dataset.dataset-stats>
        <x-dataset.image-stats :image_stats="$this->finalDataset['image_stats']"
        class="border-y border-slate-800 bg-slate-800 w-fit px-4 py-2"/>
    </div>

    <div class=" my-4">
        <x-containers.images-container :images="$this->paginatedImages" inputAction="exclude"/>
    </div>
</div>
