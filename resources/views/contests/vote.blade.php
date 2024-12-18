<x-layout :fullWidth="$contest->isVotingOpen()">
    <x-slot:heading>
        Vote in <?= $contest->name ?>
    </x-slot:heading>
    <x-slot:subheading>
        <?= $contest->description ?>
    </x-slot:subheading>
    <x-slot:breadcrumbs>
        <flux:breadcrumbs.item icon="home" title="Home" :href="route('home')" wire:navigate>Home</flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href="route('contests.index')" wire:navigate>Contests</flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href="route('contests.show', ['contest' => $contest->id])" wire:navigate><?= $contest->name ?></flux:breadcrumbs.item>
        <flux:breadcrumbs.item active>Vote</flux:breadcrumbs.item>
    </x-slot:breadcrumbs>

    @if ($contest->isVotingOpen())
        <!-- Desktop View -->
        <flux:table class="hidden lg:table">
            <flux:columns>
                <flux:column>Factors</flux:column>
                @foreach ($contest->entries as $entry)
                    @php
                        if ($contest->entry_description_display_type == 'tooltip'):
                            $tooltip = $entry->description;
                        else:
                            $tooltip = null;
                        endif;
                    @endphp
                    <flux:column :title="$tooltip">
                        {{ $entry->name }}
                        @if ($contest->entry_description_display_type == 'inline')
                            <p class="text-sm text-gray-500" title="{{ $entry->description }}">
                                {{ Str::limit($entry->description, 30) }}
                            </p>
                        @endif
                    </flux:column>
                @endforeach
            </flux:columns>
            <flux:rows>
                @foreach ($contest->ratingFactors as $ratingFactor)
                    <flux:row :key="$ratingFactor->id">
                        @php
                            if ($contest->entry_description_display_type == 'tooltip'):
                                $tooltip = $ratingFactor->description;
                            else:
                                $tooltip = null;
                            endif;
                        @endphp
                        <flux:cell :title="$tooltip" class="!pl-1">
                            {{ $ratingFactor->name }}
                            @if ($contest->entry_description_display_type == 'inline')
                                <p class="text-sm text-gray-500" title="{{ $ratingFactor->description }}">
                                    {{ Str::limit($ratingFactor->description, 30) }}
                                </p>
                            @endif
                        </flux:cell>
                        @foreach ($contest->entries as $entry)
                            <flux:cell>
                                <livewire:vote-rating :contest="$contest" :entry="$entry" :ratingFactor="$ratingFactor" mode="Desktop"/>
                            </flux:cell>
                        @endforeach
                    </flux:row>
                @endforeach
            </flux:rows>
        </flux:table>

        <!-- Mobile View -->
        <div class="mt-8 lg:hidden">
            @foreach ($contest->entries as $entry)
                <div class="mb-8 border-b pb-6">
                    <flux:heading size="lg">{{ $entry->name }}</flux:heading>
                    @if ($contest->entry_description_display_type == 'inline' || $contest->entry_description_display_type == 'tooltip')
                        <flux:subheading>{{ $entry->description }}</flux:subheading>
                    @endif
                    @foreach ($contest->ratingFactors as $ratingFactor)
                        <flux:field class="mb-4 mt-4">
                            <flux:label>{{ $ratingFactor->name }}</flux:label>
                            @if ($contest->entry_description_display_type == 'inline' || $contest->entry_description_display_type == 'tooltip')
                                <flux:description>{{ $ratingFactor->description }}</flux:description>
                            @endif
                            <livewire:vote-rating :contest="$contest" :entry="$entry" :ratingFactor="$ratingFactor" :mode="'Mobile'" />
                        </flux:field>
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

    <flux:button :href="route('contests.show', ['contest' => $contest->id])" class="mt-4">See Results</flux:button>

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
