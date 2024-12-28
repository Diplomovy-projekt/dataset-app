<div x-data="{ hoveredIndex: null, open: '' }" class="container mx-auto pt-6">


    <livewire:forms.edit-dataset :editingDataset="$dataset['unique_name']"/>
    <livewire:forms.extend-dataset :editingDataset="$dataset['unique_name']"/>


    <div  class="flex flex-col pb-5">
        <button
            @click.prevent="open = 'edit-dataset'">
            Edit</button>
        <button
            @click.prevent="open = 'extend-dataset'">
            Extend</button>
        <x-dataset-header></x-dataset-header>
        <div class=" flex items-center border-t border-slate-800 bg-slate-900/50 p-4 gap-4">
            <x-search-bar />
            <x-category-dropdown />
                <button wire:click="$set('modalStyle', 'edit-info')"
                        @click.prevent="open = 'uploadDataset'">
                    IDKKK
                </button>
            <x-dropdown-menu class="w-50">
                <x-dropdown-menu-item
                    wire:click="$set('modalStyle', 'edit-info')"
                    @click.prevent="open = 'uploadDataset'"
                    :icon="@svg('eva-download')->toHtml()">
                    Extend Dataset
                </x-dropdown-menu-item>
                <x-dropdown-menu-item
                    @click.prevent.stop="open = 'uploadDataset'"
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
    <div class="flex flex-wrap justify-between gap-10">
        {{-- Display images in a grid container --}}
        @foreach ($this->images as $image)
            <div x-data="{ imageId: {{ $image->id }} }"
                 @mouseenter="hoveredIndex = {{ $image->id }}"
                 @mouseleave="hoveredIndex = null" class="flex flex-col">
                <div class="relative h-36 w-36" wire:key="{{$image['filename']}}">
                    {{--<div class="absolute w-36 h-36 inset-0 bg-gray-300 animate-pulse"></div>--}}
                    <img id="{{$image['filename']}}"
                         class="w-full h-full object-cover opacity-1 transition-opacity duration-500 ease-in-out rounded-md" loading="lazy"
                         src="{{ asset('storage/datasets/'.$this->dataset['unique_name'] . '/thumbnails/' . $image['filename']) }}" alt="Image"
                         {{--onload="this.style.opacity=1; this.previousElementSibling.style.display='none';"--}}>
                    <x-annotation-overlay :image="$image"></x-annotation-overlay>
                    <div x-show="hoveredIndex === {{ $image->id }}"  class="absolute -top-2 -right-2 ">
                        <x-mary-checkbox  />
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

</div>
