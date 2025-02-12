<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('layouts.includes.head')
</head>

<body class="text-gray-200 ">

<!-- Full height container to ensure footer stays at the bottom -->
<div class="bg-gradient-to-b from-[#243240] to-slate-900 flex flex-col min-h-screen">

    <!-- Main content that grows to fill the available space -->
    <main class="container relative inset-0 flex-1 overflow-auto">
        {{$slot}}
    </main>

</div>

<x-reusable.flash-message />
</body>

</html>
