<div class="text-gray-200"
     x-data="{open: ''}">

    {{-- Modals --}}
    <livewire:components.resolve-request :key="'resolve-request-component'" lazy/>
{{--
    <livewire:components.classes-sample :key="'dataset-show-clases-sample'" :uniqueName="$dataset['unique_name']"  />
--}}

    <!-- Header -->
    <x-misc.highlight-card
        color="yellow"
        title="Reduce Dataset Review"
        description="Review changes requested by the user to reduce the dataset and resolve the request."
    >
        <x-slot:icon>
            <x-myicon name="reduce-dataset" color="text-yellow-500"/>
        </x-slot:icon>
        <x-misc.button
            color="yellow"
            variant="primary"
            size="lg"
            @click="$dispatch('init-resolve-request',{requestId: '{{ $requestId }}'});
                         open = 'resolve-request'">
            Resolve
        </x-misc.button>
    </x-misc.highlight-card>
    {{-- Info --}}
    <div class="border-l-0 border-r-0 border-yellow-500 rounded-lg mb-5 bg-slate-900/50">
        <div class="flex sm:justify-between border-b border-slate-800 p-4 gap-4">
            <x-dataset.dataset-stats :stats="$this->stats['stats']" class="px-4 py-2 text-xl"/>
            <x-dataset.image-stats :image_stats="$this->stats['image_stats']" />
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center border-t border-slate-800   p-4 gap-4">
            <x-search-bar searchTitle="Search Images..." searchModel="searchTerm" searchMethod="search" />
            <div class="flex">
                <x-dataset.class-dropdown />
            </div>
        </div>
    </div>

    {{-- Image Contaienr --}}
    <x-containers.images-container/>

</div>
