<div>
    <div class="flex flex-wrap justify-around py-4 gap-4">
        @forelse($this->metadataValues as $data)
            <div wire:key="metadata-type-{{$data['type']['id']}}"
                 class="w-80" x-data="{ skip: false }">
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
                        <div wire:key="metadata-value-{{$value['id']}}"
                             class="group flex items-center justify-between p-3 bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors duration-200"
                             x-data="{ selected: $wire.selectedMetadataValues[{{ $value['id'] }}] }"
                             x-effect="selected = $wire.selectedMetadataValues[{{ $value['id'] }}]">

                            <span class="text-sm font-medium text-gray-200">{{ $value['value'] }}</span>

                            <label class="cursor-pointer flex items-center">
                                <input
                                    type="checkbox"
                                    wire:model.live="selectedMetadataValues.{{ $value['id'] }}"
                                    class="relative sr-only peer"
                                >
                                <div class="w-5 h-5 flex items-center justify-center rounded border-2 border-gray-500
                                    peer-checked:border-green-500 peer-checked:bg-green-500/10
                                    hover:bg-gray-500/20 transition-all duration-200">
                                </div>
                                <svg
                                    class="absolute w-5 h-5 text-green-500 opacity-0 peer-checked:opacity-100 transition-opacity duration-200"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5 13l4 4L19 7">
                                    </path>
                                </svg>
                            </label>
                        </div>
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
            <span class="text-2xl font-bold text-blue-500">{{ count($this->datasets) }}</span>
        </div>
        <p class="text-sm text-gray-400 mt-1">Datasets matching any of the selected values will be included</p>
    </div>
</div>
