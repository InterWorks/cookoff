<x-layout>
    <x-slot:heading>
        <?= $contest->name ?>
    </x-slot:heading>

    <div class="mt-4 block px-4 py-6 border border-gray-200 rounded-lg">
        <a href="<?= route('contests.vote', ['contest' => $contest->id]) ?>" wire:navigate>Vote in Contest</a>

        <div class="mt-6">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode(route('contests.vote', ['contest' => $contest->id])) ?>" alt="QR Code">
        </div>
    </div>

    @foreach ($contest->getWinningEntries() as $place => $winner)
        <div class="mt-4 block px-4 py-6 border border-gray-200 rounded-lg">
            <h2 class="text-3xl font-semibold"><?= $place ?> Place</h2>
            <p class="text-lg"><?= $winner['entry']->name ?></p>
            <p class="text-sm text-gray-500">Score: <?= $winner['rating'] ?></p>
        </div>
    @endforeach
</x-layout>
