@props([
    'searchTitle' => 'Search...',
])
<div class="flex-1 relative items-center bg-transparent w-full" {{ $attributes }}>
    <input type="text"
           wire:model.live.debounce.300ms="searchTerm"
           placeholder="{{ $searchTitle }}"
           class="w-full bg-transparent text-slate-100 rounded-lg pl-10 pr-4 py-2
                  border-b border-slate-700 focus:outline-none focus:border-blue-500/50">
    <div class="absolute left-3 top-2.5 h-5 w-5 text-slate-500">
        <!-- Search icon (shown when not loading) -->
        <svg wire:loading.remove wire:target="searchTerm" class="h-5 w-5 text-slate-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>

        <!-- Loading spinner (shown during search) -->
        <svg wire:loading wire:target="searchTerm" class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>
</div>
