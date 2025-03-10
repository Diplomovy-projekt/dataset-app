@props([
    'dataset',
])
<a @click.stop.prevent href="{{ route('dataset.show', ['uniqueName' => $dataset['unique_name']])}}"
   wire:navigate
   wire:key="{{ $dataset['unique_name'] }}"
   class="block w-60 overflow-hidden rounded-lg bg-slate-900 backdrop-blur-sm border border-slate-800 transition-all hover:border-slate-700 ">

    {{-- Thumbnail Section --}}
    <x-images.annotated-image :image="$dataset['images'][0] ?? null" class="h-48"/>

    {{-- Content Section --}}
    <div class="p-4">
        {{-- Header with technique --}}
        <div class="flex flex-col gap-2 items-start justify-between">
            <div class="flex-1 flex items-center gap-3 w-full">
                <h3 class="text-lg font-bold text-slate-100 truncate flex-shrink min-w-0">
                    {{ $dataset['display_name'] }}
                </h3>
                <x-dataset.annot_technique :annot_technique="$dataset['annotation_technique']" />
            </div>
            {{-- Stats --}}
            <x-dataset.dataset-stats :stats="$dataset['stats']" class="bg-slate-900"/>
        </div>

        {{-- Date --}}
        <div class="mt-4 flex items-center gap-2 text-sm text-slate-400">
            <span>Last updated {{ \Carbon\Carbon::parse($dataset['updated_at'])->format('M j, Y') }}</span>
        </div>
    </div>
</a>
