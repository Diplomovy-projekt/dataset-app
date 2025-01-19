@props([
    'dataset',
    'svgSize' => 'w-5 h-5' // Default size for SVG
])

<div {{ $attributes->class(['flex justify-between items-center bg-slate-800 rounded-lg space-x-5 w-max h-max', 'text-xs' => !$attributes->has('class')]) }}>
    <!-- Images -->
    <div class="flex items-center gap-1" title="Images">
        <x-icon name="o-photo" class="text-blue-400 {{ $svgSize }}" />
        <div class="font-bold text-slate-100">{{$dataset['num_images'] ?? 'N/A'}}</div>
    </div>
    <!-- Annotations -->
    <div class="flex items-center gap-1" title="Annotations">
        <x-jam-pencil class="text-green-400 {{ $svgSize }}" />
        <div class="font-bold text-slate-100">{{$dataset['annotationCount'] ?? 'N/A'}}</div>
    </div>
    <!-- Classes -->
    <div class="flex items-center gap-1 cursor-pointer" @click.prevent="open = 'display-classes'" title="Classes">
        <x-icon name="o-tag" class="text-yellow-400 {{ $svgSize }}" />
        <div class="font-bold text-slate-100">{{count($dataset['classes'])}}</div>
    </div>
</div>
