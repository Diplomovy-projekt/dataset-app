@props([
    'dataset',
])
<a @click.stop.prevent href="{{ route('dataset.show', ['uniqueName' => $dataset['unique_name']])}}"
   wire:navigate
   wire:key="{{ $dataset['unique_name'] }}"
   class="block w-60 overflow-hidden rounded-lg bg-slate-900/50 backdrop-blur-sm border border-slate-800 transition-all hover:border-slate-700 hover:bg-slate-900/60">

    {{-- Thumbnail Section --}}
    <div class="relative h-48">
        <img src="{{asset('storage/datasets/'.$dataset['unique_name']. '/thumbnails/' . $dataset['thumbnail_image'])}}"
             alt="{{ $dataset['display_name'] }}"
             class="h-full w-full object-cover"
             loading="lazy">
        <x-annotation-overlay :image="$dataset['images'][0]"></x-annotation-overlay>
    </div>

    {{-- Content Section --}}
    <div class="p-4">
        {{-- Header with technique --}}
        <div class="flex flex-col gap-2 items-start justify-between">
            <div class="flex-1">
                <div class="flex justify-between items-center gap-3">
                    <div class="flex items-center gap-3">
                        <h3 class="text-lg font-bold text-slate-100">
                            {{ $dataset['display_name'] }}
                        </h3>
                        <div class="flex-shrink-0 rounded-full px-2 py-0.5 text-xs whitespace-nowrap {{ $dataset['annotation_technique'] === 'Bounding box' ? 'bg-green-900/50 text-green-300' : 'bg-blue-900/50 text-blue-300' }}">
                            {{ $dataset['annotation_technique'] }}
                        </div>
                    </div>

                </div>
            </div>
            {{-- Stats --}}
            <div class="flex gap-2">
                <div class="text-center rounded-lg bg-slate-700/40 p-1">
                    <div class="text-lg font-bold text-slate-100">
                        {{ $dataset['num_images'] }}
                    </div>
                    <div class="text-xs text-slate-400">Images</div>
                </div>
                <div class="text-center rounded-lg bg-slate-700/40 p-1">
                    <div class="text-lg font-bold text-slate-100">
                        {{ count($dataset['classes']) }}
                    </div>
                    <div class="text-xs text-slate-400">Labels</div>
                </div>
            </div>
        </div>

        {{-- Date --}}
        <div class="mt-4 flex items-center gap-2 text-sm text-slate-400">
            <span>Last updated {{ \Carbon\Carbon::parse($dataset['updated_at'])->format('M j, Y') }}</span>
        </div>
    </div>
</a>
