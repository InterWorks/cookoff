<x-layout>
    <x-slot:heading>
        Contests
    </x-slot:heading>

    @foreach ($contests as $contest)
        <a href="{{ route('contests.show', ['contest' => $contest->id]) }}" class="group dark:text-white-500" wire:navigate>
            <flux:card class="mt-4 space-y-6">
                <flux:heading size="lg">
                    <div class="flex justify-between decoration-inherit">
                        <span class="group-hover:underline">{{ $contest->name }}</span>
                        @if ($contest->isVotingOpen())
                            <flux:badge color="lime" title="{{ $contest->votingWindowTooltip }}" inset="top bottom">
                                Voting Open
                            </flux:badge>
                        @else
                            <flux:badge title="{{ $contest->votingWindowTooltip }}" inset="top bottom">
                                Voting Closed
                            </flux:badge>
                        @endif
                    </div>
                </flux:heading>
                <flux:subheading class="">
                    {{ $contest->description }}
                </flux:subheading>
            </flux:card>
        </a>
    @endforeach
</x-layout>
