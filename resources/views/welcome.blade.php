<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased font-sans">
    <div>

        <x-mary-form wire:submit="save">
            <x-mary-input label="Name" wire:model="name" />
            <x-mary-input label="Amount" wire:model="amount" prefix="USD" money hint="It submits an unmasked value" />

            <x-slot:actions>
                <x-mary-button label="Cancel" />
                <x-mary-button label="Click me!" class="btn-primary" type="submit" spinner="save" />
            </x-slot:actions>
        </x-mary-form>
    </div>
</body>

</html>
