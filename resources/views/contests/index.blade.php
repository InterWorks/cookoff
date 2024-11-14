<x-layout>
    <x-slot:heading>
        Contests
    </x-slot:heading>

    @foreach ($contests as $contest)
        <div class="mt-4 block px-4 py-6 border border-gray-200 rounded-lg">
            <h2 class="text-3xl font-semibold">
                <a href="{{ route('contests.show', ['contest' => $contest->id]) }}"
                    class="hover:underline"
                    wire:navigate>{{ $contest->name }}</a>
            </h2>
            <p class="text-lg">{{ $contest->description }}</p>
        </div>
    @endforeach
</x-layout>
