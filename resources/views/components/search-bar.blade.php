@props([
    'searchTitle' => 'Search...',
    'searchModel' => '',
    'searchMethod' => '',
])
<div x-data="searchBar(@this, '{{ $searchMethod }}', '{{ $searchModel }}')"
    {{ $attributes->merge(['class' => 'flex-1 relative items-center bg-transparent w-full']) }}>
    <input type="text"
           x-model="searchTerm"
           placeholder="{{ $searchTitle }}"
           class="w-full bg-transparent text-slate-100 rounded-lg pl-10 pr-4 py-2
                  border-b border-slate-700 focus:outline-none focus:border-blue-500/50">
    <button wire:click="{{ $searchMethod }}" class="absolute left-3 top-2.5 h-5 w-5 text-slate-500">
        <svg class="h-5 w-5 text-slate-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
    </button>
</div>
@script
<script>
    Alpine.data('searchBar', (wire, searchMethod, searchModel) => ({
        searchTerm: $wire.entangle(searchModel),
        init() {
            this.$watch('searchTerm', (value) => {
                wire.call(searchMethod, value);
            });
        }
    }));

</script>
@endscript
