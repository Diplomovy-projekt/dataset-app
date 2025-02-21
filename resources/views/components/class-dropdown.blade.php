{{--
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
        <span>Toggle classes</span>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <!-- Dropdown Content -->
    <div x-cloak
         x-show="open"
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
                                 style="background-color: {{ $class['rgb'] }};">
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

--}}
<div x-data="{ open: false,
        classes: @this.get('dataset.classes'),
        selectedClasses: [],
        search: '',
        init() {
            if (this.classes && typeof this.classes === 'object') {
                this.classes = Object.values(this.classes);
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
                        this.selectedClasses.splice(index, 1);
                    }else {
                        this.selectedClasses.push(classId);
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
                    el.classList.remove('hidden');
                } else {
                    el.classList.add('hidden');
                }
            });
        },
        filteredClasses() {
            return this.classes.filter(c =>
                c.name.toLowerCase().includes(this.search.toLowerCase())
            );
        }
    }"
     x-init="init()"
     class="relative">

    <!-- Dropdown Button -->
    <button @click="open = !open"
            class="bg-slate-900/50 text-slate-300 px-4 py-2 rounded-lg border border-slate-700
                   flex items-center gap-2 hover:bg-slate-800/50 transition-colors">
        <span>Toggle classes</span>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <!-- Dropdown Content -->
    <div x-cloak
         x-show="open"
         @click.away="open = false"
         class="absolute sm:right-0 sm:left-auto mt-2  bg-slate-900 border border-slate-700 rounded-lg shadow-xl z-20 w-auto min-w-max">

        <div class="p-2 sm:flex gap-3 border-b border-slate-700 items-center align-center justify-center">
            <!-- Search Bar -->
            <div class="w-full min-w-36">
                <div class="relative">
                    <input type="text"
                           x-model="search"
                           placeholder="Search classes..."
                           class="w-full bg-slate-800 text-slate-300 rounded-lg px-4 py-1.5 pl-8 focus:outline-none focus:ring-2 focus:ring-slate-600 text-sm">
                    <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <div class="flex items-center space-x-2 px-1">
                <div class="flex divide-x-0 border rounded-lg border-slate-300">
                    <div class="w-6 h-6 cursor-pointer flex items-center justify-center"
                         @click="toggleClass('all')"
                         :class="{ 'text-green-500': selectedClasses.length === classes.length, 'text-gray-300': selectedClasses.length !== classes.length }">
                        <x-tni-tick-small-o class="w-5 h-5"/>
                    </div>
                    <div class="w-6 h-6 cursor-pointer flex items-center justify-center"
                         @click="toggleClass('none')"
                         :class="{ 'text-red-500': selectedClasses.length === 0, 'text-gray-300': selectedClasses.length !== 0 }">
                        <x-tni-x-small class="w-5 h-5"/>
                    </div>
                </div>
                <p class="text-sm font-medium text-slate-300 whitespace-nowrap">Toggle all</p>
            </div>
        </div>

        <!-- Class Cards Grid -->
        <div class="p-2 max-h-[400px] overflow-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 w-full">
            @foreach($this->dataset['classes'] as $class)
                <div class="rounded-lg min-w-28"
                     x-show="filteredClasses().some(c => c.id === {{ $class['id'] }})"
                     style="border-color: {{ $class['rgb'] }};">
                    <div class="flex items-center gap-1">
                        <!-- Class Image -->
                        <div class="relative w-12 h-12 rounded-lg overflow-hidden border border-slate-700 flex-shrink-0">
                            <img src="{{asset($class['image'])}}"
                                 alt="{{ $class['name'] }}"
                                 class="object-cover w-full h-full cursor-pointer"
                                 @click="const imgSrc = $event.target.src;
                                                        console.log(imgSrc);
                                                        $dispatch('open-full-screen-image', { src: imgSrc, overlayId: 'null' })">
                            <div class="absolute bottom-0 left-0 right-0 h-4"
                                 style="background-color: {{ $class['rgb'] }}80">
                            </div>
                        </div>

                        <!-- Controls and Name -->
                        <div class="">
                            <p class="font-base text-slate-300 pr-2 whitespace-normal break-words">{{ $class['name'] }}</p>
                            <div class="w-fit flex divide-x-0 border rounded-lg border-slate-300"
                                 style="border-color: {{ $class['rgb'] }};">
                                <div class="w-6 h-6 cursor-pointer flex items-center justify-center"
                                     @click="toggleClass({{ $class['id'] }})"
                                     :class="{ 'text-green-500': selectedClasses.includes({{ $class['id'] }}), 'text-gray-300': !selectedClasses.includes({{ $class['id'] }}) }">
                                    <x-tni-tick-small-o class="w-5 h-5"/>
                                </div>
                                <div class="w-6 h-6 cursor-pointer flex items-center justify-center"
                                     @click="toggleClass({{ $class['id'] }})"
                                     :class="{ 'text-red-500': !selectedClasses.includes({{ $class['id'] }}), 'text-gray-300': selectedClasses.includes({{ $class['id'] }}) }">
                                    <x-tni-x-small class="w-5 h-5"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
