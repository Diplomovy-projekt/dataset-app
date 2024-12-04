<div>
    <x-containers.grid-card-container>
        {{-- Display images in a grid container --}}
        @foreach($this->dataset->images as $image)
            <div class="grid-card">
                <img src="{{ asset('storage/datasets/'.$this->dataset->unique_name. '/' . $image->img_filename) }}" alt="Image" class="w-full h-auto">
                <div class="text-center mt-2">
                    <p>{{ $image->img_filename }}</p>
                </div>
            </div>
        @endforeach
    </x-containers.grid-card-container>
</div>
