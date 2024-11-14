<x-layout>
    <x-slot:heading>
        Vote in <?= $contest->name ?>
    </x-slot:heading>
    <p class="mb-10"><?= $contest->description ?></p>

    <table class="mt-4">
        <thead>
            <tr>
                <th>Factors</th>
                @foreach ($contest->entries as $entry)
                    <th>{{ $entry->name }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($contest->ratingFactors as $ratingFactor)
                <tr>
                    <td>{{ $ratingFactor->name }}</td>
                    @foreach ($contest->entries as $entry)
                        <td>
                            <livewire:vote-rating :contest="$contest" :entry="$entry" :ratingFactor="$ratingFactor" />
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-6">
        <a href="<?= route('contests.show', ['contest' => $contest->id]) ?>"
            class="hover:underline"
            wire:navigate>
            See Results
        </a>
    </div>
</x-layout>
