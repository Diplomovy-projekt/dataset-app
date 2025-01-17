<div x-data="{
        hoveredImageIndex: null,
        open: '',
        fullScreenImageModal: false,
        fullScreenImageSrc: '',
        modalSvg: '',
        selectedImages: [],
     }"
     class="container mx-auto pt-3">

    <livewire:forms.edit-dataset :editingDataset="$dataset['unique_name']"/>
    <livewire:forms.extend-dataset :editingDataset="$dataset['unique_name']"/>
    <livewire:components.classes-sample :uniqueNames="$dataset['unique_name']"/>
    <div class="flex flex-col mb-5 bg-slate-900/50">
        <x-dataset-header></x-dataset-header>
        <div class="flex items-center border-t border-slate-800   p-4 gap-4">
            <x-search-bar />
            <x-category-dropdown />
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
                    wire:click="downloadDataset({{ $dataset['id'] }}, selectedImages)"
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
    <div  class="flex flex-wrap gap-10">
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
                            const imgSrc = $event.target.src.replace('/thumbnails/', '/full-images/');
                            $dispatch('open-full-screen-image', { src: imgSrc, overlayId: `svg-{{ $image['filename'] }}` })">
                    <x-annotation-overlay :image="$image"></x-annotation-overlay>
                    <div
                        x-show="hoveredImageIndex === {{ $image->id }} || selectedImages.includes({{ strval($image->id) }})"
                        class="absolute -top-0 -right-0"
                    >
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                class="peer sr-only"
                                @change="selectedImages.includes({{ $image->id }})
                                        ? selectedImages = selectedImages.filter(id => id !== {{ $image->id }})
                                        : selectedImages.push({{ $image->id }})
                                        ; console.log(selectedImages)"
                                :checked="selectedImages.includes({{ $image->id }})"
                            />
                            <div class="w-5 h-5 bg-black/30 rounded border-2 border-blue-500 peer-checked:bg-blue-500 peer-focus:ring-2 peer-focus:ring-blue-300"></div>
                        </label>
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
    <x-images.full-screen-image/>


</div>
