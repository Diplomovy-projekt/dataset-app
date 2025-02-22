@props([
    'title' => '',
    'info' => ''
])
<div class=" mb-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-200">{{$title}}</h1>
        <div class="h-px flex-1 bg-gradient-to-r from-transparent via-slate-700 to-transparent mx-6"></div>
    </div>
    <p class="mt-1 text-sm text-gray-400">{{ $info }}</p>
</div>
