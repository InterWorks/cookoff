<?php

use Livewire\Volt\Component;

new class extends Component {
    public array $nextSteps = [
        'Run composer install --dev',
        'Run npm install && npm run dev',
        'Start building your application',
    ];

    public array $tips = [
        'php artisan make:volt component-name --class',
    ];
}; ?>

<main class="grid min-h-full place-items-center bg-white px-6 py-24 sm:py-32 lg:px-8">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <div class="text-center">
        <img src="{{ Vite::asset('resources/images/iw_logo.svg') }}" alt="InterWorks Logo">
        <p class="mt-8 text-3xl font-semibold text-indigo-600">InterWorks Cook-Off Contest Voting</p>

        {{-- <div class="mt-8 block max-w-md rounded-lg border border-gray-200 bg-white p-6 shadowdark:border-gray-700 dark:bg-gray-800">
            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Next steps:</h5>
            <ul class="max-w-md list-inside space-y-1 text-gray-500 dark:text-gray-400">
                @foreach ($nextSteps as $step)
                    <li class="flex items-center">
                        <i class="fa-solid fa-arrow-right"></i>&nbsp;
                        {{ $step }}
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="mt-8 block max-w-md rounded-lg border border-gray-200 bg-white p-6 shadowdark:border-gray-700 dark:bg-gray-800">
            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Tips:</h5>
            <ul class="max-w-md list-inside space-y-1 text-gray-500 dark:text-gray-400">
                @foreach ($tips as $tip)
                    <li class="flex items-center">
                        <i class="fa-solid fa-arrow-right"></i>&nbsp;
                        {{ $tip }}
                    </li>
                @endforeach
            </ul>
        </div> --}}
    </div>
</main>
