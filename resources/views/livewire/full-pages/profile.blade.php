<div x-data="{ open: '' }"
     class="flex flex-col max-w-7xl mx-auto px-4 py-8">

    <div class="flex justify-between w-full">
        <div></div>
        <x-button type="primary" text="Upload Dataset" size="sm" @click.prevent="open = 'uploadDataset'"/>
    </div>

    <livewire:forms.upload-dataset :modalId="'uploadDataset'" />
    <x-containers.grid-card-container>
        @foreach($datasets as $dataset)
            <div class="bg-gray-800 rounded-lg shadow-md">
                <div class="relative">
                    <img src="https://picsum.photos/200/300" alt="{{ $dataset['name'] }}" class="w-full h-64 object-cover rounded-t-lg">
                    <div class="absolute top-4 right-4 bg-gray-900 text-white px-2 py-1 rounded-lg text-sm">
                        {{ $dataset['annotation_technique'] }}
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="text-lg font-medium text-white">{{ $dataset['display_name'] }}</h3>
                    <div class="flex items-center space-x-2 text-gray-400 text-sm">
                        <span>{{ $dataset['num_images'] }} images</span>
                        <span>·</span>
                        <span>Last updated {{ $dataset['updated_at'] }}</span>
                    </div>
                </div>
            </div>

        @endforeach
    </x-containers.grid-card-container>
</div>
