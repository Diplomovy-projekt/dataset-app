<div x-data="adminDashboard(@this)" class="p-6">
    <!-- Header Section -->

    <x-misc.header title="System Statistics"/>

    <!-- Stats Grid -->
    <div class="flex justify-between gap-3">
        @foreach([
            ['icon' => 'o-users', 'color' => 'blue', 'title' => 'Active Users', 'value' => $this->userCount],
            ['icon' => 'o-squares-2x2', 'color' => 'purple', 'title' => 'Total Datasets', 'value' => $this->datasetCount],
            ['icon' => 'o-server', 'color' => 'yellow', 'title' => 'Dataset Storage', 'value' => "$totalStorage GB", 'extra' => 'Total space used']
        ] as $stat)
            <div class="bg-slate-800 rounded-lg p-4 flex items-center gap-4 flex-1">
                <div class="bg-{{ $stat['color'] }}-500/10 p-3 rounded-lg">
                    <x-icon name="{{ $stat['icon'] }}" class="w-6 h-6 text-{{ $stat['color'] }}-400" />
                </div>
                <div class="flex flex-col">
                    <h3 class="text-gray-200 text-sm font-semibold">{{ $stat['title'] }}</h3>
                    <p class="text-3xl font-bold text-gray-200">{{ $stat['value'] }}</p>
                    @isset($stat['extra'])
                        <p class="text-xs text-gray-400">{{ $stat['extra'] }}</p>
                    @endisset
                </div>
            </div>
        @endforeach
    </div>



    <!-- Metadata Management Section -->
    <div class="space-y-6">
        <!-- Section Header -->
        <x-misc.header title="Metadata Management"/>

        <div class="bg-gradient-to-r from-slate-800 to-slate-900 p-4 border-b border-slate-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-500 p-2 rounded-lg">
                        <x-icon name="o-squares-2x2" class="w-5 h-5 text-gray-200" />
                    </div>
                    <h2 class="text-xl font-bold text-gray-200">Metadata</h2>
                </div>
                <button @click="open = open === 'new-type' ? '' : 'new-type'"
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                    <div class="flex items-center gap-2">
                        <x-icon name="o-plus" class="w-4 h-4" />
                        <span>New Metadata</span>
                    </div>
                </button>
            </div>
        </div>
        <!-- Metadata Types List -->
        <div class="space-y-3">

                <!-- New Type Input (Initially Hidden) -->
            <div
                x-show="open == 'new-type'"
                x-transition
                class="bg-slate-800 rounded-xl p-4 border border-blue-500/50">
                <div class="flex items-center gap-3">
                    <input
                        type="text"
                        x-model="typeName"
                        @keydown.enter="open = ''"
                        @keydown.escape="open = ''"
                        placeholder="Enter type name..."
                        class="flex-1 bg-slate-800/50 border border-slate-600 rounded-lg px-4 py-2 text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:bg-slate-800"
                        >

                    <button
                        @click="open = ''; saveType(typeName, null)"
                        class="p-2 text-gray-400 hover:text-gray-200">
                        <x-icon name="o-check" class="w-5 h-5" />
                    </button>
                    <button
                        @click="open = ''"
                        class="p-2 text-gray-400 hover:text-gray-200">
                        <x-icon name="o-x-mark" class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <!-- Existing Types -->
            @foreach($this->metadata as $type)
                <div
                    class="bg-slate-800 rounded-xl overflow-hidden"
                    x-data="{
                    expanded: false,
                    isAddingValue: false,
                    newValue: '',
                    typeName: '{{ $type['name'] }}'
                }">
                    <!-- Type Header (Collapsed State) -->
                    <div class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between hover:bg-slate-700/50 transition-colors">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3 cursor-pointer" @click="expanded = !expanded">
                            <div class="bg-purple-500/10 p-2 rounded-lg">
                                <x-icon
                                    name="o-chevron-right"
                                    class="w-5 h-5 text-purple-400 transition-transform"
                                    ::class="{ 'rotate-90': expanded }" />
                            </div>
                            <!-- Type Name (Editable) -->
                            <input
                                type="text"
                                x-model="typeName"
                                @keydown.enter="saveType(typeName, {{ $type['id'] }})"
                                @blur="saveType(typeName, {{ $type['id'] }})"
                                class=" sm:w-fit text-xl font-bold text-gray-300 bg-slate-800/50 border border-slate-600 rounded-lg px-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:bg-slate-800"
                                >
                            <span class="text-sm text-gray-400">({{ count($type['metadata_values']) }} values)</span>
                        </div>
                        <!-- Delete Type Button -->
                        <button
                            @click="typeDelete = {{ $type['id'] }}; open = 'delete-type-modal'"
                            class="p-2 text-red-400 hover:text-red-300 transition-colors">
                            <x-icon name="o-trash" class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Values Section (Expanded State) -->
                    <div
                        x-show="expanded"
                        x-collapse
                        class="border-t border-slate-700">
                        <!-- Add Value Input -->
                        <div class="p-4 bg-slate-800/50">
                            <div
                                x-show="isAddingValue"
                                class="flex items-center gap-2">
                                <input
                                    type="text"
                                    x-model="newValue"
                                    @keydown.escape="isAddingValue = false"
                                    @click.outside="isAddingValue = false"
                                    placeholder="Enter new value..."
                                    class="flex-1 bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                <button
                                    @click="isAddingValue = false; saveValue({{ $type['id'] }}, newValue)"
                                    class="p-2 text-gray-400 hover:text-gray-200">
                                    <x-icon name="o-check" class="w-5 h-5" />
                                </button>
                                <button
                                    @click="isAddingValue = false"
                                    class="p-2 text-gray-400 hover:text-gray-200">
                                    <x-icon name="o-x-mark" class="w-5 h-5" />
                                </button>
                            </div>
                            <button
                                x-show="!isAddingValue"
                                @click="isAddingValue = true"
                                class="text-sm text-blue-400 hover:text-blue-300 flex items-center gap-2">
                                <x-icon name="o-plus" class="w-4 h-4" />
                                Add New Value
                            </button>
                        </div>

                        <!-- Values List -->
                        <div class="grid sm:grid-cols-2">
                            @foreach($type['metadata_values'] as $index => $value)
                                <div
                                    class="p-4 flex items-center justify-between hover:bg-slate-700/50 group"
                                    x-data="{valueText: '{{ $value['value'] }}'}">
                                    <!-- Value Text (Editable) -->
                                    <input
                                        type="text"
                                        x-model="valueText"
                                        @keydown.enter="saveValue({{ $type['id'] }}, valueText, {{ $value['id'] }})"
                                        @blur="saveValue({{ $type['id'] }}, valueText, {{ $value['id'] }})"
                                        class="w-full bg-slate-800/50 border border-slate-600 rounded-lg px-3 py-1 text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:bg-slate-800"
                                        >
                                    <!-- Action Buttons -->
                                    <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button
                                            @click="deleteValue({{ $value['id'] }})"
                                            class="p-1.5 text-red-400 hover:text-red-300">
                                            <x-icon name="o-trash" class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Delete Confirmation Modal -->
        <x-modals.fixed-modal modalId="delete-type-modal">
            <div class="p-6 space-y-4">
                <div class="flex items-center gap-3 text-red-400">
                    <x-icon name="o-exclamation-triangle" class="w-6 h-6" />
                    <h3 class="text-xl font-bold">Delete Metadata Type</h3>
                </div>
                <p class="text-gray-300">This will delete all associated values. Also dataset metadata. This action can not be undone</p>
                <div class="flex justify-end gap-3 mt-6">
                    <button
                        @click="open = ''"
                        class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-gray-200 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button
                        @click="deleteType(); open = ''"
                        class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors">
                        Delete Type
                    </button>
                </div>
            </div>
        </x-modals.fixed-modal>
    </div>
