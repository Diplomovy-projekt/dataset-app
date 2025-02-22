<div
     x-data="datasetShow(@this)"
     class="container mx-auto pt-3">

    <livewire:forms.edit-dataset :editingDataset="$dataset['unique_name']" />
    <livewire:forms.extend-dataset :editingDataset="$dataset['unique_name']" />
    <livewire:components.classes-sample :uniqueNames="$dataset['unique_name']" />
    <livewire:components.download-dataset :datasetId="$dataset['unique_name']" />

    <div class=" mb-5 bg-slate-900/50">
        <x-dataset.dataset-header></x-dataset.dataset-header>
        <div class="flex flex-col sm:flex-row sm:items-center border-t border-slate-800   p-4 gap-4">
            <x-search-bar searchTitle="Search Images..." searchModel="searchTerm" searchMethod="search" />
            <div class="flex">
                <x-class-dropdown />
                <x-dropdown-menu direction="left" class="w-50">
                    @auth
                        @if(auth()->user()->role === 'admin' || auth()->id() === $dataset['user_id'])
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
                        @endif
                    @endauth
                    <x-dropdown-menu-item
                        @click.prevent="open = 'display-classes'"
                        :icon="@svg('o-tag')->toHtml()">
                        Preview Classes
                    </x-dropdown-menu-item>

                    <x-dropdown-menu-item
                        @click="$wire.cacheQuery('{{$dataset['id']}}'); open = 'download-dataset'"
                        :icon="@svg('eva-download')->toHtml()">
                        Download Dataset
                    </x-dropdown-menu-item>

                    @auth
                        @if(auth()->user()->role === 'admin' || auth()->id() === $dataset['user_id'])
                            <div class="border-t border-gray-300"></div>

                            <x-dropdown-menu-item
                                wire:click="deleteDataset({{ $dataset['id'] }})"
                                wire:confirm="This will permanently delete the dataset"
                                danger
                                :icon="@svg('mdi-trash-can-outline')->toHtml()">
                                Delete Dataset
                            </x-dropdown-menu-item>

                            <x-dropdown-menu-item
                                wire:click="deleteImages()"
                                wire:confirm="Are you sure you want to delete these images?"
                                danger
                                :icon="@svg('mdi-trash-can-outline')->toHtml()">
                                Delete Images
                            </x-dropdown-menu-item>
                        @endif
                    @endauth
                </x-dropdown-menu>


            </div>
        </div>
    </div>
    <x-containers.images-container :images="$this->paginatedImages"/>
</div>



@script
<script>
    Alpine.data('datasetShow', (livewireComponent) =>({
        open: '',
        init() {
        }
    }));

</script>
@endscript
