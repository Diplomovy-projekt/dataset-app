@props([
    'originData'
])
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4">
    {{-- Century Column --}}
    <div>
        <h2 class="text-xl font-bold text-gray-200 mb-4">Century</h2>
        <div class="space-y-3">
            @if(isset($originData['century']))

                @forelse($originData['century'] as $century)
                    <label class="block bg-gray-700 text-gray-200 rounded-lg p-3 flex items-center hover:bg-gray-600 transition duration-300 ease-in-out">
                        <input
                            type="checkbox"
                            name="century[]"
                            value="{{ $century }}"
                            class="form-checkbox h-5 w-5 text-indigo-600 mr-3 bg-gray-800 border-transparent focus:border-transparent focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500"
                        >
                        <span class="text-sm font-medium">{{ $century }}</span>
                    </label>
                @empty
                    <p class="text-gray-400">No centuries found</p>
                @endforelse
            @endif
        </div>
    </div>

    {{-- Language Column --}}
    <div>
        <h2 class="text-xl font-bold text-gray-200 mb-4">Language</h2>
        <div class="space-y-3">
            @if(isset($originData['language']))
                @forelse($originData['language'] as $language)
                    <label class="block bg-gray-700 text-gray-200 rounded-lg p-3 flex items-center hover:bg-gray-600 transition duration-300 ease-in-out">
                        <input
                            type="checkbox"
                            name="language[]"
                            value="{{ $language }}"
                            class="form-checkbox h-5 w-5 text-indigo-600 mr-3 bg-gray-800 border-transparent focus:border-transparent focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500"
                        >
                        <span class="text-sm font-medium">{{ $language }}</span>
                    </label>
                @empty
                    <p class="text-gray-400">No languages found</p>
                @endforelse
            @endif
        </div>
    </div>
</div>
