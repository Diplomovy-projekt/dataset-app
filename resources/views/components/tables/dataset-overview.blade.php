@props([
    'table' => [],
    'items'
])
<x-tables.wrapper :table="$table">
    @foreach($this->{$items} as $dataset)
        <x-tables.table-row id="admin-dataset-management-{{ $dataset['id'] }}">
            {{-- Display name --}}
            <x-tables.table-cell>
                <div class="flex items-center gap-3">
                    <div class="bg-blue-500/10 p-2 rounded-lg">
                        <x-icon name="o-folder" class="w-5 h-5 text-blue-400" />
                    </div>
                    <a href="{{ route('dataset.show', ['uniqueName' => $dataset['unique_name']])}}"
                       wire:navigate
                       class="text-gray-200">
                        {{ $dataset['display_name'] }}</a>
                </div>
            </x-tables.table-cell>
            {{-- Categories --}}
            <x-tables.table-cell>
                <div class="flex flex-wrap gap-2">
                    @foreach($dataset['categories'] as $category)
                        <span class="px-2 py-1 text-xs rounded-full bg-slate-700 text-gray-200">
                            {{ $category['name'] }}
                        </span>
                    @endforeach
                </div>
            </x-tables.table-cell>
            {{-- Annotation technique --}}
            <x-tables.table-cell>
                <x-dataset.annot_technique :annot_technique="$dataset['annotation_technique']" />
            </x-tables.table-cell>
            {{-- Owner column --}}
            <x-tables.table-cell>
                <button @click="open = 'change-owner'; datasetId = '{{ $dataset['id'] }}';"
                        class="flex items-center gap-2 text-gray-200 hover:text-blue-400 transition-colors">
                    <span>{{ $dataset['user']['email'] }}</span>
                    <x-icon name="o-chevron-down" class="w-4 h-4" />
                </button>

            </x-tables.table-cell>
            {{-- Visibility --}}
            <x-tables.table-cell>
                <div x-data="{ isPublic: {{ $dataset->is_public ? 'true' : 'false' }} }">
                    <button class="px-3 py-1 rounded-full text-sm"
                            :class="isPublic ? 'bg-green-500/10 text-green-400' : 'bg-slate-700 text-gray-400'"
                            @click="isPublic = !isPublic; $wire.toggleVisibility({{ $dataset->id }})">
                        <span x-text="isPublic ? 'Public' : 'Private'"></span>
                    </button>
                </div>
            </x-tables.table-cell>
            {{-- Pending changes --}}
            <x-tables.table-cell>
                @if(($dataset['pending_changes'] ?? 0) > 0)
                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-500/10 text-yellow-400">
                                            {{ $dataset['pending_changes'] }} changes
                                        </span>
                @else
                    <span class="text-gray-500">-</span>
                @endif
            </x-tables.table-cell>
            {{-- Actions --}}
            <x-tables.table-cell>
                <x-dropdown-menu direction="left" class="w-50">
                    <x-dropdown-menu-item
                        @click="$dispatch('extend-selected',{uniqueName: '{{ $dataset['unique_name'] }}'});
                                open = 'extend-dataset'"
                        :icon="@svg('eva-upload')->toHtml()">
                        Extend Dataset
                    </x-dropdown-menu-item>

                    <x-dropdown-menu-item
                        @click="$dispatch('edit-selected',{uniqueName: '{{ $dataset['unique_name'] }}'});
                                open = 'edit-dataset'"
                        :icon="@svg('eos-edit')->toHtml()">
                        Edit Dataset info
                    </x-dropdown-menu-item>

                    <x-dropdown-menu-item
                        @click.prevent="open = 'display-classes'"
                        :icon="@svg('o-tag')->toHtml()">
                        Preview Classes
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
            </x-tables.table-cell>
        </x-tables.table-row>
    @endforeach
</x-tables.wrapper>