</div>

@script
<script>
    Alpine.data('adminDashboard', (wire) => ({
        // Modal
        open: '',
        // Type Management
        typeName: '',
        typeDelete: null,
        saveType(name, id) {
            name = name.trim();
            if(name === ''){
                window.dispatchEvent(new CustomEvent('flash-msg', {
                    detail: { type: 'error', message: 'Type name cannot be empty' }
                }));
                return;
            }
            $wire.saveType(name, id);
        },
        saveValue(typeId, value, valueId) {
            value = value.trim();
            if(value === ''){
                window.dispatchEvent(new CustomEvent('flash-msg', {
                    detail: { type: 'error', message: 'Value cannot be empty' }
                }));
                return;
            }
            $wire.saveValue(typeId, value, valueId);
        },
        deleteType() {
            if(!this.typeDelete){
                window.dispatchEvent(new CustomEvent('flash-msg', {
                    detail: { type: 'error', message: 'Type ID not found' }
                }));
                return;
            }
            $wire.deleteType(this.typeDelete);
        },
        deleteValue(valueId) {
            if(!valueId){
                window.dispatchEvent(new CustomEvent('flash-msg', {
                    detail: { type: 'error', message: 'Value ID not found' }
                }));
                return;
            }
            $wire.deleteValue(valueId);
        }
    }));
</script>
@endscript
