<flux:card class="space-y-6">
    @if (session()->has('error'))
        <flux:callout icon="exclamation-triangle" variant="danger">
            <flux:callout.heading>Error</flux:callout.heading>
            <flux:callout.text>{{ session('error') }}</flux:callout.text>
        </flux:callout>
    @endif

    <div>
        <flux:heading size="lg">Select Your Winner</flux:heading>
        <flux:subheading>Choose one entry as the winner. You can only select one entry.</flux:subheading>
    </div>

    <div class="space-y-3">
        @foreach($entries as $entry)
            <label class="flex items-start gap-3 p-4 border rounded-lg cursor-pointer transition-all hover:border-gray-400 focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-2 {{ $selectedEntryId == $entry->id ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-400' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800' }}">
                <input
                    type="radio"
                    name="winner"
                    value="{{ $entry->id }}"
                    wire:model.live="selectedEntryId"
                    class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500 dark:border-gray-600 dark:focus:ring-blue-400 mt-0.5"
                />
                <div class="flex-1">
                    <div class="font-medium text-gray-900 dark:text-gray-100"
                         @if($entry->description && $contest->entry_description_display_type === 'tooltip')
                             title="{{ $entry->description }}"
                         @endif
                    >{{ $entry->name }}</div>
                    @if($entry->description && $contest->entry_description_display_type !== 'hidden' && $contest->entry_description_display_type !== 'tooltip')
                        <div class="text-sm text-zinc-500 dark:text-white/70 mt-1">{{ $entry->description }}</div>
                    @endif
                </div>
                @if($selectedEntryId == $entry->id)
                    <div class="ml-3">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.293a1 1 0 00-1.414-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a1 1 0 001.137-.089l4-5.5z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                @endif
            </label>
        @endforeach
    </div>

    @if($selectedEntryId)
        <flux:callout icon="check-circle">
            <flux:callout.heading>Vote Recorded</flux:callout.heading>
            <flux:callout.text>
                Your vote has been recorded for: {{ $entries->firstWhere('id', $selectedEntryId)->name }}
            </flux:callout.text>
        </flux:callout>
    @endif
</flux:card>
