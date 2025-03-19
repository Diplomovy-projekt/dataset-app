@props(['filename'])

<div x-cloak x-data="{
         showTooltip: false,
         timer: null
     }"
     class="relative w-36"
     x-on:mouseenter="
         showTooltip = true;
         clearTimeout(timer);
     "
     x-on:mouseleave="
         timer = setTimeout(() => {
            if (!$refs.tooltip.matches(':hover')) {
                showTooltip = false;
            }
         }, 300);
     ">
    <!-- Text field with truncation -->
    <p class="text-sm w-full truncate">
        {{ $filename }}
    </p>

    <!-- Tooltip with dynamic positioning -->
    <div x-show="showTooltip"
         x-ref="tooltip"
         x-transition
         @mouseenter="clearTimeout(timer)"
         @mouseleave="showTooltip = false"
         class="text-base fixed z-50 mt-1 bg-slate-700 text-white p-2 rounded shadow-lg select-text cursor-text"
         x-init="
            $watch('showTooltip', value => {
                if (value) {
                    $nextTick(() => {
                        const rect = $el.getBoundingClientRect();
                        const parentRect = $el.parentElement.getBoundingClientRect();
                        const viewportWidth = window.innerWidth;
                        const viewportHeight = window.innerHeight;

                        // Position below parent initially
                        let top = parentRect.bottom + 5;
                        let left = parentRect.left;

                        // Check right edge
                        if (left + rect.width > viewportWidth - 10) {
                            // Adjust to stay within viewport
                            left = Math.max(10, viewportWidth - rect.width - 10);
                        }

                        // Check bottom edge
                        if (top + rect.height > viewportHeight - 10) {
                            // Position above parent instead
                            top = parentRect.top - rect.height - 5;
                        }

                        $el.style.top = `${top}px`;
                        $el.style.left = `${left}px`;
                        $el.style.maxWidth = `${viewportWidth - 20}px`;
                    });
                }
            })
         ">
        <span class="block break-words">{{ $filename }}</span>
    </div>
</div>
