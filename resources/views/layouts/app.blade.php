<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('layouts.includes.head')
</head>

    <body class="text-gray-200" x-data="">

        <div class="bg-gradient-to-b from-[#243240] to-slate-900 flex flex-col min-h-screen">

            <nav class="">
                <livewire:navbar />
            </nav>

            <main class="relative inset-0 flex-1 overflow-visible">
                <div class="container mx-auto mt-5 mb-14">
                    {{$slot}}
                </div>
            </main>

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
