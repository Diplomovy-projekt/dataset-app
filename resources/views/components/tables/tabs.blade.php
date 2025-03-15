<div x-data="{ activeTab:  '{{array_keys($tables)[0]}}' }">
    <!-- Tabs -->
    <div class="border-b border-slate-700">
        <div class="flex gap-6 px-6">
            @foreach($tables as $tabId => $table)
                <button
                    @click="activeTab = '{{ $tabId }}'"
                    :class="{
                        'border-b-2 border-blue-500 text-blue-500': activeTab === '{{ $tabId }}',
                        'text-gray-400 hover:text-gray-200': activeTab !== '{{ $tabId }}'
                    }"
                    class="py-4 font-medium transition-colors flex items-center gap-2">
                    {{ $table['label'] ?? ucfirst(str_replace('-', ' ', $tabId)) }}

                    @if(isset($counters[$tabId]) && $counters[$tabId]['count'] > 0)
                        <span class="px-2 py-0.5 text-xs {{ $counters[$tabId]['class'] }} rounded-full">
                            {{ $counters[$tabId]['count'] }}
                        </span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    <!-- Table Content Section -->
    @foreach($tables as $tabId => $table)
        @php
            $variableName = 'paginated' . Illuminate\Support\Str::studly(str_replace('-', ' ', $table['id']));
        @endphp

        <div x-show="activeTab === '{{ $tabId }}'" x-cloak>
            <x-dynamic-component :component="'tables.' . $tabId" :table="$table" :items="$variableName"/>
        </div>

        <div class="mt-5" x-show="activeTab === '{{ $tabId }}'" x-cloak>
            {{ $this->{$variableName}->links() }}
        </div>
    @endforeach
</div>
