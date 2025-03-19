<div>
    <x-modals.fixed-modal modalId="download-dataset" class="w-fit">
        @if(count($classesData) > 0)
            <div class="max-w-5xl mx-auto p-6 pt-0 bg-slate-800 rounded-lg space-y-4">
                <!-- Annotation Statistics Section -->
                <div class="mb-4">
                    <x-misc.header title="Statistics"></x-misc.header>
                        <!-- Statistics summary -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                            <!-- Total annotations card -->
                        <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-lg p-3 border-l-4 border-blue-500 shadow-md hover:shadow-lg transition-shadow">
                            <div class="flex items-center mb-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-400 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                                </svg>
                                <span class="text-xs font-medium text-gray-400">Total Annotations</span>
                            </div>
                            <div class="flex items-baseline">
                                <span class="text-lg font-bold text-white">{{ $stats['totalCount'] }}</span>
                                <span class="ml-1 text-xs text-gray-400">items</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">Across {{ $stats['classCount'] }} classes</div>
                        </div>

                        <!-- Highest frequency card -->
                        <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-lg p-3 border-l-4 border-green-500 shadow-md hover:shadow-lg transition-shadow">
                            <div class="flex items-center mb-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-400 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-xs font-medium text-gray-400">Highest Frequency</span>
                            </div>
                            <div class="flex items-baseline">
                                <span class="text-lg font-bold text-white">{{$stats['maxClass']['count'] }}</span>
                                <span class="ml-1 text-xs text-gray-400">items</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1 truncate" title="{{ $stats['maxClass']['name'] }}">
                                Class: <span style="color: {{ $stats['maxClass']['rgb'] }}">{{ $stats['maxClass']['name'] }}</span>
                            </div>
                        </div>

                        <!-- Lowest frequency card -->
                        <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-lg p-3 border-l-4 border-red-500 shadow-md hover:shadow-lg transition-shadow">
                            <div class="flex items-center mb-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-400 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1v-5a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-xs font-medium text-gray-400">Lowest Frequency</span>
                            </div>
                            <div class="flex items-baseline">
                                <span class="text-lg font-bold text-white">{{ $stats['minClass']['count'] }}</span>
                                <span class="ml-1 text-xs text-gray-400">items</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1 truncate" title="{{ $stats['minClass']['name'] }}">
                                Class: <span style="color: {{ $stats['minClass']['name'] }}">{{ $stats['minClass']['name'] }}</span>
                            </div>
                        </div>

                        <!-- Statistics card -->
                        <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-lg p-3 border-l-4 border-purple-500 shadow-md hover:shadow-lg transition-shadow">
                            <div class="flex items-center mb-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-purple-400 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                                </svg>
                                <span class="text-xs font-medium text-gray-400">Distribution Stats</span>
                            </div>
                            <div class="sm:grid sm:grid-cols-2 sm:gap-x-2 sm:gap-y-1 mt-1">
                                <div>
                                    <span class="text-xs text-gray-500">Mean:</span>
                                    <span class="text-sm font-semibold text-white ml-1">{{ $stats['avgCount'] }}</span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500">Median:</span>
                                    <span class="text-sm font-semibold text-white ml-1">{{ $stats['median'] }}</span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500">Std Dev:</span>
                                    <span class="text-sm font-semibold text-white ml-1">{{ $stats['stdDev'] }}</span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500">Imbalance:</span>
                                    <span class="text-sm font-semibold text-white ml-1">{{ $stats['imbalance'] }}x</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <x-misc.header title="Frequency per class"></x-misc.header>
                    <!-- Scrollable container with fixed height -->
                    <div class="max-h-[50vh] overflow-y-auto pr-2">
                        <!-- Grid layout for desktop, 6 columns on PC -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                            @foreach($classesData as $annotation)
                                <div class="flex items-center p-2 bg-slate-900 rounded-lg">
                                    <!-- Class image thumbnail -->
                                    <div class="w-12 h-12 rounded-full flex-shrink-0 mr-2 flex items-center justify-center border-2 overflow-hidden" style="border-color: {{$annotation['rgb']}};">
                                        <x-images.img dataset="{{$annotation['image']['dataset']}}"
                                                      filename="{{$annotation['image']['filename']}}"
                                                      folder="{{$annotation['image']['folder']}}"
                                                      class="w-full h-full object-cover"
                                        ></x-images.img>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-xs font-medium text-gray-300 truncate" title="{{ $annotation['name'] }}">{{ $annotation['name'] }}</span>
                                            <span class="text-xs text-gray-400 ml-1">{{ $annotation['count'] }}</span>
                                        </div>
                                        <div class="text-xs text-gray-400 truncate mb-1" title="{{ $annotation['supercategory'] }}">{{ $annotation['supercategory'] }}</div>
                                        <div class="w-full bg-slate-700 rounded-full h-1.5">
                                            <div class="h-1.5 rounded-full bg-yellow-600"
                                                 style="width: {{ min(100, ($annotation['count'] / max(array_column($classesData, 'count')) * 100)) }}%;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <x-misc.header title="Settings"></x-misc.header>

                <div class="bg-slate-800 rounded-lg border border-slate-700 p-4">
                    <div class="flex flex-col sm:flex-row gap-6">
                        <!-- Min Annotations Control -->
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm text-gray-300">Min annotations</label>
                                <span class="text-xs text-slate-500">Range: 1-{{ max(array_column($this->originalClassesData, 'count')) }}</span>
                            </div>
                            <div class="flex items-center gap-3 mb-1">
                                <div class="relative flex-1">
                                    <input
                                        type="range"
                                        min="1"
                                        max="{{ max(array_column($this->originalClassesData, 'count')) }}"
                                        wire:model.live="minAnnotations"
                                        class="w-full h-1 appearance-none bg-slate-700 rounded focus:outline-none [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-blue-500 [&::-webkit-slider-thumb]:cursor-pointer [&::-moz-range-thumb]:h-4 [&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-blue-500 [&::-moz-range-thumb]:cursor-pointer"
                                    />
                                    <div class="absolute w-full flex justify-between text-xs text-slate-500 px-1 mt-1">
                                        <span>1</span>
                                        <span>{{ max(array_column($this->originalClassesData, 'count')) }}</span>
                                    </div>
                                </div>
                                <div class="w-16">
                                    <input
                                        type="number"
                                        min="1"
                                        max="{{ max(array_column($this->originalClassesData, 'count')) }}"
                                        wire:model.live="minAnnotations"
                                        class="w-full bg-slate-700 border border-slate-600 rounded px-2 py-1 text-sm text-blue-400 font-medium focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Max Annotations Control -->
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm text-gray-300">Max annotations</label>
                                <span class="text-xs text-slate-500">Range: 1-{{ max(array_column($this->originalClassesData, 'count')) }}</span>
                            </div>
                            <div class="flex items-center gap-3 mb-1">
                                <div class="relative flex-1">
                                    <input
                                        type="range"
                                        min="1"
                                        max="{{ max(array_column($this->originalClassesData, 'count')) }}"
                                        wire:model.live="maxAnnotations"
                                        class="w-full h-1 appearance-none bg-slate-700 rounded focus:outline-none [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-green-500 [&::-webkit-slider-thumb]:cursor-pointer [&::-moz-range-thumb]:h-4 [&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-green-500 [&::-moz-range-thumb]:cursor-pointer"
                                    />
                                    <div class="absolute w-full flex justify-between text-xs text-slate-500 px-1 mt-1">
                                        <span>1</span>
                                        <span>{{ max(array_column($this->originalClassesData, 'count')) }}</span>
                                    </div>
                                </div>
                                <div class="w-16">
                                    <input
                                        type="number"
                                        min="1"
                                        max="{{ max(array_column($this->originalClassesData, 'count')) }}"
                                        wire:model.live="maxAnnotations"
                                        class="w-full bg-slate-700 border border-slate-600 rounded px-2 py-1 text-sm text-green-400 font-medium focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                id="randomizeAnnotations"
                                wire:model="randomizeAnnotations"
                                class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-purple-500 focus:ring-purple-500 focus:ring-opacity-25 focus:ring-offset-0"
                            />
                            <label for="randomizeAnnotations" class="ml-2 text-sm text-gray-300">Randomize annotations</label>
                            <div class="ml-2 group relative">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-slate-500 cursor-help">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.94 6.94a.75.75 0 11-1.061-1.061 3 3 0 112.871 5.026v.345a.75.75 0 01-1.5 0v-.5c0-.72.57-1.172 1.081-1.287A1.5 1.5 0 108.94 6.94zM10 15a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                </svg>
                                <div class="absolute left-full ml-2 w-64 p-2 bg-slate-800 border border-slate-700 rounded text-xs text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">
                                    When enabled, annotations will be randomly selected if fewer than the total available are fetched. When disabled, annotations will be fetched in sequential order from the database.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Simple validation message using standard Livewire -->
                    @if($minAnnotations > $maxAnnotations)
                        <div class="mt-4">
                            <div class="text-red-400 text-xs">
                                Min annotations cannot be greater than max annotations.
                            </div>
                        </div>
                    @endif
                </div>


                <!-- Download Format Selection - Always visible -->
                <div class="relative w-48">
                    <select wire:model="exportFormat" class="w-full appearance-none px-3 py-1.5 pr-8 bg-slate-700 text-gray-300 text-sm rounded-lg border border-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500 hover:border-slate-500 transition-colors">
                        <option value="" disabled selected>Download Format</option>
                        @foreach(App\Configs\AppConfig::ANNOTATION_FORMATS_INFO as $format)
                            <option wire:key="download-annot-formtat{{$format['name']}}" value="{{ $format['name'] }}">{{ $format['name'] }}</option>
                        @endforeach
                    </select>

                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                <!-- Error Messages -->
                @error('exportFormat')
                <span class="w-full mx-auto text-red-500 text-sm mt-1 block">{{ $message }}</span>
                @enderror
                @error('token')
                <span class="w-full mx-auto text-red-500 text-sm mt-1 block">{{ $message }}</span>
                @enderror
                @error('locked')
                <span class="w-full mx-auto text-red-500 text-sm mt-1 block">{{ $message }}</span>
                @enderror
                @if(isset($this->failedDownload))
                    <x-dataset.dataset-errors
                        :errorMessage="$this->failedDownload['message']"
                        :errorData="$this->failedDownload['data']">
                    </x-dataset.dataset-errors>
                @endif

                <!-- Download Button - Always visible -->
                <x-misc.button wire:click="download" id="download-btn"
                               class="mt-2 w-48 mx-auto flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
                               :icon="@svg('eva-download')->toHtml()"
                >
                    Download Dataset
                </x-misc.button>

                <!-- Progress Indicator -->
                @if($this->locked)
                    <div wire:poll.1500ms="updateProgress" class="text-center text-sm text-gray-300">
                        <span>{{ $this->progress ?? null }}</span>
                    </div>
                @endif
            </div>
        @else
            @if($this->failedDownload)
                <x-dataset.dataset-errors
                    :errorMessage="$this->failedDownload['message']"
                    :errorData="$this->failedDownload['data']">
                </x-dataset.dataset-errors>
            @else
                <div class="max-w-5xl mx-auto p-6 bg-slate-800 rounded-lg space-y-4">
                    <span class="text-gray-300 text-base flex items-center">
                        <svg class="animate-spin h-5 w-5 mr-1 text-gray-300" viewBox="0 0 24 24" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4l-3 3-3-3h4z"></path>
                        </svg>
                    </span>
                </div>
            @endif
        @endif
    </x-modals.fixed-modal>
</div>
