<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
@vite('resources/css/app.css')
@vite('resources/js/app.js')
<script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
@stack('scripts')
<title>{{ $title ?? 'Page Title' }}</title>
@yield('additional_head')
@livewireScripts
<script src="https://unpkg.com/@nextapps-be/livewire-sortablejs@0.4.0/dist/livewire-sortable.js"></script>
<script src="https://unpkg.com/konva@9.3.16/konva.js"></script>
