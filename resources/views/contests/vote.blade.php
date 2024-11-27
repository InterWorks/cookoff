<x-layout>
    <x-slot:heading>
        Vote in <?= $contest->name ?>
    </x-slot:heading>
    <p class="mb-10"><?= $contest->description ?></p>

    @if ($contest->isVotingOpen())
        <!-- Desktop View -->
        <table class="mt-4 hidden md:block">
            <thead>
                <tr>
                    <th>Factors</th>
                    @foreach ($contest->entries as $entry)
                        <th
                            @if ($contest->entry_description_display_type == 'tooltip')
                                title="{{ $entry->description }}"
                            @endif>
                            {{ $entry->name }}
                            @if ($contest->entry_description_display_type == 'inline')
                                <p class="text-sm text-gray-500" title="{{ $entry->description }}">
                                    {{ Str::limit($entry->description, 30) }}
                                </p>
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($contest->ratingFactors as $ratingFactor)
                    <tr>
                        <td
                            @if ($contest->entry_description_display_type == 'tooltip')
                                title="{{ $ratingFactor->description }}"
                            @endif>
                            {{ $ratingFactor->name }}
                            @if ($contest->entry_description_display_type == 'inline')
                                <p class="text-sm text-gray-500" title="{{ $ratingFactor->description }}">
                                    {{ Str::limit($ratingFactor->description, 30) }}
                                </p>
                            @endif
                        </td>
                        @foreach ($contest->entries as $entry)
                            <td>
                                <livewire:vote-rating :contest="$contest" :entry="$entry" :ratingFactor="$ratingFactor" />
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Mobile View -->
        <div class="md:hidden">
            @foreach ($contest->entries as $entry)
                <div class="mb-8 border-b pb-6">
                    <h3 class="font-bold text-lg mb-2">
                        {{ $entry->name }}
                        @if ($contest->entry_description_display_type == 'inline' || $contest->entry_description_display_type == 'tooltip')
                            <p class="text-sm text-gray-500">{{ $entry->description }}</p>
                        @endif
                    </h3>
                    @foreach ($contest->ratingFactors as $ratingFactor)
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $ratingFactor->name }}</label>
                            @if ($contest->entry_description_display_type == 'inline' || $contest->entry_description_display_type == 'tooltip')
                                <p class="text-sm text-gray-500">{{ $ratingFactor->description }}</p>
                            @endif
                            <livewire:vote-rating :contest="$contest" :entry="$entry" :ratingFactor="$ratingFactor" />
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @else
        <p class="mt-4">Voting is closed for this contest.</p>
        @if (isset($contest->voting_window_opens_at) && isset($contest->voting_window_closes_at))
            <p class="mt-4">
                @if (now()->isBefore($contest->voting_window_opens_at))
                    Voting will be open from
                @else
                    Voting was open from
                @endif
                <span class="timestamp font-bold" data-timestamp="{{ $contest->voting_window_opens_at->toIsoString() }}">
                    <?= $contest->voting_window_opens_at->format('F j, Y g:i A') ?>
                </span>
                to
                <span class="timestamp font-bold" data-timestamp="{{ $contest->voting_window_closes_at->toIsoString() }}">
                    <?= $contest->voting_window_closes_at->format('F j, Y g:i A') ?>
                </span>.
            </p>
        @elseif (isset($contest->voting_window_opens_at))
            <p class="mt-4">
                Voting starts at
                <span class="timestamp font-bold" data-timestamp="{{ $contest->voting_window_opens_at->toIsoString() }}">
                    <?= $contest->voting_window_opens_at->format('F j, Y g:i A') ?>
                </span>.
            </p>
        @elseif (isset($contest->voting_window_closes_at))
            <p class="mt-4">
                Voting was open until
                <span class="timestamp font-bold" data-timestamp="{{ $contest->voting_window_closes_at->toIsoString() }}">
                    <?= $contest->voting_window_closes_at->format('F j, Y g:i A') ?>
                </span>.
            </p>
        @else
            <p class="mt-4">I'm unsure of the voting window.</p>
        @endif
    @endif

    <div class="mt-6">
        <a href="<?= route('contests.show', ['contest' => $contest->id]) ?>"
            class="hover:underline"
            wire:navigate>
            See Results
        </a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const timestamps = document.querySelectorAll('.timestamp');
            timestamps.forEach(function (timestamp) {
                const utcDate         = new Date(timestamp.getAttribute('data-timestamp'));
                const options         = {
                    year        : 'numeric',
                    month       : 'long',
                    day         : 'numeric',
                    hour        : 'numeric',
                    minute      : 'numeric',
                    timeZoneName: 'short',
                    second      : undefined // Ensure seconds are not displayed
                };
                const localDate       = utcDate.toLocaleString(undefined, options);
                timestamp.textContent = localDate;
            });
        });
    </script>
</x-layout>
