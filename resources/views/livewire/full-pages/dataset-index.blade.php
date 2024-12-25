<div>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Search Bar -->
        <div class="flex flex-col gap-2 pb-5">
            <x-search-bar />
        </div>

        <!-- Dataset Card Container -->
        <div class="flex flex-wrap sm:gap-5 pt-5">
            @foreach($datasets as $dataset)
                <x-dataset-card :dataset="$dataset"></x-dataset-card>
            @endforeach
        </div>
    </div>
</div>
