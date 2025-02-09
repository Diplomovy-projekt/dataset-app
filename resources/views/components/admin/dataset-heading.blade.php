<button class="flex items-center gap-2 hover:text-blue-400 transition-colors"
        @click="sortBy('categories')">
    Categories
    <span class="flex flex-col">
                                <svg :class="{ 'text-blue-400': sortField === 'categories' && sortDirection === 'asc' }"
                                     class="w-3 h-3 -mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                </svg>
                                <svg :class="{ 'text-blue-400': sortField === 'categories' && sortDirection === 'desc' }"
                                     class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </span>
</button>
