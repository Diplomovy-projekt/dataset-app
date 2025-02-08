<div x-data="{ open: '' }"
     class="flex flex-col max-w-7xl mx-auto px-4 py-8">

    <div class="flex justify-between w-full">
        <div></div>
        <x-button type="primary" text="Upload Dataset" size="sm" @click.prevent="open = 'uploadDataset'"/>
    </div>

    <livewire:forms.upload-dataset :modalId="'uploadDataset'" :modalStyle="'new-upload'"/>
    <div class="flex flex-wrap sm:gap-5 pt-5">
        @forelse($datasets as $dataset)
            <x-dataset.dataset-card :dataset="$dataset"></x-dataset-card>
        @empty
        @endforelse
    </div>
</div>

