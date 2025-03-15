<div x-data="datasetManagement(@this)" class="p-6">

    {{-- Livewire component modals --}}
    <livewire:forms.upload-dataset :modalId="'uploadDataset'" :modalStyle="'new-upload'"/>
    <livewire:forms.edit-dataset :key="'admin-datasets-edit-dataset'" />
    <livewire:forms.extend-dataset :key="'admin-datasets-extend-dataset'" />
    <livewire:components.download-dataset :key="'admin-datasets-download-dataset'" />
    <livewire:components.resolve-request :key="'resolve-request-component'" />

    <!-- Header Section -->
    <x-misc.header title="Dataset Management"/>

    <!-- Table Section -->
    <div class="bg-slate-800 rounded-xl overflow-hidden">
        <!-- Table Header -->
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 p-4 border-b border-slate-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-500 p-2 rounded-lg">
                        <x-icon name="o-squares-2x2" class="w-5 h-5 text-gray-200" />
                    </div>
                    <h2 class="text-xl font-bold text-gray-200">Datasets</h2>
                </div>
                <button @click.prevent="open = 'uploadDataset'"
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                    <div class="flex items-center gap-2">
                        <x-icon name="o-plus" class="w-4 h-4" />
                        <span>New Dataset</span>
                    </div>
                </button>
            </div>
        </div>
    </div>

    {{--Tables --}}
    <x-tables.tabs :tables="$tables" />

    {{-- Update dataset owner modal --}}
    <x-modals.fixed-modal modalId="change-owner" class="w-fit">
        <div class="p-4">
            {{-- Header --}}
            <x-misc.header title="Change Dataset Ownership" info="Select a new owner before deleting the user. The dataset will be transferred automatically."/>

            {{-- Search input --}}
            <div class="relative mb-3">
                <input type="text"
                       wire:model.live="userSearchTerm"
                       wire:keyup.debounce.500ms="searchUsers"
                       placeholder="Search users..."
                       class="w-full px-3 py-2 bg-slate-700 rounded-md text-gray-200 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            {{-- Users list --}}
            <div class="mb-3 p-3 pl-0 border-b border-slate-700 bg-slate-800 rounded-md">
                {{-- Current User Option --}}
                <label class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors cursor-pointer font-medium text-gray-300 bg-blue-700/10 border border-blue-500">
                    <input type="radio" x-model="newOwnerId" value="{{ auth()->id() }}" class="form-radio text-blue-500">
                    <div class="bg-blue-600 p-1.5 rounded-full">
                        <x-icon name="o-user" class="w-4 h-4 text-white" />
                    </div>
                    <span class="text-white font-semibold text-sm">Current User (You)</span>
                </label>
            </div>
            <div class="max-h-48 overflow-y-auto space-y-1">
                @foreach($users as $user)
                    @if($user['id'] !== auth()->id())
                        <label class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-3 py-2 hover:bg-slate-700 rounded-md transition-colors cursor-pointer">
                            <div class="flex items-center gap-3">
                                <input type="radio" x-model="newOwnerId" value="{{ $user['id'] }}" class="form-radio text-blue-500">
                                <div class="bg-slate-600 p-1.5 rounded-full">
                                    <x-icon name="o-user" class="w-4 h-4 text-gray-300" />
                                </div>
                                <div>
                                    <span class="text-white font-medium text-sm">{{ $user['name'] }}</span>
                                    <p class="text-gray-400 text-xs">{{ $user['email'] }}</p>
                                </div>
                            </div>
                            <div class="w-fit px-2 py-0.5 text-xs font-semibold rounded-md"
                                 :class="{ 'bg-red-600 text-white': {{ $user['role'] === 'admin' ? 'true' : 'false' }}, 'bg-gray-600 text-gray-200': {{ $user['role'] === 'user' ? 'true' : 'false' }} }">
                                {{ ucfirst($user['role']) }}
                            </div>
                        </label>
                    @endif
                @endforeach

            </div>

            <div class="mt-3 text-right">
                <x-misc.button @click="$wire.changeOwner(datasetId, newOwnerId)" color="blue" size="sm">
                    Update owner
                </x-misc.button>
                <x-misc.button @click="open = false" color="gray" size="sm">
                    Cancel
                </x-misc.button>
            </div>
        </div>
    </x-modals.fixed-modal>
</div>

@script
<script>
    Alpine.data('datasetManagement', (wire) => ({
        open: '',
        newOwnerId: '{{ auth()->id() }}',
        datasetId: '',
        init() {

        }
    }));
</script>
@endscript
