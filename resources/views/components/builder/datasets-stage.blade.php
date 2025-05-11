<div x-data="datasetsStage(@this)"
     class="container mx-auto py-4">
    <div id="paginatedDatasets" class="relative space-y-3">
        <x-misc.pagination-loading/>
        @forelse($this->paginatedDatasets as $index => $dataset)
            <label class="flex items-center cursor-pointer space-x-2">
            <div wire:key="datasets-stage-{{ $dataset['id'] }}"
                x-data="{ checked: false }"
                 class="bg-slate-800 rounded-lg shadow-lg border border-slate-700 hover:bg-slate-750 transition-all duration-200 cursor-pointer"
                 @click="checked = !checked">
                <div x-data="{ isChecked: false }"
                     class="p-4 flex flex-col sm:flex-row gap-4">
                    {{--Left section--}}
                    <div class="flex gap-4">
                        <div class="relative h-5 w-5">
                            <input type="checkbox"
                                   value="{{ $dataset['id'] }}"
                                   wire:model="selectedDatasets.{{$dataset['id']}}"
                                   class="peer h-5 w-5 cursor-pointer appearance-none rounded-lg border border-blue-500 checked:bg-blue-500 checked:border-blue-600" />

                            <!-- Fade-in SVG -->
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="absolute h-3.5 w-3.5 text-white left-1/2 top-1/2 opacity-0 transform -translate-x-1/2 -translate-y-1/2 transition-opacity duration-300 ease-in-out peer-checked:opacity-100"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="3"
                                 stroke-linecap="round"
                                 stroke-linejoin="round">
                                <polyline points="4 12 10 18 20 6" />
                            </svg>
                        </div>
                        <div class="flex-shrink-0 w-20 h-20">
                            <x-images.annotated-image :image="$dataset->images->first()"></x-images.annotated-image>
                        </div>
                    </div>

                    {{--Main Content--}}
                    <div class="flex-1 min-w-0">
                        {{--Top row with title, tag, and buttons--}}
                        <div class="flex flex-col md:flex-row justify-between gap-4 mb-2">
                            <div class="flex flex-col md:flex-row md:items-center gap-3 flex-1 min-w-0">
                                <div class="min-w-0 mt-2 sm:mt-0">
                                    <h3 class="text-lg font-semibold text-white truncate">{{ $dataset['display_name'] }}</h3>
                                </div>
                                <span class="w-fit px-2 py-0.5 rounded-full text-xs whitespace-nowrap {{ $dataset['annotation_technique'] === 'Bounding box' ? 'bg-green-900/50 text-green-300' : 'bg-blue-900/50 text-blue-300' }}">
                                    {{ $dataset['annotation_technique'] }}
                                </span>
                            </div>
                            <div class="flex flex-col md:flex-row gap-3">
                                {{--Classes preview button--}}
                                <div x-data="{open: false}">
                                    <livewire:components.classes-sample :key="'classes-sample-'.$index.'-'.$dataset['unique_name']"
                                                                        :uniqueName="$dataset['unique_name']"
                                                                        :selectable="true"
                                                                        wire:model="selectedClasses.{{$dataset['id']}}"

                                                                        />
                                    <button
                                        @click.prevent="open = 'display-classes'"
                                        :disabled="!selectedDatasets[{{$dataset['id']}}]"
                                        :class="[
                                                'w-fit flex-shrink-0 px-3 py-1.5 text-sm text-slate-200 rounded-lg transition-all duration-200',
                                                selectedDatasets[{{$dataset['id']}}]
                                                    ? 'bg-blue-500 hover:bg-blue-600 border border-blue-400'
                                                    : 'bg-slate-600 border border-slate-500 cursor-not-allowed opacity-50'
                                                ]"
                                        :title="selectedDatasets[{{$dataset['id']}}] ? '' : 'Select Dataset to select classes'">
                                        Select classes
                                    </button>
                                </div>
                                {{-- Dataset preview button--}}
                                <a wire:navigate href="{{ route('dataset.show', ['uniqueName' => $dataset['unique_name']])}}"
                                   class="w-fit flex-shrink-0 px-3 py-1.5 bg-blue-600/20 text-blue-400 text-sm rounded-lg hover:bg-blue-700/70 transition-all duration-200">
                                    Preview Dataset
                                </a>
                            </div>
                        </div>


                        {{--Description--}}
                        <p class="text-sm text-gray-400 line-clamp-2 mb-3 break-all">{{$dataset['description']}}</p>

                        {{--Stats and Properties--}}
                        <div class="flex flex-col gap-2">
                            {{--Stats--}}
                            <x-dataset.dataset-stats :stats="$dataset['stats']" class="text-base" svgSize="w-5 h-5"/>
                            <x-dataset.image-stats :image_stats="$dataset['image_stats']" class="p-0"  />

                            {{--Dataset Properties--}}
                            <div class="flex items-center gap-2 overflow-x-auto w-full max-w-full scrollbar-thin scrollbar-thumb-slate-600">
                                @foreach($dataset['categories'] as $category)
                                    <div class="flex-shrink-0 bg-blue-600 bg-opacity-80  px-3 py-1.5 rounded-full text-sm font-medium text-white whitespace-nowrap">
                                        {{ $category['name'] }}
                                    </div>
                                @endforeach
                                @forelse($dataset['metadataValues'] as $metadata)
                                    <div class="flex-shrink-0 bg-slate-700  px-3 py-1.5 rounded-full text-sm text-gray-200 whitespace-nowrap ">
                                        {{ $metadata['value'] }}
                                    </div>
                                @empty
                                    <div class="flex-shrink-0 bg-slate-700/50 px-2 py-1 rounded text-sm text-gray-400">
                                        No metadata
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </label>
        @empty
            <p class="text-gray-400">No datasets found</p>
        @endforelse
    </div>
    <div class="flex-1 mt-3 overflow-x-auto">
        <div class="inline-block min-w-full">
            {{ $this->paginatedDatasets->links(data: ['scrollTo' => '#datasetsStage']) }}
        </div>
    </div>
</div>

@script
    <script>
        Alpine.data('datasetsStage', (livewireComponent) => ({
            selectedDatasets: livewireComponent.entangle('selectedDatasets')
        }));
    </script>
@endscript
