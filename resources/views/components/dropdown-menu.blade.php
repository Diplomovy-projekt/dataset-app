<div class="relative z-30"
     x-data="{
        open: false,
        positionDropdown() {
            const button = $el.querySelector('button');
            const dropdown = $el.querySelector('[role=\'menu\']');
            const buttonRect = button.getBoundingClientRect();

            // Get available space
            const spaceBelow = window.innerHeight - buttonRect.bottom;
            const spaceRight = window.innerWidth - buttonRect.right;

            // Position dropdown
            dropdown.style.position = 'fixed';
            dropdown.style.top = `${buttonRect.bottom + 5}px`;

            // Handle horizontal positioning
            if (spaceRight < dropdown.offsetWidth && buttonRect.left > dropdown.offsetWidth) {
                // Not enough space right, but enough space left
                dropdown.style.right = `${window.innerWidth - buttonRect.right}px`;
            } else {
                // Default to align with left edge
                dropdown.style.left = `${buttonRect.left}px`;
            }

            // Handle vertical positioning
            if (spaceBelow < dropdown.offsetHeight && buttonRect.top > dropdown.offsetHeight) {
                // Not enough space below, but enough space above
                dropdown.style.top = `${buttonRect.top - dropdown.offsetHeight - 5}px`;
            }
        }
     }"
     @click.away="open = false">

    {{-- Trigger Button --}}
    <button
        @click="open = !open; $nextTick(() => { if(open) positionDropdown() })"
        class="p-2 rounded-full hover:bg-slate-800 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-100" viewBox="0 0 20 20" fill="currentColor">
            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
        </svg>
    </button>

    {{-- Dropdown Menu --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         @resize.window="if(open) positionDropdown()"
         @scroll.window="if(open) positionDropdown()"
         role="menu"
         style="display: none;"
         class="z-20 w-48">
        <div {{ $attributes->merge(['class' => 'bg-slate-900 rounded-md shadow-lg ring-1 ring-black ring-opacity-5']) }}>
            {{ $slot }}
        </div>
    </div>
</div>
