@props([
    'metadataValues'
])
<div class="flex flex-wrap justify-around p-4">
    @foreach($metadataValues as $data)
        <div class="w-80" x-data="{ skip: false }">
            <div class="bg-gray-800 rounded-lg p-4 mb-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-200">{{$data['type']['name']}}</h2>
                    <label class="flex items-center space-x-3 text-gray-400 hover:text-gray-300 cursor-pointer">
                        <span class="text-sm">Skip</span>
                        <div class="relative inline-flex items-center">
                            <input
                                type="checkbox"
                                class="sr-only peer"
                                x-model="skip"
                                value="{{$data['type']['id']}}"
                                wire:model="skipTypes"
                            >
                            <div class="w-9 h-5 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                        </div>
                    </label>
                </div>
            </div>
            <div class="space-y-3" x-show="!skip" x-transition>
                @forelse($data['values'] as $value)
                    <div class="bg-gray-700 rounded-lg p-3 flex items-center justify-between" x-data="{ selection: $wire.selectedMetadataValues[{{ $value['id'] }}] }" x-effect="selection = $wire.selectedMetadataValues[{{ $value['id'] }}]">
                        <span class="text-sm font-medium text-gray-200">{{ $value['value'] }}</span>

                        <div class="flex gap-2">
                            <!-- Include Option -->
                            <label>
                                <input
                                    type="radio"
                                    name="filter_{{ $value['id'] }}"
                                    wire:model="selectedMetadataValues.{{ $value['id'] }}"
                                    value="true"
                                    class="sr-only peer"
                                >
                                <div class="w-8 h-8 flex items-center justify-center rounded-md border-2 border-gray-500 peer-checked:border-green-500 peer-checked:bg-green-500/10 hover:bg-gray-600 cursor-pointer transition-all duration-300"
                                     :class="{'opacity-30 hover:bg-transparent cursor-default': selection === 'exclude'}">
                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                         :class="{'text-gray-400': selection === 'exclude'}">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </label>

                            <!-- Exclude Option -->
                            <label>
                                <input
                                    type="radio"
                                    name="filter_{{ $value['id'] }}"
                                    wire:model="selectedMetadataValues.{{ $value['id'] }}"
                                    value="false"
                                    class="sr-only peer"
                                >
                                <div class="w-8 h-8 flex items-center justify-center rounded-md border-2 border-gray-500 peer-checked:border-red-500 peer-checked:bg-red-500/10 hover:bg-gray-600 cursor-pointer transition-all duration-300"
                                     :class="{'opacity-30 hover:bg-transparent cursor-default': selection === 'include'}">
                                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                         :class="{'text-gray-400': selection === 'include'}">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                            </label>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400">No data found for {{$data['type']['name']}}</p>
                @endforelse
            </div>
        </div>
    @endforeach
</div>
