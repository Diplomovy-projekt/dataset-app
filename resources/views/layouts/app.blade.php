<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('layouts.includes.head')
</head>

    <body class="text-gray-200 ">

    <!-- Full height container to ensure footer stays at the bottom -->
        <div class="bg-gradient-to-b from-[#243240] to-slate-900 flex flex-col min-h-screen">
            {{-- bg-gradient-to-b from-[#243240] to-slate-900 CURRENT --}}
            {{--  bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 CLAUDE--}}
            <!-- Navbar (fixed at the top) -->
            <nav class="">
                <livewire:navbar />
            </nav>

            <!-- Main content that grows to fill the available space -->
            <main class="relative inset-0 flex-1 overflow-visible">
                <div class="container mx-auto">
                    {{$slot}}
                </div>
            </main>

            <!-- Footer -->
            @include('layouts.includes.footer')

            <x-notif></x-notif>
            <x-images.full-screen-image/>
        </div>
    </body>
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('refresh', () => {
            window.location.reload();
        });
    });
</script>
</html>
