<div x-data="classSelector(@this)" class="relative">
    <!-- Dropdown Button -->
    <button @click="open = !open" class="bg-slate-900/50 text-slate-300 px-4 py-2 rounded-lg border border-slate-700 flex items-center gap-2 hover:bg-slate-800/50 transition-colors">
        <span>Toggle classes</span>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <!-- Dropdown Content -->
    <div x-cloak x-show="open" @click.away="open = false" class="absolute sm:right-0 sm:left-auto mt-2 bg-slate-900 border border-slate-700 rounded-lg shadow-xl z-20 w-auto min-w-max">
        <div class="p-2 sm:flex gap-3 border-slate-700 items-center align-center">
            <!-- Search Bar -->
            <div class="relative flex-1">
                {{-- Search bar --}}
                <div class="relative">
                    <input type="text"
                           x-model="searchInput"
                           @input="updateSuggestions"
                           @keydown.enter="performSearch"
                           placeholder="Search classes..."
                           class="w-full bg-transparent text-slate-100 rounded-lg pl-10 pr-4 py-2 border-b border-slate-700 focus:outline-none focus:border-blue-500/50">
                    <button @click="performSearch" class="absolute left-3 top-2.5 h-5 w-5 text-slate-500">
                        <svg class="h-5 w-5 text-slate-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                </div>
                {{--Suggestions --}}
                <ul x-show="showSuggestions" @click.outside="showSuggestions = false" class="absolute left-0 w-full min-w-36 bg-slate-900 border border-slate-700 rounded-lg shadow-lg mt-1 z-20 text-sm backdrop-blur-md bg-opacity-80">
                    <template x-for="(classItem, index) in suggestions" :key="classItem.id">
                        <li @click="selectSuggestion(classItem)" class="px-3 py-1.5 text-slate-300 cursor-pointer transition-colors duration-150 hover:bg-blue-500 hover:text-white first:rounded-t-lg last:rounded-b-lg">
                            <span x-text="classItem.name" class="truncate"></span>
                        </li>
                        <div x-show="index < suggestions.length - 1" class="h-px bg-slate-700 mx-2"></div>
                    </template>
                </ul>
            </div>


            {{-- Toggle all --}}
            <div class="flex items-center space-x-2 px-1">
                <div class="flex divide-x-0 border rounded-lg border-slate-300">
                    <div class="w-6 h-6 cursor-pointer flex items-center justify-center" @click="toggleClass('all')" :class="{ 'text-green-500': selectedClasses.length === classes.length, 'text-gray-300': selectedClasses.length !== classes.length }">
                        <x-tni-tick-small-o class="w-5 h-5"/>
                    </div>
                    <div class="w-6 h-6 cursor-pointer flex items-center justify-center" @click="toggleClass('none')" :class="{ 'text-red-500': selectedClasses.length === 0, 'text-gray-300': selectedClasses.length !== 0 }">
                        <x-tni-x-small class="w-5 h-5"/>
                    </div>
                </div>
                <p class="text-sm font-medium text-slate-300 whitespace-nowrap">Toggle all</p>
            </div>
        </div>

        <!-- Class Cards Grid -->
        <div class="p-2 max-h-[400px] overflow-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 w-full">
            @foreach($this->toggleClasses as $classItem)
                <div class="rounded-lg min-w-28"
                     :style="'border-color: {{ $classItem['rgb'] }};'"
                     x-show="!activeSearch.trim() || '{{ strtolower($classItem['name']) }}'.includes(activeSearch.toLowerCase())">
                    <div class="flex items-center gap-1">
                        <!-- Class Image -->
                        <div class="relative w-12 h-12 rounded-lg overflow-hidden border border-slate-700 flex-shrink-0">
                            <x-images.img dataset="{{ $classItem['image']['dataset'] }}"
                                          folder="{{ $classItem['image']['folder'] }}"
                                          filename="{{ $classItem['image']['filename'] }}"
                                          id="{{ $classItem['image']['filename'] }}"
                                          fetchpriority="high"
                                          @load="console.log('Toggle image loaded')"
                                          class="object-cover w-full h-full cursor-pointer"
                                          @click="const imgSrc = $event.target.src.replace('/thumbnails/', '/full-images/');
                                      $dispatch('open-full-screen-image', { src: imgSrc, overlayId: null })">
                            </x-images.img>
                            <div class="absolute bottom-0 left-0 right-0 h-4" style="background-color: {{ $classItem['rgb'] }}80;"></div>
                        </div>

                        <!-- Controls and Name -->
                        <div>
                            <p class="font-base text-slate-300 pr-2 whitespace-normal break-words">{{ $classItem['name'] }}</p>
                            <div class="w-fit flex divide-x-0 border rounded-lg border-slate-300" style="border-color: {{ $classItem['rgb'] }};">
                                <div class="w-6 h-6 cursor-pointer flex items-center justify-center"
                                     @click="toggleClass({{ $classItem['id'] }})"
                                     :class="{ 'text-green-500': selectedClasses.includes({{ $classItem['id'] }}), 'text-gray-300': !selectedClasses.includes({{ $classItem['id'] }}) }">
                                    <x-tni-tick-small-o class="w-5 h-5"/>
                                </div>
                                <div class="w-6 h-6 cursor-pointer flex items-center justify-center"
                                     @click="toggleClass({{ $classItem['id'] }})"
                                     :class="{ 'text-red-500': !selectedClasses.includes({{ $classItem['id'] }}), 'text-gray-300': selectedClasses.includes({{ $classItem['id'] }}) }">
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


@script
<script>
    Alpine.data('classSelector', (wire) => ({
        open: false,
        classes: [],
        imageBasePath: "{{ asset('') }}",
        selectedClasses: [],
        search: '',
        selectedAll: 'all',
        showSuggestions: false,
        filteredClasses: [],
        suggestions: [],
        searchInput: '',      // What user types
        activeSearch: '',

        init() {
            this.classes = wire.get('toggleClasses') || [];
            if (typeof this.classes === 'object') {
                this.classes = Object.values(this.classes);
            }
            if (Array.isArray(this.classes) && this.classes.length > 0) {
                this.selectedClasses = this.classes.map(c => c.id);
            }
            this.filteredClasses = [...this.classes];
            this.classes = [...this.classes];
        },

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
                    if (this.selectedClasses.includes(classId)) {
                        this.selectedClasses = this.selectedClasses.filter(id => id !== classId);
                    } else {
                        this.selectedClasses.push(classId);
                    }
                    break;
            }
            this.updateVisibility();
        },

        updateVisibility() {
            document.querySelectorAll('[annotation-class]').forEach(el => {
                const classId = Number(el.getAttribute('annotation-class'));
                el.classList.toggle('hidden', !this.selectedClasses.includes(classId));
            });
        },

        updateSuggestions() {
            if (this.searchInput.length > 0) {
                this.suggestions = this.classes
                    .filter(c => c.name.toLowerCase().includes(this.searchInput.toLowerCase()))
                    .slice(0, 5);
                this.showSuggestions = this.suggestions.length > 0;
            } else {
                this.suggestions = [];
                this.showSuggestions = false;
                this.activeSearch = '';  // Clear active search when input is empty
            }
        },

        selectSuggestion(suggestion) {
            this.searchInput = suggestion.name;
            this.activeSearch = suggestion.name;
            this.showSuggestions = false;
        },

        performSearch(e) {
            e.preventDefault();
            this.activeSearch = this.searchInput;
            this.showSuggestions = false;
        }
    }));
</script>
@endscript
