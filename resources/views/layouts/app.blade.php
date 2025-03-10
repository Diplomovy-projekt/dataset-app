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
            <main class="container relative inset-0 flex-1 overflow-auto">
                {{$slot}}
            </main>

            <!-- Footer or bottom element -->
            <footer class=" border-t border-gray-600 text-white py-4">
                <div class="container mx-auto">
                    <div class="flex justify-between items-center">
                        <div>
                            <p>&copy; 2025 Dataset Builder.</p>
                        </div>
                        <div>
                            <a href="#" class="text-white hover:text-gray-200">Privacy Policy</a>
                            <span class="mx-2">|</span>
                            <a href="#" class="text-white hover:text-gray-200">Terms of Service</a>
                        </div>
                    </div>
                </div>
            </footer>
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
