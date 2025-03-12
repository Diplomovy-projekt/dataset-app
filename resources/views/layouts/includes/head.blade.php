<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
@vite('resources/css/app.css')
@vite('resources/js/app.js')
<script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
{{--
<script src="https://cdnjs.cloudflare.com/ajax/libs/pixi.js/8.6.6/pixi.min.js" integrity="sha512-9Che/pADxtzmgRM/Lt7g+wgmgVPNu4qLCOjH+owFqCSpd9HHCi1fMYp+XtfE8nOdRQWUsD0TUNQUc5Z1SwaLyw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
--}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pixi.js/7.4.2/pixi.min.js" integrity="sha512-PaaQR/Kf0+ItZTMVjdgJRUcrktec2B3m6XM+TiflUXy5hAF7eVys9dETQO4UX3/A/0H6KJf+e3D2VMVMlywAdA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

@stack('scripts')
<title>{{ $title ?? 'Dataset builder' }}</title>
@yield('additional_head')
@livewireScripts
@livewireStyles
{{--<script src="https://unpkg.com/@nextapps-be/livewire-sortablejs@0.4.0/dist/livewire-sortable.js"></script>--}}
