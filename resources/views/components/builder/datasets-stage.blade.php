<div class="container mx-auto px-4 py-4">
    <div class="space-y-3">
        @foreach($datasets as $dataset)
            <div x-data="{ checked: false }"
                 class="bg-slate-800 rounded-lg shadow-lg border border-slate-700 hover:bg-slate-750 transition-all duration-200 cursor-pointer"
                 @click="checked = !checked">
                <div class="p-4 flex gap-4">
                    <!-- Left section -->
                    <div class="flex gap-4">
                        <div class="" @click.stop>
                            <input type="checkbox"
                                   name="selected_datasets[]"
                                   value="{{ $dataset->id }}"
                                   wire:model="selectedDatasets.{{$dataset->id}}"
                                   class="w-5 h-5 text-blue-500 bg-slate-700 rounded border-slate-600 focus:ring-blue-500 focus:ring-offset-slate-800">
                        </div>

                        <div class="flex-shrink-0">
                            <img src="https://picsum.photos/200/300"
                                 alt="Preview of {{ $dataset->display_name }}"
                                 class="w-20 h-20 object-cover rounded-lg border border-slate-600">
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="flex-1 min-w-0">
                        <!-- Top row with title, tag, and button -->
                        <div class="flex items-center justify-between gap-4 mb-2">
                            <div class="flex items-center gap-3 min-w-0">
                                <h3 class="text-lg font-semibold text-white truncate">{{ $dataset->display_name }}</h3>
                                <span class="px-2 py-0.5 rounded-full text-xs whitespace-nowrap {{ $dataset->annotation_technique === 'Bounding box' ? 'bg-green-900/50 text-green-300' : 'bg-blue-900/50 text-blue-300' }}">
                                    {{ $dataset->annotation_technique }}
                                </span>
                            </div>
                            <a wire:navigate href="{{ route('dataset.show', ['uniqueName' => $dataset['unique_name']])}}"
                                class="flex-shrink-0 px-3 py-1.5 bg-blue-600/20 text-blue-400 text-sm rounded-lg hover:bg-blue-600/30 transition-all duration-200">
                                Preview Dataset
                            </a>
                        </div>

                        <!-- Description -->
                        <p class="text-sm text-gray-400 line-clamp-2 mb-3 break-all">{{'This is a sample descriptiondescriptiondescriptiondescriptiondescriptiondescriptiondescriptiondescription descriptiondescription descriptiondescriptiondescription descriptiondescription  for the dataset. It can span multiple lines and will be truncated after two lines with an ellipsis if it gets too long.' }}</p>

                        <!-- Stats and Properties -->
                        <div class="flex flex-col gap-2">
                            <!-- Stats -->
                            <div class="flex items-center gap-3 text-sm text-gray-300">
                                <span class="flex items-center gap-1" title="Total images">
                                    <x-humble-image class="w-5 h-5 text-gray-500 rounded-full"/>
                                    {{ number_format($dataset->num_images) }}
                                </span>
                                <span class="flex items-center gap-1" title="Total classes">
                                    <x-pepicon-label class="w-5 h-5 text-gray-500"/>
                                    {{ count($dataset->classes) }}
                                </span>
                            </div>

                            <!-- Dataset Properties -->
                            <div class="flex items-center gap-2 overflow-x-auto w-full max-w-full scrollbar-thin scrollbar-thumb-slate-600">
                                @forelse($dataset->datasetProperties as $property)
                                    <div class="flex-shrink-0 bg-slate-700/50 px-2 py-1 rounded text-sm text-gray-300 whitespace-nowrap">
                                        {{ $property->propertyValue->value }}
                                    </div>
                                @empty
                                    <div class="flex-shrink-0 bg-slate-700/50 px-2 py-1 rounded text-sm text-gray-400">
                                        No properties
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
