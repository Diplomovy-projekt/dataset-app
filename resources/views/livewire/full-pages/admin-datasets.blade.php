<div x-data="datasetManagement(@this)"
    class="p-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-200">Dataset Management</h1>
        <div class="h-px flex-1 bg-gradient-to-r from-transparent via-slate-700 to-transparent mx-6"></div>
    </div>

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
                <livewire:forms.upload-dataset :key="'admin-datasets-new-dataset-upload'" :modalId="'uploadDataset'" :modalStyle="'new-upload'"/>
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
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                <tr>
                    <template x-for="header in headers" :key="header.field">
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-200">
                            <template x-if="header.sortable">
                                <button class="flex items-center gap-2 hover:text-blue-400 transition-colors"
                                        @click="sortBy(header.field)">
                                    <span x-text="header.label"></span>
                                    <span class="flex flex-col">
                            <svg :class="{ 'text-blue-400': sortField === header.field && sortDirection === 'asc' }"
                                 class="w-4 h-4 -mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                            </svg>
                            <svg :class="{ 'text-blue-400': sortField === header.field && sortDirection === 'desc' }"
                                 class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </span>
                                </button>
                            </template>
                            <template x-if="!header.sortable">
                                <span x-text="header.label"></span>
                            </template>
                        </th>
                    </template>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                @foreach($datasets as $dataset)
                    <tr wire:key="admin-dataset-management-{{ $dataset['id'] }}"
                        class="hover:bg-slate-750 transition-colors">
                        {{-- Display Name --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="bg-blue-500/10 p-2 rounded-lg">
                                    <x-icon name="o-folder" class="w-5 h-5 text-blue-400" />
                                </div>
                                <span class="text-gray-200">{{ $dataset['display_name'] }}</span>
                            </div>
                        </td>
                        {{-- Categories --}}
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-2">
                                @foreach($dataset['categories'] as $category)
                                    <span class="px-2 py-1 text-xs rounded-full bg-slate-700 text-gray-200">
                                        {{ $category['name'] }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        {{-- Annotation Technique --}}
                        <td class="px-6 py-4">
                            <x-dataset.annot_technique :annot_technique="$dataset['annotation_technique']" />
                        </td>
                        {{-- Owner --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="text-gray-200">{{ 'Owner' }}</span>
                            </div>
                        </td>
                        {{-- Visibility --}}
                        <td class="px-6 py-4">
                            <div x-data="{ isPublic: $wire.entangle('datasets.{{ $dataset['id'] }}.is_public') }">
                                <button
                                    class="px-3 py-1 rounded-full text-sm"
                                    :class="isPublic ? 'bg-green-500/10 text-green-400' : 'bg-slate-700 text-gray-400'"
                                    wire:click="toggleVisibility({{ $dataset['id'] }})">
                                    <span x-text="isPublic ? 'Public' : 'Private'"></span>
                                </button>
                            </div>
                        </td>
                        {{-- Pending Changes --}}
                        <td class="px-6 py-4">
                            @if(($dataset['pending_changes_count'] ?? 0) > 0)
                                <div class="flex items-center gap-2">
                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-500/10 text-yellow-400">
                                    {{ $dataset['pending_changes_count'] }} changes
                                </span>
                                </div>
                            @else
                                <span class="text-gray-500">No changes</span>
                            @endif
                        </td>
                        {{-- Actions --}}
                        <td class="px-6 py-4">
                            <livewire:forms.edit-dataset :key="'admin-datasets-edit-dataset'.$dataset['id']"  :editingDataset="$dataset['unique_name']"/>
                            <livewire:forms.extend-dataset :key="'admin-datasets-extend-dataset'.$dataset['id']"  :editingDataset="$dataset['unique_name']"/>
                            <x-dropdown-menu direction="left" class="w-50">
                                <x-dropdown-menu-item
                                    @click.prevent="open = 'extend-dataset'"
                                    :icon="@svg('eva-upload')->toHtml()">
                                    Extend Dataset
                                </x-dropdown-menu-item>
                                <x-dropdown-menu-item
                                    @click.prevent.stop="open = 'edit-dataset'"
                                    :icon="@svg('eos-edit')->toHtml()">
                                    Edit Dataset info
                                </x-dropdown-menu-item>

                                <x-dropdown-menu-item
                                    @click.prevent.stop="open = 'download-dataset'"
                                    :icon="@svg('eva-download')->toHtml()">
                                    Download Dataset
                                </x-dropdown-menu-item>

                                <div class="border-t border-gray-300"></div>

                                <x-dropdown-menu-item
                                    wire:click="deleteDataset({{ $dataset['id'] }})"
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
</div>

@script
<script>
    Alpine.data('datasetManagement', (wire) => ({
        open: '',
        sortField: null,
        sortDirection: 'asc',
        headers: [
            { label: 'Display Name', field: 'display_name', sortable: true },
            { label: 'Categories', field: 'categories', sortable: false },
            { label: 'Annotation Technique', field: 'annotation_technique', sortable: true },
            { label: 'Owner', field: 'owner', sortable: true },
            { label: 'Visibility', field: 'visibility', sortable: true },
            { label: 'Pending Changes', field: 'pending_changes', sortable: true },
            { label: 'Actions', field: 'actions', sortable: false },
        ],
        datasets: wire.entangle('datasets'),
        init() {

        },

        sortBy(field) {
            // Toggle sort direction if clicking the same field
            if (this.sortField === field) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortField = field;
                this.sortDirection = 'asc';
            }
            const rows = [...document.querySelectorAll('.dataset-row')];

            rows.sort((a, b) => {
                let aValue = a.querySelector(`[data-type="${field}"]`)?.dataset.value || '';
                let bValue = b.querySelector(`[data-type="${field}"]`)?.dataset.value || '';

                return this.sortDirection === 'asc'
                    ? aValue.localeCompare(bValue)
                    : bValue.localeCompare(aValue);
            });

            rows.forEach(row => row.parentElement.appendChild(row));
        }
    }));
</script>
@endscript
