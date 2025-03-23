<div class="text-gray-200"
     x-data="{open: ''}">

    <livewire:components.resolve-request :key="'resolve-request-component'" lazy/>

    {{-- Header --}}
    <x-misc.highlight-card
        color="purple"
        title="Edit Dataset Info Review"
        description="Review changes requested by the user to edit the dataset info and resolve the request."
    >
        <x-slot:icon>
            <x-myicon name="edit-dataset" color="text-purple-500"/>
        </x-slot:icon>
        <x-misc.button
            color="purple"
            variant="primary"
            size="lg"
            @click="$dispatch('init-resolve-request',{requestId: '{{ $requestId }}'});
                         open = 'resolve-request'">
            Resolve
        </x-misc.button>
    </x-misc.highlight-card>

    <div class="container mx-auto px-4 pb-10 mt-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{--Current Dataset Info Column--}}
            <div class=" rounded-xl p-6 border border-slate-700  shadow-lg">
                <div class="flex items-center mb-6">
                    <div class="w-4 h-4 bg-blue-500 rounded-full mr-3"></div>
                    <h3 class="text-xl font-bold text-white">Current Dataset</h3>
                </div>

                {{--Dataset Name--}}
                <div class="mb-5">
                    <span class="text-sm font-medium text-gray-400">Dataset Name</span>
                    <div class="p-3 border border-slate-700 rounded-md mt-2 text-white">
                        {{ $currentDataset['display_name'] }}
                    </div>
                </div>

                {{--Categories--}}
                <div class="mb-5">
                    <span class="text-sm font-medium text-gray-400">Categories</span>
                    <div class="p-3 border border-slate-700 rounded-md mt-2 flex flex-wrap gap-2">
                        @foreach($currentDataset['categories'] as $category)
                            <span class="badge border border-blue-600 text-white px-3 py-1.5 rounded-full text-xs">{{ $category['name'] }}</span>
                        @endforeach
                    </div>
                </div>

                {{--Metadata--}}
                <div class="mb-5">
                    <span class="text-sm font-medium text-gray-400">Metadata</span>
                    @foreach($currentDataset['metadata'] as $group)
                        <div class="mt-3 border border-slate-700 rounded-md p-3">
                            <div class="font-medium text-white">{{ $group['name'] }}</div>
                            <div class="flex flex-wrap gap-2 mt-2">
                                @foreach($group['metadataValues'] as $value)
                                    <span class="badge bg-[#344156] text-gray-300 border-0 px-3 py-1.5 rounded-full text-xs">{{ $value['value'] }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{--Description--}}
                <div class="mb-5">
                    <span class="text-sm font-medium text-gray-400">Description</span>
                    <div class="p-3 border border-slate-700 rounded-md mt-2 whitespace-pre-line text-gray-300">
                        {{ $currentDataset['description'] }}
                    </div>
                </div>
            </div>

            {{--Requested Changes Column--}}
            <div class=" rounded-xl p-6 border border-purple-500/30 shadow-lg relative">

                <div class="flex items-center mb-6">
                    <div class="w-4 h-4 bg-purple-500 rounded-full mr-3"></div>
                    <h3 class="text-xl font-bold text-white">Requested Changes</h3>
                </div>

                {{--Dataset Name--}}
                <div class="mb-5">
                    <span class="text-sm font-medium text-gray-400">Dataset Name</span>
                    <div class="rounded-md mt-2"
                         :class="{ 'border-l-4 border-purple-500': '{{ $currentDataset['display_name'] !== $requestDataset['display_name'] }}' }">
                        <div class="p-3 border border-slate-700 text-white rounded-md">
                            {{ $requestDataset['display_name'] }}
                        </div>
                    </div>


                </div>

                {{--Categories--}}
                <div class="mb-5">
                    <span class="text-sm font-medium text-gray-400">Categories</span>
                    <div class="relative"
                         x-data="{
                                    currentIds: @json(array_column($currentDataset['categories'], 'id')),
                                    newIds: @json(array_column($requestDataset['categories'], 'id')),
                                    hasChanges() {
                                        return this.currentIds.length !== this.newIds.length ||
                                               !this.currentIds.every(id => this.newIds.includes(id));
                                    }
                                 }">
                        {{--Border div (only if there are changes)--}}
                        <div x-cloak x-show="hasChanges()" class="absolute left-0 top-0 h-full w-1 bg-purple-500 rounded-l-md"></div>

                        {{--Main container--}}
                        <div class="p-3 border border-slate-700 rounded-md mt-2 flex flex-wrap gap-2"
                             >

                            @foreach($requestDataset['categories'] as $category)
                                <span class="badge text-white text-xs px-3 py-1.5 rounded-full"
                                      :class="currentIds.includes({{ $category['id'] }})
                            ? 'bg-transparent border border-blue-500'
                            : 'bg-blue-600 border-0'">
                {{ $category['name'] }}
            </span>
                            @endforeach

                        </div>
                    </div>

                </div>

                {{--Metadata--}}
                <div class="mb-5">
                    <span class="text-sm font-medium text-gray-400">Metadata</span>
                    @foreach($requestDataset['metadata'] as $group)
                        @php
                            $currentGroup = collect($currentDataset['metadata'])->firstWhere('id', $group['id']);
                            $currentValuesIds = $currentGroup ? array_column($currentGroup['metadataValues'], 'id') : [];
                            $newValuesIds = array_column($group['values'], 'id');
                        @endphp

                        <div class="relative"
                             x-data="{
                                    currentValues: @json($currentValuesIds),
                                    newValues: @json($newValuesIds),
                                    hasChanges() {
                                        return this.currentValues.length !== this.newValues.length ||
                                               !this.currentValues.every(id => this.newValues.includes(id));
                                    }
                                 }">
                            {{--Border div (only if there are changes)--}}
                            <div x-cloak x-show="hasChanges()" class="absolute left-0 top-0 h-full w-1 bg-purple-500 rounded-l-md"></div>

                            {{--Main container--}}
                            <div class="mt-3 border border-slate-700 rounded-md p-3"
                                 >
                                <div class="font-medium text-white">{{ $group['name'] }}</div>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    @foreach($group['values'] as $value)
                                        <span class="badge text-xs px-3 py-1.5 rounded-full"
                                              :class="currentValues.includes({{ $value['id'] }})
                                                    ? 'bg-[#344156] text-gray-300 border-0'
                                                    : 'bg-blue-600 text-white border-0'">
                                            {{ $value['value'] }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{--Description--}}
                <div class="mb-5">
                    <span class="text-sm font-medium text-gray-400">Description</span>
                    <div class="relative"
                         x-data="{
                            currentDesc: {{ json_encode($currentDataset['description']) }},
                            newDesc: {{ json_encode($requestDataset['description']) }},
                            hasChanges() {
                                return this.currentDesc !== this.newDesc;
                            }
                         }">
                        <div x-cloak x-show="hasChanges()" class="absolute left-0 top-0 h-full w-1 bg-purple-500 rounded-l-md"></div>
                        <div class="p-3 border border-slate-700 rounded-md mt-2 whitespace-pre-line text-gray-300">
                            {{ $requestDataset['description'] }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
