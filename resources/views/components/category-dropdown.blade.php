{{--
<div x-data="{ open: false }" class="relative">
    <!-- Dropdown Button -->
    <button @click="open = !open"
            class="bg-slate-900/50 text-slate-300 px-4 py-2 rounded-lg border border-slate-700
                                   flex items-center gap-2 hover:bg-slate-800/50 transition-colors">
        <span>Filter by category</span>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <!-- Dropdown Content -->
    <div x-show="open"
         @click.away="open = false"
         class="absolute mt-2 w-48 bg-slate-900 border border-slate-700 rounded-lg shadow-xl z-50">
        <div class="py-1">
            <div class="z-10 w-48 max-h-96 divide-y divide-slate-500 overflow-auto">

                <!-- Toggle All -->
                <div class="flex items-center space-x-2 p-2">
                    <div class="w-4 h-4 rounded"></div>
                    <div class="flex divide-x-0 border rounded-lg border-slate-300">
                        <!-- Tick button -->
                        <div class="w-6 h-6 cursor-pointer"
                             wire:click="toggleClass(null, 'all')"
                             :class="{ 'text-green-500': @json($this->selectedAll === 'all'), 'text-gray-300': @json($this->selectedAll !== 'all') }">
                            <x-tni-tick-small-o/>
                        </div>
                        <!-- Cross button -->
                        <div class="w-6 h-6 cursor-pointer"
                             wire:click="toggleClass(null, 'none')"
                             :class="{ 'text-red-500': @json($this->selectedAll === 'none'), 'text-gray-300': @json($this->selectedAll !== 'none') }">
                            <x-tni-x-small/>
                        </div>
                    </div>
                    <p class="text-sm text-slate-300">Toggle all</p>
                </div>

                <!-- Class Buttons -->
                @foreach($this->dataset['classes'] as $class)
                    <div class="p-2" wire:key="{{ $class['id'] }}">
                        <div class="flex items-center space-x-2">
                            <!-- Class Color -->
                            <div class="w-4 h-4 rounded border border-white/50"
                                 style="background-color: {{ $class['color']['fill'] }};">
                            </div>
                            <!-- Radio Buttons -->
                            <div class="flex divide-x-0 border rounded-lg border-gray-300">
                                <!-- Tick button -->
                                <div class="w-6 h-6 cursor-pointer"
                                     wire:click="toggleClass({{ $class['id'] }}, 'true')"
                                     :class="{ 'text-green-500': @json($class['state'] === 'true'), 'text-gray-300': @json($class['state'] !== 'true') }">
                                    <x-tni-tick-small-o/>
                                </div>
                                <!-- Cross button -->
                                <div class="w-6 h-6 cursor-pointer"
                                     wire:click="toggleClass({{ $class['id'] }}, 'false')"
                                     :class="{ 'text-red-500': @json($class['state'] === 'false'), 'text-gray-300': @json($class['state'] !== 'false') }">
                                    <x-tni-x-small/>
                                </div>
                            </div>
                            <!-- Category Name -->
                            <p class="text-sm text-slate-300">{{ $class['name'] }}</p>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </div>
</div>
--}}
<div x-data="{ open: false,
        classes: @this.get('dataset.classes'),
        selectedClasses: [],
        init() {
            if (this.classes && typeof this.classes === 'object') {
                this.classes = Object.values(this.classes); // Convert object to array
            }
            if (Array.isArray(this.classes) && this.classes.length > 0) {
                this.selectedClasses = this.classes.map(c => c.id);
            }
        },
        selectedAll: 'all',
        toggleClass(classId) {
            switch (classId) {
                case 'all':
                    this.selectedAll = 'all';
                    this.selectedClasses = this.classes.map(c => c.id);
                    break;
                case 'none':
                    this.selectedAll = 'none';
                    this.selectedClasses = [];
                    break;
                default:
                    if(this.selectedClasses.includes(classId)) {
                        const index = this.selectedClasses.indexOf(classId);
                        this.selectedClasses.splice(index, 1); // Remove class from selected
                     }else {
                        this.selectedClasses.push(classId); // Add class to selected
                    }
                    break;
            }
            this.updateVisibility();
        },
        updateVisibility() {
            const elements = document.querySelectorAll('[annotation-class]');
            elements.forEach(el => {

                const classId = Number(el.getAttribute('annotation-class'));
                if (this.selectedClasses.includes(classId)) {
                    el.classList.remove('hidden'); // Show the annotation
                } else {
                    el.classList.add('hidden'); // Hide the annotation
                }
        });
    }
     }"
     x-init="init()"
     class="relative">
    <!-- Dropdown Button -->
    <button @click="open = !open"
            class="bg-slate-900/50 text-slate-300 px-4 py-2 rounded-lg border border-slate-700
                                   flex items-center gap-2 hover:bg-slate-800/50 transition-colors">
        <span>Filter by category</span>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <!-- Dropdown Content -->
    <div x-show="open"
         @click.away="open = false"
         class="absolute mt-2 w-48 bg-slate-900 border border-slate-700 rounded-lg shadow-xl z-50">
        <div class="py-1">
            <div class="z-10 w-48 max-h-96 divide-y divide-slate-500 overflow-auto">

                <!-- Toggle All -->
                <div class="flex items-center space-x-2 p-2">
                    <div class="w-4 h-4 rounded"></div>
                    <div class="flex divide-x-0 border rounded-lg border-slate-300">
                        <!-- Tick button -->
                        <div class="w-6 h-6 cursor-pointer"
                             @click="toggleClass('all')"
                             :class="{ 'text-green-500': selectedClasses.length === classes.length, 'text-gray-300': selectedClasses.length !== classes.length }">
                            <x-tni-tick-small-o/>
                        </div>
                        <!-- Cross button -->
                        <div class="w-6 h-6 cursor-pointer"
                             @click="toggleClass('none')"
                             :class="{ 'text-red-500': selectedClasses.length === 0, 'text-gray-300': selectedClasses.length !== 0 }">
                            <x-tni-x-small/>
                        </div>
                    </div>
                    <p class="text-sm text-slate-300">Toggle all</p>
                </div>

                <!-- Class Buttons -->
                @foreach($this->dataset['classes'] as $class)
                    <div class="p-2" :key="{{ $class['id'] }}">
                        <div class="flex items-center space-x-2">
                            <!-- Class Color -->
                            <div class="w-4 h-4 rounded border border-white/50"
                                 style="background-color: {{ $class['color']['fill'] }};">
                            </div>
                            <!-- Radio Buttons -->
                            <div class="flex divide-x-0 border rounded-lg border-gray-300">
                                <!-- Tick button -->
                                <div class="w-6 h-6 cursor-pointer"
                                     @click="toggleClass({{ $class['id'] }})"
                                     :class="{ 'text-green-500': selectedClasses.includes({{ $class['id'] }}), 'text-gray-300': !selectedClasses.includes({{ $class['id'] }}) }">
                                    <x-tni-tick-small-o/>
                                </div>
                                <!-- Cross button -->
                                <div class="w-6 h-6 cursor-pointer"
                                     @click="toggleClass({{ $class['id'] }})"
                                     :class="{ 'text-red-500': !selectedClasses.includes({{ $class['id'] }}), 'text-gray-300': selectedClasses.includes({{ $class['id'] }}) }">
                                    <x-tni-x-small/>
                                </div>
                            </div>
                            <!-- Category Name -->
                            <p class="text-sm text-slate-300">{{ $class['name'] }}</p>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </div>
</div>

