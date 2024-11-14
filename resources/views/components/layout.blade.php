<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>

        <!-- Fonts -->
        <link href="https://fonts.bunny.net" rel="preconnect">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="h-full font-sans antialiased">

        <div class="min-h-full">
            <livewire:layout.navigation />

            <header class="bg-white shadow">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 sm:flex sm:justify-between">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ $heading }}</h1>

                    {{-- <x-button href="/jobs/create">
                        Create Job
                    </x-button> --}}
                </div>
            </header>


            <main class="px-4 py-6 sm:px-6 lg:px-8 max-w-7xl mx-auto">
                {{ $slot }}
            </main>
        </div>
    </body>

</html>
