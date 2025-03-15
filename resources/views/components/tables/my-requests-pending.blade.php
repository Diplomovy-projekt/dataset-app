@props([
    'table' => [],
    'items'
])
<x-tables.wrapper :table="$table">
    @foreach($this->{$items} as $request)
        <x-tables.table-row id="admin-dataset-pending-request-{{ $request['id'] }}">
            {{-- Dataset --}}
            <x-tables.table-cell>
                <div class="flex items-center gap-3">
                    <div class="bg-blue-500/10 p-2 rounded-lg">
                        <x-icon name="o-folder" class="w-5 h-5 text-blue-400" />
                    </div>
                    <a href="{{ route('dataset.show', ['uniqueName' => $request['dataset']['unique_name']])}}"
                       wire:navigate
                       class="text-gray-200">
                        {{ $request['dataset']['display_name'] }}</a>
                </div>
            </x-tables.table-cell>

            {{-- Type --}}
            <x-tables.table-cell>
                @php
                    $color = match($request['type'] ?? '') {
                        'new' => 'green',
                        'extend' => 'blue',
                        'reduce' => 'yellow',
                        'delete' => 'red',
                        'edit' => 'purple',
                    };
                @endphp

                <x-misc.tag text="{{ ucfirst($request['type']) }} Dataset" color="{{ $color }}" />
            </x-tables.table-cell>
            {{-- Status --}}
            <x-tables.table-cell>
                @php
                    $color = match($request['status'] ?? '') {
                        'approved' => 'green',
                        'rejected' => 'red',
                        'pending' => 'yellow',
                    };
                @endphp

                <x-misc.tag text="{{ ucfirst($request['status']) }}" color="{{ $color }}" />
            </x-tables.table-cell>

            {{-- Requested At --}}
            <x-tables.table-cell>
                <span class="text-gray-200">{{ \Carbon\Carbon::parse($request['created_at'])->format('M d, Y') }}</span>
            </x-tables.table-cell>

            {{-- Action buttons --}}
            <x-tables.table-cell>
                <div
                    class="flex items-center gap-3">
                    <x-misc.button
                        wire:click="cancelRequest({{ $request['id'] }})"
                        color="red"
                        size="sm">
                        Cancel
                    </x-misc.button>
                </div>
            </x-tables.table-cell>
        </x-tables.table-row>
    @endforeach
</x-tables.wrapper>
