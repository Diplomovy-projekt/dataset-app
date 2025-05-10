@props([
    'dataset',
    'folder',
    'filename',
])

@php
    $folder = trim($folder, '/');
    $publicPath = asset("storage/datasets/{$dataset}/{$folder}/{$filename}");
    $privatePath = route('private.image', ['dataset' => $dataset, 'filename' => base64_encode("{$folder}/{$filename}")]);
@endphp

<img src="{{ $publicPath }}"
     onerror="
        if (!this.dataset.failed) {
            this.dataset.failed = true;
            this.src = this.dataset.privateSrc;
        }
     "
     data-private-src="{{ $privatePath }}"
     alt="{{ $filename ?? basename($src) }}"
     {{ $attributes->except('@click') }}
     @if ($attributes->has('@click'))
         @click="{{ $attributes->get('@click') }}"
    @endif
>

