<div class="flex flex-col">
    {{-- Dataset Header Section --}}
    <div class="bg-slate-900/50 backdrop-blur-sm rounded-lg">
        <div class="p-6">
            <div class="flex-col justify-between items-start">
                <div class="flex flex-col sm:flex-row sm:justify-between gap-3 items-start sm:items-center w-full">
                    <div class="flex items-center gap-3 min-w-0 w-full sm:w-auto">
                        <h2 class="text-2xl font-bold text-slate-100 truncate flex-shrink min-w-0">
                            {{ $this->dataset['display_name'] }}
                        </h2>
                        <x-dataset.annot_technique :annot_technique="$this->dataset['annotation_technique']" />
                    </div>
                    <x-dataset.dataset-stats :stats="$this->dataset['stats']" class="px-4 py-2 text-xl"/>
                </div>

                {{-- Statistics --}}
                <p class="mt-2 text-sm text-slate-400 max-w-2xl">
                    {{ empty($this->dataset['description']) ? 'No description available' : $this->dataset['description'] }}
                </p>
            </div>

            {{-- Categories --}}
            <div class="mt-6 flex flex-wrap items-center gap-x-12 gap-y-4">
                <div class="flex items-center gap-3">
                    <span class="text-sm text-slate-400">Categories:</span>
                    @foreach($this->categories as $category)
                        <span class="bg-blue-500/20 text-blue-300 px-3 py-1.5 rounded-md text-sm font-medium">{{$category['name']}}</span>
                    @endforeach
                </div>
                {{-- Metadata --}}
                @foreach($this->metadata as $metadata)
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-slate-400">{{$metadata['name']}}:</span> <!-- Accessing 'type' and 'name' keys -->
                        @foreach($metadata['metadataValues'] as $metadataValue) <!-- Accessing 'values' array -->
                        <span class="text-sm text-slate-300">{{$metadataValue['value']}}</span> <!-- Accessing 'value' key -->
                        @endforeach
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</div>
