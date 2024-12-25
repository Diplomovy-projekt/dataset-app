<div class="flex flex-col">
    {{-- Dataset Header Section --}}
    <div class="bg-slate-900/50 backdrop-blur-sm rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex gap-3 items-center">
                        <h2 class="text-2xl font-bold text-slate-100">
                           {{ $this->dataset->display_name }}
                        </h2>
                        <span class="flex-shrink-0 grow-0 px-2 py-0.5 rounded-full text-xs whitespace-nowrap {{ $this->dataset->annotation_technique === 'Bounding box' ? 'bg-green-900/50 text-green-300' : 'bg-blue-900/50 text-blue-300' }}">
                            {{ $this->dataset->annotation_technique }}
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-slate-400 max-w-2xl">
                        {{ empty($this->dataset->description) ? 'No description available' : $this->dataset->description }}
                    </p>
                </div>
                <div class="flex gap-3 text-slate-100">
                    <div class="text-center bg-slate-700/40 rounded-lg p-2">
                        <div class="text-2xl font-bold">{{$this->dataset->num_images ?? 'N/A'}}</div>
                        <div class="text-xs text-slate-400">Images</div>
                    </div>
                    <div class="text-center bg-slate-700/40 rounded-lg p-2">
                        <div class="text-2xl font-bold">{{count($this->classes)}}</div>
                        <div class="text-xs text-slate-400">Labels</div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-x-12 gap-y-4">
                <div class="flex items-center gap-3">
                    <span class="text-sm text-slate-400">Categories:</span>
                    @foreach($this->categories as $category)
                        <span class="bg-blue-500/20 text-blue-300 px-3 py-1.5 rounded-md text-sm font-medium">
                {{$category['name']}} <!-- Accessing 'name' key from array -->
            </span>
                    @endforeach
                </div>
                @foreach($this->metadata as $metadata)
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-slate-400">{{$metadata['type']['name']}}:</span> <!-- Accessing 'type' and 'name' keys -->
                        @foreach($metadata['values'] as $metadataValue) <!-- Accessing 'values' array -->
                        <span class="text-sm text-slate-300">{{$metadataValue['value']}}</span> <!-- Accessing 'value' key -->
                        @endforeach
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</div>
