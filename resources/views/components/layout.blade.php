@props(['fullWidth' => false])

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
        @fluxAppearance
        @livewireStyles

        <link rel="icon" href="{{ Vite::asset('resources/images/iw_logo.svg') }}" type="image/svg+xml">
    </head>

    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <!-- Desktop header -->
        <flux:header container sticky class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:brand href="{{ route('home') }}" title="InterWorks Cook-off" logo="{{ Vite::asset('resources/images/iw_logo.svg') }}" class="max-lg:hidden dark:hidden pt-4" inset="top bottom" />
            <flux:brand href="{{ route('home') }}" title="InterWorks Cook-off" logo="{{ Vite::asset('resources/images/iw_logo.svg') }}" class="max-lg:!hidden hidden dark:flex filter dark:invert pt-4" inset="top bottom"/>

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item href="/">Home</flux:navbar.item>
                <flux:navbar.item href="/contests">Contests</flux:navbar.item>
                <flux:navbar.item href="/admin">Admin</flux:navbar.item>
            </flux:navbar>

            <flux:spacer />

            @auth
                <flux:dropdown position="top" align="start">
                    <flux:button icon="user" class="hidden sm:flex sm:items-center sm:ms-6">
                        <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                    </flux:button>

                    <flux:menu>
                        <flux:menu.item icon="user" :href="route('profile')">
                            {{ __('Profile') }}
                        </flux:menu.item>
                        <flux:menu.item icon="arrow-right-start-on-rectangle" wire:click="logout">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            @endauth
        </flux:header>

        <!-- Mobile sidebar -->
        <flux:sidebar stashable sticky class="lg:hidden bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <flux:brand href="{{ route('home') }}" title="InterWorks Cook-off" logo="{{ Vite::asset('resources/images/iw_logo.svg') }}" name="InterWorks Cook-off" class="px-2 dark:hidden">
                <x-slot:logo><img class="pt-1" src="{{ Vite::asset('resources/images/iw_logo.svg') }}"></x-slot:logo>
            </flux:brand>
            <flux:brand href="{{ route('home') }}" title="InterWorks Cook-off" name="InterWorks Cook-off" class="px-2 hidden dark:flex">
                <x-slot:logo><img class="pt-1 filter dark:invert" src="{{ Vite::asset('resources/images/iw_logo.svg') }}"></x-slot:logo>
            </flux:brand>

            <flux:navlist variant="outline">
                <flux:navlist.item icon="home" href="/">Home</flux:navlist.item>
                <flux:navlist.item icon="trophy" href="/contests">Contests</flux:navlist.item>
                <flux:navlist.item icon="lock-closed" href="/admin">Admin</flux:navlist.item>
            </flux:navlist>

            <flux:spacer />

            @auth
                <flux:navlist variant="outline">
                    <flux:navlist.item icon="user" :href="route('profile')">
                        {{ __('Profile') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="arrow-right-start-on-rectangle" wire:click="logout">
                        {{ __('Log Out') }}
                    </flux:navlist.item>
                </flux:navlist>
            @endauth
        </flux:sidebar>

        @if (!empty($fullWidth))
            <!-- full width {{ $fullWidth }} -->
        @endif
        <flux:main :container="empty($fullWidth)">
            <!-- Breadcrumbs -->
            @if (!empty($breadcrumbs))
                <flux:breadcrumbs class="mb-8 -mt-4 hidden sm:flex">
                    {{ $breadcrumbs }}
                </flux:breadcrumbs>
            @endif
            <flux:heading size="xl">{{ $heading }}</flux:heading>
            @if(!empty($subheading))
                <flux:subheading size="xl">{{ $subheading }}</flux:subheading>
            @endif
            {{ $slot }}
        </flux:main>

        @livewireScripts

        <!-- Manual Flux scripts -->
        <script>
        {!! file_get_contents(base_path('vendor/livewire/flux/dist/flux.min.js')) !!}
        </script>
    </body>
</html>
