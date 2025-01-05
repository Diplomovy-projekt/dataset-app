<div class="flex flex-col">
    {{-- Dataset Header Section --}}
    <div class="bg-slate-900/50 backdrop-blur-sm rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex gap-3 items-center">
                        <h2 class="text-2xl font-bold text-slate-100">
                           {{ $this->dataset['display_name'] }}
                        </h2>
                        <span class="flex-shrink-0 grow-0 px-2 py-0.5 rounded-full text-xs whitespace-nowrap {{ $this->dataset['annotation_technique'] === 'Bounding box' ? 'bg-green-900/50 text-green-300' : 'bg-blue-900/50 text-blue-300' }}">
                            {{ $this->dataset['annotation_technique'] }}
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-slate-400 max-w-2xl">
                        {{ empty($this->dataset['description']) ? 'No description available' : $this->dataset['description'] }}
                    </p>
                </div>
                {{-- Statistics --}}
                <div class="flex justify-between items-center bg-slate-800 rounded-lg px-4 py-2  space-x-5">
                    <!-- Images -->
                    <div class="flex items-center gap-1" title="Images">
                        <x-icon name="o-photo" class="text-blue-400 w-5 h-5" />
                        <div>
                            <div class="text-xl font-bold text-slate-100">{{$this->dataset['num_images'] ?? 'N/A'}}</div>
                        </div>
                    </div>
                    <!-- Annotations -->
                    <div class="flex items-center gap-1" title="Annotations">
                        <x-jam-pencil class="text-green-400 w-5 h-5"/>
                        <div>
                            <div class="text-xl font-bold text-slate-100">{{$this->dataset['annotationCount'] ?? 'N/A'}}</div>
                        </div>
                    </div>
                    <!-- Classes -->
                    <div class="flex items-center gap-1 cursor-pointer" @click.prevent="open = 'display-classes'" title="Classes">
                        <x-icon name="o-tag" class="text-yellow-400 w-5 h-5"/>
                        <div>
                            <div class="text-xl font-bold text-slate-100">{{count($this->dataset['classes'])}}</div>
                        </div>
                    </div>
                </div>

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
