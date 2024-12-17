<x-mary-dropdown label="Categories" class="w-36">
    <div class="z-10 w-64 max-h-96 divide-y divide-gray-100 overflow-auto">
        <div @click.stop="" class="flex items-center space-x-2 p-2">
            <div class="w-4 h-4 rounded"></div>
            <div x-data="{ selectedAll: null }" class="flex divide-x-0 border-2 rounded-lg border-gray-300">
                <div
                    :class="selectedAll === 'tickAll' ? 'text-green-500' : 'text-gray-300'"
                    wire:click="toggleCategory('tickAll', null)"
                    class="w-6 h-6 cursor-pointer"
                    x-on:click="selectedAll = selectedAll === 'tickAll' ? null : 'tickAll';">
                    <x-tni-tick-small-o/>
                </div>
                <div
                    :class="selectedAll === 'crossAll' ? 'text-red-500' : 'text-gray-300'"
                    wire:click="toggleCategory('crossAll', null)"
                    class="w-6 h-6 cursor-pointer"
                    x-on:click="selectedAll = selectedAll === 'crossAll' ? null : 'crossAll';">
                    <x-tni-x-small />
                </div>
            </div>
            <p class="text-sm">Toggle all</p>
            <x-button wire:click="toggleCategory('reset', null)" text="Reset" size="xs" type="secondary" wire:click="resetCategoryToggle" class="text-sm text-red-500">Reset</x-button>
        </div>
        @foreach($this->categories as $category)
            <div @click.stop="" class="p-2" wire:key="{{$category['id']}}">
                <div class="flex items-center space-x-2">
                    <div
                        class="w-4 h-4 rounded"
                        style="background-color: {{ $category['color']['fill'] }};"
                    ></div>
                    <div x-data="{ selected: null }" class="flex divide-x-0 border-2 rounded-lg border-gray-300">
                        <div
                            :class="selected === 'tick-{{$category['id']}}' ? 'text-green-500' : 'text-gray-300'"
                            class="w-6 h-6 cursor-pointer"
                            x-on:click="selected = selected === 'tick-{{$category['id']}}' ? 'null' : 'tick-{{$category['id']}}'; $wire.toggleCategory({{ $category['id'] }}, selected)">
                            <x-tni-tick-small-o />
                        </div>

                        <div
                            :class="selected === 'x-{{$category['id']}}' ? 'text-red-500' : 'text-gray-300'"
                            class="w-6 h-6 cursor-pointer"
                            x-on:click="selected = selected === 'x-{{$category['id']}}' ? null : 'x-{{$category['id']}}'; $wire.toggleCategory({{ $category['id'] }}, selected)">
                            <x-tni-x-small />
                        </div>
                    </div>

                    <p class="text-sm">{{ $category['name'] }}</p>
                </div>
            </div>
        @endforeach

    </div>
</x-mary-dropdown>
