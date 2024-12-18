<x-layout>
    <x-slot:heading>
        <?= $contest->name ?>
    </x-slot:heading>
    <x-slot:subheading>
        <?= $contest->description ?>
    </x-slot:subheading>
    <x-slot:breadcrumbs>
        <flux:breadcrumbs.item icon="home" title="Home" :href="route('home')" wire:navigate>Home</flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href="route('contests.index')" wire:navigate>Contests</flux:breadcrumbs.item>
        <flux:breadcrumbs.item active><?= $contest->name ?></flux:breadcrumbs.item>
    </x-slot:breadcrumbs>

    <flux:card class="mt-4 space-y-6 block">
        <a href="<?= route('contests.vote', ['contest' => $contest->id]) ?>"
            class="group flex flex-col justify-center items-center"
            wire:navigate>
            <div class="mb-4">
                <flux:button>
                    Vote in Contest
                </flux:button>
                @if ($contest->isVotingOpen())
                    <flux:badge color="lime" title="{{ $contest->votingWindowTooltip }}" class="ml-2">
                        Voting Open
                    </flux:badge>
                @else
                    <flux:badge title="{{ $contest->votingWindowTooltip }}" class="ml-2">
                        Voting Closed
                    </flux:badge>
                @endif
            </div>

            <div class="mt-6">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode(route('contests.vote', ['contest' => $contest->id])) ?>" alt="QR Code">
            </div>
        </a>
    </flux:card>

    @foreach ($contest->getWinningEntries() as $place => $winner)
        <flux:card class="mt-4 space-y-4">
            <flux:heading size="lg"><?= $place ?> Place</flux:heading>
            <flux:heading><?= $winner['entry']->name ?></flux:heading>
            <flux:subheading class="mb-4">
                @if ($contest->entry_description_display_type == 'inline' || $contest->entry_description_display_type == 'tooltip')
                        <p><?= $winner['entry']->description ?></p>
                @endif
                <p>Score: <?= $winner['rating'] ?></p>
            </flux:subheading>
        </flux:card>
    @endforeach
</x-layout>
