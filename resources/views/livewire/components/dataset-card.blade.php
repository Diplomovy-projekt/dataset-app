<div class="bg-gray-800 rounded-lg shadow-md">
    <div class="relative">
        <img src="{{ $dataset['image'] }}" alt="{{ $dataset['name'] }}" class="w-full h-64 object-cover rounded-t-lg">
        <div class="absolute top-4 right-4 bg-gray-900 text-white px-2 py-1 rounded-lg text-sm">
            {{ $dataset['annotation_format'] }}
        </div>
    </div>
    <div class="p-4">
        <h3 class="text-lg font-medium text-white">{{ $dataset['name'] }}</h3>
        <div class="flex items-center space-x-2 text-gray-400 text-sm">
            <span>{{ $dataset['image_count'] }} images</span>
            <span>·</span>
            <span>Last updated {{ $dataset['updated_at'] }}</span>
        </div>
    </div>
</div>
