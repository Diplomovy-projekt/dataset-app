@props([
    'table' => [],
    'sortColumn' => null,
    'sortDirection' => null,
])

<div {{ $attributes->merge(['class'=>'w-full overflow-x-auto']) }}>
    <table class="table-auto w-full border-collapse">
        <thead x-data="{
            sortField: $wire.entangle('tables.{{ $table['id'] }}.sortColumn'),
            sortDirection: $wire.entangle('tables.{{ $table['id'] }}.sortDirection')
        }">
        <tr>
            @foreach($table['headers'] as $header)
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-200 {{ $header['width'] }}">
                    @if($header['sortable'])
                        <button class="flex items-center gap-2 hover:text-blue-400 transition-colors"
                                wire:click="sortBy('{{ $table['id'] }}','{{ $header['field'] }}')">
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
        {{ $slot }}
        </tbody>
    </table>
    {{--@php
        $variableName = 'paginated' . Illuminate\Support\Str::studly(str_replace('-', ' ', $table['id']));
    @endphp

    {{ $this->{$variableName}->links() }}--}}
</div>
