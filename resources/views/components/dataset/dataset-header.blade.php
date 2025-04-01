<div class="flex flex-col rounded-lg">
    {{-- Dataset Header Section --}}
    <div class="bg-slate-900/50 backdrop-blur-sm rounded-lg">
        <div class="p-6">
            <div class="flex-col justify-between items-start">
                <div class="flex flex-col sm:flex-row sm:justify-between gap-3 items-start sm:items-center w-full">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 min-w-0 w-full sm:w-auto">
                        <h2 class="text-2xl font-bold text-slate-100 truncate flex-shrink min-w-0">
                            {{ $this->dataset['display_name'] }}
                        </h2>
                        <x-dataset.annot_technique :annot_technique="$this->dataset['annotation_technique']" />
                    </div>
                    <x-dataset.dataset-stats :stats="$this->dataset['stats']" class="px-4 py-2 text-xl"/>
                </div>

                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mt-2">
                    <p class="mt-2 text-sm text-slate-400 max-w-2xl">
                        {{ empty($this->dataset['description']) ? 'No description available' : $this->dataset['description'] }}
                    </p>
                    <x-dataset.image-stats :image_stats="$this->dataset['image_stats']" />

                </div>
            </div>


            {{-- Categories --}}
            <div class="mt-6 flex flex-wrap items-center gap-x-12 gap-y-4">
                <div class="flex items-center gap-3">
                    <span class="text-sm text-slate-400">Categories:</span>
                    @foreach($this->categories as $category)
                        <div class="flex-shrink-0 bg-blue-600 bg-opacity-80  px-3 py-1.5 rounded-full text-sm font-medium text-white whitespace-nowrap">
                            {{ $category['name'] }}
                        </div>
                    @endforeach
                </div>
                {{-- Metadata --}}
                @foreach($this->metadata as $metadata)
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-slate-400">{{$metadata['name']}}:</span>
                        @foreach($metadata['metadataValues'] as $metadataValue)
                            <div class="flex-shrink-0 bg-slate-700  px-3 py-1.5 rounded-full text-sm text-gray-200 whitespace-nowrap ">
                                {{ $metadataValue['value'] }}
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</div>
