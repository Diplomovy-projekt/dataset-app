<div>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Search Bar -->
        <div class="flex flex-col gap-2 pb-5">
            <x-search-bar />
        </div>

        <!-- Dataset Card Container -->
        <div class="flex flex-wrap sm:gap-5 pt-5">
            @foreach($datasets as $dataset)
                <a href="{{ route('dataset.show', ['uniqueName' => $dataset['unique_name']])}}"
                   wire:navigate
                   wire:key="{{ $dataset['unique_name'] }}"
                   class="bg-gray-800 rounded-lg shadow-md w-64 block overflow-hidden">
                    <div class="relative h-48">
                        <img src="https://picsum.photos/200/300" alt="{{ $dataset['name'] }}" class="w-full h-full object-cover rounded-t-lg">
                        <div class="absolute top-4 right-4 bg-gray-900 text-white px-2 py-1 rounded-lg text-sm">
                            {{ $dataset['annotation_technique'] }}
                        </div>
                    </div>
                    <div class="p-4 bg-slate-900 border-b border-x border-gray-700 rounded-b-lg">
                        <h3 class="text-lg font-medium text-white">{{ $dataset['display_name'] }}</h3>
                        <div class="flex items-center space-x-2 text-gray-400 text-sm">
                            <span>{{ $dataset['num_images'] }} images</span>
                            <span>·</span>
                            <span>Last updated {{ $dataset['updated_at'] }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>
