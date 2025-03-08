<div x-data="classSampleSort(@this)"
     id="classes-sample-container-{{$this->dataset['unique_name']}}">
    <x-modals.fixed-modal modalId="display-classes" class="sm:max-w-11/12">
        <!-- Modal Title -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-200">Class Sample Preview</h1>
            <div class="h-px flex-1 bg-gradient-to-r from-transparent via-slate-700 to-transparent mx-6"></div>
        </div>
        @if($this->selectable)
            <p class="text-gray-500 font-sm mb-2">Here you can choose which classes to include in your dataset.</p>
        @endif
        <div class="mb-8" {{--wire:key="livewire-classes-sample-comp-{{$this->dataset['unique_name']}}"--}}>
            <!-- Dataset Header -->
            <div class="flex flex-col sm:flex-row items-center justify-between bg-gradient-to-r from-slate-800 to-slate-900 p-4 rounded-t-xl border-b border-slate-700">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <div class="bg-blue-500 p-2 rounded-lg">
                        <x-icon name="o-folder" class="w-5 h-5 text-gray-200" />
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-xl font-bold text-gray-200 truncate">{{$this->dataset['display_name']}}</h2>
                        <p class="text-slate-400 text-sm">Total Classes: {{ count($this->dataset['classes']) }}</p>
                    </div>
                </div>

                <!-- Sorting Controls -->
                <div class="flex sm:items-center gap-3" x-data="{ sortOrder: 'asc' }">
                    <span class="text-slate-400 text-sm">Sort by:</span>
                    <select
                        class="bg-slate-700 text-gray-200 rounded-md px-3 py-1.5 text-sm border border-slate-600 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        @change="sortBy($event.target.value)">
                        <option @click="sortBy('class-name')" value="class-name">Class Name</option>
                        <option @click="sortBy('annotation-count')" value="annotation-count">Annotation Count</option>
                        <option @click="sortBy('image-count')" value="image-count">Image Count</option>
                    </select>

                    <button
                        @click="sortOrder = sortOrder === 'asc' ? 'desc' : 'asc'; sortBy()"
                        class="p-1.5 rounded hover:bg-slate-600 text-gray-200 transition-colors">
                        <svg x-show="sortOrder === 'asc'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                        </svg>
                        <svg x-show="sortOrder === 'desc'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>
            </div>


            <!-- Classes Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4 bg-slate-900/50 rounded-b-xl">
                @foreach($this->dataset['classes'] as $class)
                    <div id="class-sample-{{$class['id']}}" class="bg-slate-800 rounded-lg hover:bg-slate-750 transition-all duration-200 group" wire:key="nested-classes-in-samples-{{$class['id']}}">
                        <!-- Class Header -->
                        <div class="p-4 border-b border-slate-700">
                            <div class="flex items-center justify-between">
                                @if($this->selectable)
                                    <label x-data="{ skip: false }"
                                           class="flex items-center space-x-3 text-gray-400 hover:text-gray-300 cursor-pointer">
                                        <div class="relative inline-flex items-center">
                                            <input
                                                type="checkbox"
                                                class="sr-only peer"
                                                wire:model="selectedClasses.{{$class['id']}}"
                                            >
                                            <div
                                                class="w-9 h-5 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"
                                                x-bind:class="skip ? 'bg-blue-500' : 'bg-gray-700'"
                                            ></div>
                                        </div>
                                    </label>
                                @endif
                                {{-- Class Name --}}
                                <h3 class="text-lg font-semibold text-gray-200"
                                    data-type="class-name"
                                    data-value="{{$class['name']}}">
                                    {{$class['name']}}
                                </h3>
                                {{--Image and Annotation count--}}
                                <div class="flex gap-2">
                                    <div
                                        class="flex items-center gap-2 bg-slate-700 px-3 py-1 rounded-full"
                                        title="Images that have at least 1 annotation labeled with this class"
                                        data-type="image-count"
                                        data-value="{{$class['imageCount']}}">
                                        <x-icon name="o-photo" class="w-4 h-4 text-blue-400" />
                                        <span class="text-sm text-gray-200">{{$class['imageCount']}}</span>
                                    </div>
                                    <div
                                        class="flex items-center gap-2 bg-slate-700 px-3 py-1 rounded-full"
                                        title="Annotations that are labeled with this class"
                                        data-type="annotation-count"
                                        data-value="{{$class['annotationCount']}}">
                                        <x-jam-pencil class="text-green-400 w-4 h-4"/>
                                        <span class="text-sm text-gray-200">{{$class['annotationCount']}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Image Preview Grid -->
                        <div class="p-4">
                            <div class="grid grid-cols-3 gap-2">
                                @foreach($class['images'] as $image)
                                    <div class="relative group/image" wire:key="path-of-image-in-classes-sample{{$image['filename']}}">
                                        <x-images.img dataset="{{$this->dataset['unique_name']}}"
                                                      folder="{{$image['folder']}}"
                                                      filename="{{$image['filename']}}"
                                                      id="{{$image['filename']}}"
                                                      fetchpriority="low"
                                                      class="w-14 rounded-md border border-slate-700 group-hover/image:border-blue-500 transition-all"
                                                      @click=" const imgSrc = $event.target.src.replace('/thumbnails/', '/full-images/');
                                                                $dispatch('open-full-screen-image', { src: imgSrc, overlayId: null })">
                                        </x-images.img>
                                        <!-- Hover Overlay -->
                                        <div class="w-14 pointer-events-none absolute inset-0 bg-slate-900/60 opacity-0 group-hover/image:opacity-100 transition-opacity rounded-md flex items-center justify-center">
                                            <x-icon name="o-magnifying-glass" class="w-4 h-4 text-gray-200" />
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-modals.fixed-modal>
</div>

@script
<script>
    Alpine.data('classSampleSort', (livewireComponent) => ({
        sortOrder: 'asc',
        sortType: 'class-name',
        selectedClasses: livewireComponent.entangle('selectedClasses'),
        uniqueName: livewireComponent.dataset.unique_name,
        init() {
        },
        sortBy(sortBy = null) {
            this.sortType = sortBy ?? this.sortType;

            const parentContainer = document.querySelector('#classes-sample-container-' + this.uniqueName);
            if (!parentContainer) {
                return;
            }
            const classes = [...parentContainer.querySelectorAll('.group')];
            classes.sort((a, b) => {
                const aElement = a.querySelector(`[data-type=${this.sortType}]`);
                const bElement = b.querySelector(`[data-type=${this.sortType}]`);

                if (!aElement || !bElement) return 0;

                const aVal = aElement.getAttribute('data-value');
                const bVal = bElement.getAttribute('data-value');

                // Detect if values are numeric
                const aNum = parseFloat(aVal);
                const bNum = parseFloat(bVal);
                const isNumeric = !isNaN(aNum) && !isNaN(bNum);

                if (isNumeric) {
                    return this.sortOrder === 'asc' ? aNum - bNum : bNum - aNum;
                } else {
                    return this.sortOrder === 'asc' ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
                }
            });

            classes.forEach((el) => el.parentElement.appendChild(el));
        }
    }));

</script>
@endscript
