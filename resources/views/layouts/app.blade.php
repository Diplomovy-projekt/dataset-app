<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('layouts.includes.head')
</head>

<body class="bg-slate-700 text-white">

<!-- Full height container to ensure footer stays at the bottom -->
<div class="flex flex-col min-h-screen">

    <!-- Navbar (fixed at the top) -->
    <nav class="bg-gray-800">
        <livewire:navbar />
    </nav>

    <!-- Main content that grows to fill the available space -->
    <main class="flex-1 overflow-auto mb-12">
        {{$slot}}
    </main>

    <!-- Footer or bottom element -->
    <footer class="bg-gray-900 text-white py-4">
        <div class="text-center">© 2024 Your Website</div>
    </footer>

</div>

<x-reusable.flash-message />
</body>

</html>
