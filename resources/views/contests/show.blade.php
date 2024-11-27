<x-layout>
    <x-slot:heading>
        <?= $contest->name ?>
    </x-slot:heading>
    <p class="mb-10"><?= $contest->description ?></p>

    <div class="mt-4 block px-4 py-6 border border-gray-200 rounded-lg">
        <a href="<?= route('contests.vote', ['contest' => $contest->id]) ?>"
            class="hover:underline"
            wire:navigate>
            Vote in Contest
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

            <div class="mt-6">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode(route('contests.vote', ['contest' => $contest->id])) ?>" alt="QR Code">
            </div>
        </a>
    </div>

    @foreach ($contest->getWinningEntries() as $place => $winner)
        <div class="mt-4 block px-4 py-6 border border-gray-200 rounded-lg">
            <h2 class="text-3xl font-semibold"><?= $place ?> Place</h2>
            <p class="text-lg"><?= $winner['entry']->name ?></p>
            @if ($contest->entry_description_display_type == 'inline' || $contest->entry_description_display_type == 'tooltip')
                <p class="text-sm text-gray-500"><?= $winner['entry']->description ?></p>
            @endif
            <p class="text-sm text-gray-500">Score: <?= $winner['rating'] ?></p>
        </div>
    @endforeach
</x-layout>
