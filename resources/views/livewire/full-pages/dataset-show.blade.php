<div x-data="{
        hoveredImageIndex: null,
        editDatasetModal: '',
        fullScreenImageModal: false,
        fullScreenImageSrc: '',
        modalSvg: '',

     }"
     class="container mx-auto pt-6">

    <livewire:forms.edit-dataset :editingDataset="$dataset['unique_name']"/>
    <livewire:forms.extend-dataset :editingDataset="$dataset['unique_name']"/>

    <div class="flex flex-col pb-5">
        <x-dataset-header></x-dataset-header>
        <div class="flex items-center border-t border-slate-800 bg-slate-900/50 p-4 gap-4">
            <x-search-bar />
            <x-category-dropdown />
            <x-dropdown-menu class="w-50">
                <x-dropdown-menu-item
                    @click.prevent="editDatasetModal = 'extend-dataset'"
                    :icon="@svg('eva-upload')->toHtml()">
                    Extend Dataset
                </x-dropdown-menu-item>
                <x-dropdown-menu-item
                    @click.prevent.stop="editDatasetModal = 'edit-dataset'"
                    :icon="@svg('eos-edit')->toHtml()">
                    Edit Dataset info
                </x-dropdown-menu-item>

                <x-dropdown-menu-item
                    wire:click="downloadDataset({{ $dataset['id'] }})"
                    :icon="@svg('eva-download')->toHtml()">
                    Download Dataset
                </x-dropdown-menu-item>

                <div class="border-t border-gray-300"></div>

                <x-dropdown-menu-item
                    wire:click="deleteDataset({{ $dataset['id'] }})"
                    danger
                    :icon="@svg('mdi-trash-can-outline')->toHtml()">
                    Delete Dataset
                </x-dropdown-menu-item>
            </x-dropdown-menu>
        </div>
    </div>

    <div  class="flex flex-wrap justify-around gap-10">
        {{-- Display images in a grid container --}}
        @foreach ($this->images as $image)
            <div x-data="{ imageId: {{ $image->id }} }"
                 @mouseenter="hoveredImageIndex = {{ $image->id }}"
                 @mouseleave="hoveredImageIndex = null"
                 class="flex flex-col">
                <div class="relative h-36 w-36" wire:key="{{$image['filename']}}">
                    <img id="{{$image['filename']}}"
                         class="w-full h-full object-cover opacity-1 transition-opacity duration-500 ease-in-out rounded-md"
                         loading="lazy"
                         src="{{ asset('storage/datasets/'.$this->dataset['unique_name'] . '/thumbnails/' . $image['filename']) }}"
                         alt="Image"
                         @click="
                            fullScreenImageModal = true;
                            fullScreenImageSrc = '{{ asset('storage/datasets/'.$this->dataset['unique_name'] . '/full-images/' . $image['filename']) }}';
                            modalSvg = document.getElementById(`svg-{{ $image['filename'] }}`)?.cloneNode(true).outerHTML;">
                    <x-annotation-overlay :image="$image"></x-annotation-overlay>
                    <div x-show="hoveredImageIndex === {{ $image->id }}" class="absolute -top-2 -right-2">
                        <x-mary-checkbox />
                    </div>
                </div>
                <x-mary-popover position="top-start" offset="10">
                    <x-slot:trigger>
                        <div class="w-32 truncate">
                            <p class="text-sm">{{ $image['filename'] }}</p>
                        </div>
                    </x-slot:trigger>
                    <x-slot:content class="text-xs">
                        {{ $image['filename'] }}
                    </x-slot:content>
                </x-mary-popover>
            </div>
        @endforeach
    </div>
    {{ $this->images->links() }}

    {{-- Modal --}}
    <div x-show="fullScreenImageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
        <button
            class="absolute top-4 right-4 text-white text-2xl"
            @click="fullScreenImageModal = false">
            &times;
        </button>
        <div class="relative">
            <img :src="fullScreenImageSrc" class="max-w-full max-h-screen rounded-md" alt="Full-size Image">
            {{--Copy the svg overlay here--}}
            <div x-html="modalSvg" class="absolute inset-0 pointer-events-none"></div>

        </div>
    </div>


</div>
