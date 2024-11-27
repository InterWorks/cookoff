<x-layout>
    <x-slot:heading>
        Contests
    </x-slot:heading>

    @foreach ($contests as $contest)
        <div class="mt-4 block px-4 py-6 border border-gray-200 rounded-lg">
            @if ($contest->isVotingOpen())
                <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20"
                    title="{{ $contest->votingWindowTooltip }}">
                    Voting Open
                </span>
            @else
                <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10"
                    title="{{ $contest->votingWindowTooltip }}">
                    Voting Closed
                </span>
            @endif
            <h2 class="text-3xl font-semibold">
                <a href="{{ route('contests.show', ['contest' => $contest->id]) }}"
                    class="hover:underline"
                    wire:navigate>{{ $contest->name }}</a>
            </h2>
            <p class="text-lg">{{ $contest->description }}</p>
        </div>
    @endforeach
</x-layout>
