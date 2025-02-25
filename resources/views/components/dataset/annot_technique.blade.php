@props(
    [
        'annot_technique',
    ]
)
<div class="w-fit flex-shrink-0 grow-0 px-2 py-0.5 rounded-full text-xs whitespace-nowrap {{ $annot_technique === 'Bounding box' ? 'bg-green-900/50 text-green-300' : 'bg-blue-900/50 text-blue-300' }}">
    {{ $annot_technique }}
</div>
