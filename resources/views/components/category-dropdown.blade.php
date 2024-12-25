<div x-data="{ open: false }" class="relative">
    <button @click="open = !open"
            class="bg-slate-900/50 text-slate-300 px-4 py-2 rounded-lg border border-slate-700
                                   flex items-center gap-2 hover:bg-slate-800/50 transition-colors">
        <span>Filter by category</span>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="open"
         @click.away="open = false"
         class="absolute mt-2 w-48 bg-slate-900 border border-slate-700 rounded-lg shadow-xl z-50">
        <div class="py-1">
            <div class="z-10 w-48 max-h-96 divide-y divide-slate-500 overflow-auto">

                {{-- Toggle ALL  --}}
                <div @click.stop="" class="flex items-center space-x-2 p-2">
                    <div class="w-4 h-4 rounded"></div>
                    <div x-data="{ selectedAll: 'all' }" class="flex divide-x-0 border rounded-lg border-slate-300">
                        {{-- Tick button --}}
                        <div x-model="selectedAll"
                             :class="selectedAll === 'all' ? 'text-green-500' : 'text-gray-300'"
                             class="w-6 h-6 cursor-pointer"
                             x-on:click="if (selectedAll == 'none') { selectedAll = 'all'; $wire.toggleClass('null', 'all'); }">
                            <x-tni-tick-small-o/>
                        </div>
                        {{-- Cross button --}}
                        <div x-model="selectedAll"
                             :class="selectedAll === 'none' ? 'text-red-500' : 'text-gray-300'"
                             class="w-6 h-6 cursor-pointer"
                             x-on:click="if (selectedAll == 'all') { selectedAll = 'none'; $wire.toggleClass('null', 'none'); }">
                            <x-tni-x-small />
                        </div>
                    </div>
                    <p class="text-sm text-slate-300">Toggle all</p>

                    {{-- Class buttons --}}
                </div>
                @foreach($this->classes as $class)
                    <div @click.stop="" class="p-2" wire:key="{{$class['id']}}">
                        <div class="flex items-center space-x-2">
                            {{-- Class color --}}
                            <div
                                class="w-4 h-4 rounded border border-white/50"
                                style="background-color: {{ $class['color']['fill'] }};"
                            ></div>
                            {{-- Radio buttons to enable classes --}}
                            <div x-data="{ selected: '{{$class['state']}}' }" class="flex divide-x-0 border rounded-lg border-gray-300">
                                {{-- Tick button --}}
                                <div x-model="selected"
                                     :class="selected == 'true' ? 'text-green-500' : 'text-gray-300'"
                                     class="w-6 h-6 cursor-pointer"
                                     x-on:click="if (selected == 'false') { selected = 'true'; $wire.toggleClass({{ $class['id'] }}, selected); }">
                                    <x-tni-tick-small-o />
                                </div>
                                {{-- Cross button --}}
                                <div x-model="selected"
                                     :class="selected == 'false' ? 'text-red-500' : 'text-gray-300'"
                                     class="w-6 h-6 cursor-pointer"
                                     x-on:click="if (selected == 'true') { selected = 'false'; $wire.toggleClass({{ $class['id'] }}, selected); }">
                                    <x-tni-x-small />
                                </div>
                            </div>
                            {{-- Category name --}}
                            <p class="text-sm text-slate-300">{{ $class['name'] }}</p>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </div>
</div>
