<div x-data="datasetManagement(@this)" class="p-6">

    <livewire:forms.edit-dataset :key="'admin-datasets-edit-dataset'" />
    <livewire:forms.extend-dataset :key="'admin-datasets-extend-dataset'" />
    <livewire:components.download-dataset :key="'admin-datasets-download-dataset'" />

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
                <livewire:forms.upload-dataset :modalId="'uploadDataset'" :modalStyle="'new-upload'"/>
                <button @click.prevent="open = 'uploadDataset'"
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                    <div class="flex items-center gap-2">
                        <x-icon name="o-plus" class="w-4 h-4" />
                        <span>New Dataset</span>
                    </div>
                </button>
            </div>
        </div>

        <!-- Table Content -->
        <div class="w-full overflow-x-auto">
            <table class="table-auto w-full border-collapse">
                <thead x-data="{ sortField: $wire.entangle('sortColumn'), sortDirection: $wire.entangle('sortDirection')}">
                <tr>
                    @foreach($headers as $header)
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-200 {{ $header['width'] }}">
                            @if($header['sortable'])
                                <button class="flex items-center gap-2 hover:text-blue-400 transition-colors"
                                        wire:click="sortBy('{{ $header['field'] }}')">
                                    {{ $header['label'] }}
                                    <span class="flex flex-col">
                                            <svg class="w-4 h-4 -mb-1"
                                                 :class="{ 'text-blue-400': sortField === '{{ $header['field'] }}' && sortDirection === 'asc' }"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                            </svg>
                                            <svg class="w-4 h-4"
                                                 :class="{ 'text-blue-400': sortField === '{{ $header['field'] }}' && sortDirection === 'desc' }"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </span>
                                </button>
                            @else
                                {{ $header['label'] }}
                            @endif
                        </th>
                    @endforeach
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                @foreach($this->paginatedDatasets as $dataset)
                    <tr wire:key="admin-dataset-management-{{ $dataset['id'] }}"
                        class="hover:bg-slate-750 transition-colors">
                        {{-- Display name --}}
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3">
                                <div class="bg-blue-500/10 p-2 rounded-lg">
                                    <x-icon name="o-folder" class="w-5 h-5 text-blue-400" />
                                </div>
                                <a href="{{ route('dataset.show', ['uniqueName' => $dataset['unique_name']])}}"
                                   wire:navigate
                                   class="text-gray-200">
                                    {{ $dataset['display_name'] }}</a>
                            </div>
                        </td>
                        {{-- Categories --}}
                        <td class="px-6 py-3">
                            <div class="flex flex-wrap gap-2">
                                @foreach($dataset['categories'] as $category)
                                    <span class="px-2 py-1 text-xs rounded-full bg-slate-700 text-gray-200">
                                            {{ $category['name'] }}
                                        </span>
                                @endforeach
                            </div>
                        </td>
                        {{-- Annotation technique --}}
                        <td class="px-6 py-3">
                            <x-dataset.annot_technique :annot_technique="$dataset['annotation_technique']" />
                        </td>
                        {{-- Owner column --}}
                        <td class="px-6 py-3">
                            <div x-data="{ open: '' }">
                                <button @click="open = 'change-owner'"
                                        class="flex items-center gap-2 text-gray-200 hover:text-blue-400 transition-colors">
                                    <span>{{ $dataset['user']['email'] }}</span>
                                    <x-icon name="o-chevron-down" class="w-4 h-4" />
                                </button>

                                {{-- Modal --}}
                                <x-modals.fixed-modal modalId="change-owner">
                                    <div class="p-4">
                                        {{-- Header --}}
                                        <x-misc.header title="Change Dataset Owner" info="Select a new owner for the dataset." />

                                        {{-- Search input --}}
                                        <div class="relative mb-3">
                                            <input type="text"
                                                   wire:model.live="userSearchTerm"
                                                   wire:keyup.debounce.500ms="searchUsers"
                                                   placeholder="Search users..."
                                                   class="w-full px-3 py-2 bg-slate-700 rounded-md text-gray-200 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                        </div>

                                        {{-- Users list --}}
                                        <div class="max-h-48 overflow-y-auto space-y-1">
                                            @foreach($users as $user)
                                                <label class="flex items-center justify-between gap-3 px-3 py-2 hover:bg-slate-700 rounded-md transition-colors cursor-pointer">
                                                    <button wire:click="changeOwner({{ $dataset['id'] }}, {{ $user['id'] }})"
                                                            @click="open = false"
                                                            class="w-full flex items-center gap-3 justify-between text-left p-2 hover:bg-slate-700 rounded-md transition-colors">

                                                        <!-- User Details -->
                                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                                            <div class="bg-slate-600 p-1.5 rounded-full flex-shrink-0">
                                                                <x-icon name="o-user" class="w-4 h-4 text-gray-300" />
                                                            </div>
                                                            <div class="flex flex-col min-w-0">
                                                                <span class="text-white font-medium text-sm truncate">{{ $user['name'] }}</span>
                                                                <p class="text-gray-400 text-xs truncate">{{ $user['email'] }}</p>
                                                                @if($user['id'] === $dataset['user']['id'])
                                                                    <span class="text-xs text-blue-400">Current owner</span>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <!-- Role Tag -->
                                                        <span class="flex-shrink-0 text-right px-2 py-0.5 text-xs font-semibold rounded-md w-fit inline-block"
                                                              :class="{ 'bg-blue-600 text-white': @json($user['role'] === 'admin'),
                                                                        'bg-gray-600 text-gray-200': @json($user['role'] === 'user') }">
                                                            {{ ucfirst($user['role']) }}
                                                        </span>

                                                    </button>

                                                </label>
                                            @endforeach
                                        </div>

                                        {{-- Close Button --}}
                                        <div class="mt-3 text-right">
                                            <x-misc.button @click="open = false" color="gray" size="sm">
                                                Cancel
                                            </x-misc.button>
                                        </div>
                                    </div>
                                </x-modals.fixed-modal>
                            </div>
                        </td>
                        {{-- Visibility --}}
                        <td class="px-6 py-3">
                            <div x-data="{ isPublic: {{ $dataset->is_public ? 'true' : 'false' }} }">
                                <button class="px-3 py-1 rounded-full text-sm"
                                        :class="isPublic ? 'bg-green-500/10 text-green-400' : 'bg-slate-700 text-gray-400'"
                                        @click="isPublic = !isPublic; $wire.toggleVisibility({{ $dataset->id }})">
                                    <span x-text="isPublic ? 'Public' : 'Private'"></span>
                                </button>
                            </div>
                        </td>
                        {{-- Pending changes --}}
                        <td class="px-6 py-3">
                            @if(($dataset['pending_changes'] ?? 0) > 0)
                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-500/10 text-yellow-400">
                                        {{ $dataset['pending_changes'] }} changes
                                    </span>
                            @else
                                <span class="text-gray-500">-</span>
                            @endif
                        </td>
                        {{-- Actions --}}
                        <td class="px-6 py-3">
                            <x-dropdown-menu direction="left" class="w-50">
                                <x-dropdown-menu-item
                                    @click="$dispatch('extend-selected','{{ $dataset['unique_name'] }}'); open = 'extend-dataset'"
                                    :icon="@svg('eva-upload')->toHtml()">
                                    Extend Dataset
                                </x-dropdown-menu-item>

                                <x-dropdown-menu-item
                                    @click="$dispatch('edit-selected','{{ $dataset['unique_name'] }}'); open = 'edit-dataset'"
                                    :icon="@svg('eos-edit')->toHtml()">
                                    Edit Dataset info
                                </x-dropdown-menu-item>

                                <x-dropdown-menu-item
                                    @click="$wire.cacheQuery('{{$dataset['id']}}'); open = 'download-dataset'"
                                    :icon="@svg('eva-download')->toHtml()">
                                    Download Dataset
                                </x-dropdown-menu-item>

                                <div class="border-t border-gray-300"></div>

                                <x-dropdown-menu-item
                                    wire:click="deleteDataset('{{ $dataset['unique_name'] }}')"
                                    wire:confirm="This will permanently delete the dataset"
                                    danger
                                    :icon="@svg('mdi-trash-can-outline')->toHtml()">
                                    Delete Dataset
                                </x-dropdown-menu-item>
                            </x-dropdown-menu>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $this->paginatedDatasets->links() }}
    </div>
</div>

@script
<script>
    Alpine.data('datasetManagement', (wire) => ({
        open: '',
        init() {

        }
    }));
</script>
@endscript
