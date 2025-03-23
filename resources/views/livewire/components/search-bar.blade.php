<div class="flex items-center bg-slate-900  rounded-lg shadow-md">
    {{--Search Input--}}
    <input
        type="text"
        wire:model="searchTerm"
        class="flex-grow p-3 bg-transparent text-white rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-300"
        placeholder="Search..."
    >
    {{--Search Button--}}
    <button wire:click="search"
        class="flex items-center bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-r-lg focus:outline-none">
        Search
        <x-zondicon-search class="w-5 h-5 ml-2" />
    </button>
</div>
