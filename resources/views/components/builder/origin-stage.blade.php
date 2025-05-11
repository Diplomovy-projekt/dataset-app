<div>
    <div class="flex flex-wrap justify-around py-4 gap-4">
        @forelse($this->metadataValues as $data)
            <div wire:key="metadata-type-{{$data['type']['id']}}"
                 class="w-80" x-data="{ skip: @js(in_array($data['type']['id'], $this->skipTypes)) }">
                <div class="bg-gray-800 rounded-lg p-4 mb-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-200">{{$data['type']['name']}}</h2>
                        <label class="flex items-center space-x-3 text-gray-400 hover:text-gray-300 cursor-pointer"
                        title="Skipping is same as checking all options in the category">
                            <span class="text-sm">Skip</span>
                            <div class="relative inline-flex items-center">
                                <input
                                    type="checkbox"
                                    class="sr-only peer"
                                    x-model="skip"
                                    value="{{$data['type']['id']}}"
                                    wire:model.live="skipTypes"
                                >
                                <div
                                    class="w-9 h-5 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all"
                                    x-bind:class="skip ? 'bg-blue-500' : 'bg-gray-700'"
                                ></div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Values --}}
                <div class="space-y-2" x-show="!skip" x-transition>
                    @forelse($data['values'] as $value)
                        <label class="flex items-center cursor-pointer space-x-2 w-full">
                            <div wire:key="metadata-value-{{$value['id']}}"
                                 class="w-full group flex items-center justify-between p-3 bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors duration-200"
                                 x-data="{ selected: $wire.selectedMetadataValues[{{ $value['id'] }}] }"
                                 x-effect="selected = $wire.selectedMetadataValues[{{ $value['id'] }}]">

                                <span class="text-sm font-medium text-gray-200">{{ $value['value'] }}</span>
                                    <div class="relative h-5 w-5">
                                        <input type="checkbox"
                                               wire:model.live="selectedMetadataValues.{{ $value['id'] }}"
                                               class="peer h-5 w-5 cursor-pointer appearance-none rounded-lg border border-gray-500 checked:bg-green-500/10 checked:border-green-600" />

                                        <!-- Fade-in SVG -->
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="absolute h-3.5 w-3.5 text-green-500 left-1/2 top-1/2 opacity-0 transform -translate-x-1/2 -translate-y-1/2 transition-opacity duration-300 ease-in-out peer-checked:opacity-100"
                                             viewBox="0 0 24 24"
                                             fill="none"
                                             stroke="currentColor"
                                             stroke-width="3"
                                             stroke-linecap="round"
                                             stroke-linejoin="round">
                                            <polyline points="4 12 10 18 20 6" />
                                        </svg>
                                    </div>
                            </div>
                        </label>
                    @empty
                        <p class="text-gray-400 text-center py-4">No values available for {{$data['type']['name']}}</p>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="w-full text-center py-8">
                <p class="text-gray-400">No metadata available for datasets matching the selected categories.</p>
            </div>
        @endforelse
    </div>

    {{-- Results Counter --}}
    <div class="bg-gray-800 rounded-lg p-4 mt-4">
        <div class="flex items-center justify-between text-gray-200">
            <span class="text-sm">Matching Datasets</span>
            <span class="text-2xl font-bold text-blue-500">{{ count($this->datasetIds) }}</span>
        </div>
        <p class="text-sm text-gray-400 mt-1">
            Datasets matching any of the selected values or those without metadata will be included.
        </p>
    </div>
</div>
