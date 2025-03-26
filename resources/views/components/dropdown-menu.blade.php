@props([
    'direction' => 'right', // can be 'left', 'right', 'top-left', 'top-right'
    'buttonText' => null // Default to 3 dots if not provided
])

<div x-data="{
    openDropdown: false,
    dropdownTop: null,
    dropdownLeft: null,
    checkPosition() {
        this.$nextTick(() => {
            let dropdown = this.$refs.dropdown;
            let trigger = this.$refs.trigger;
            if (!dropdown || !trigger) return;

            let rect = trigger.getBoundingClientRect();
            let spaceBelow = window.innerHeight - rect.bottom;
            let dropdownHeight = dropdown.offsetHeight;
            let dropdownWidth = dropdown.offsetWidth;

            // Position vertically
            if (spaceBelow < dropdownHeight) {
                this.dropdownTop = rect.top - dropdownHeight - 10; // Place above
            } else {
                this.dropdownTop = rect.bottom + 10; // Place below
            }

            // Position horizontally
            if ('{{ $direction }}' === 'right') {
                this.dropdownLeft = rect.left;
            } else if ('{{ $direction }}' === 'left') {
                this.dropdownLeft = rect.right - dropdownWidth;
            }
        });
    }
}"
     class="relative inline-block">
    {{--Trigger Button--}}
    <button
        @click="openDropdown = !openDropdown; checkPosition()"
        class="p-2 rounded-full hover:bg-slate-800 transition-colors flex items-center space-x-2"
        x-ref="trigger"
    >
        @if ($buttonText)
            <span class="text-gray-100">{{ $buttonText }}</span>
        @else
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-100" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
            </svg>
        @endif
    </button>

    {{--Dropdown Menu--}}
    <div x-cloak
        x-show="openDropdown"
        @click.away="openDropdown = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="fixed z-30 w-60"
        x-bind:style="'top: ' + dropdownTop + 'px; left: ' + dropdownLeft + 'px;'"
        x-ref="dropdown"
        style="display: none;"
        role="menu"
    >
        <div {{ $attributes->merge(['class' => 'bg-slate-900 rounded-md shadow-lg ring-1 ring-black ring-opacity-5']) }}>
            {{ $slot }}
        </div>
    </div>
</div>
