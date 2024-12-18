
@props(['mode' => 'Default'])

<div>
    <form onsubmit="return false;">
        <flux:field>
            <flux:input :title="$entry->name . ' and ' . $ratingFactor->name"
                type="text"
                :class="$mode == 'Desktop' ? 'rating-input' : ''"
                placeholder="Enter your rating"
                wire:model="rating"
                wire:change="updateRating"
                wire:target="updateRating"
                wire:loading.class="bg-yellow-100"
            >
                <x-slot name="iconTrailing">
                    <flux:icon.loading wire:target="updateRating" wire:loading />
                    @error('rating')
                        <flux:icon.exclamation-triangle class="text-red-500"/>
                    @enderror
                </x-slot>
            </flux:input>
            <flux:error name="rating" />
        </flux:field>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputs = document.querySelectorAll('.rating-input input');
        inputs.forEach((input, index) => {
            input.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    // Change focus to the input in the next row but same column
                    const countOfEntries            = <?= count($contest->entries) ?>;
                    const countOfRatingFactors      = <?= count($contest->ratingFactors) ?>;
                    const columnIndexOfCurrentInput = index % countOfEntries;
                    const rowIndexOfCurrentInput    = Math.floor(index / countOfEntries);
                    const isInLastRow               = rowIndexOfCurrentInput === (countOfRatingFactors - 1);
                    const nextRow                   = isInLastRow ? 0 : rowIndexOfCurrentInput + 1;
                    const isInLastColumn            = columnIndexOfCurrentInput === (countOfEntries - 1);
                    const nextColumn                = isInLastRow
                        ? (isInLastColumn ? 0 : columnIndexOfCurrentInput + 1)
                        : columnIndexOfCurrentInput;
                    const nextIndex                 = (nextRow * countOfEntries) + nextColumn;
                    inputs[nextIndex].focus();
                } else if (event.key == 'ArrowRight') {
                    const nextIndex = index + 1;
                    inputs[nextIndex].focus();
                } else if (event.key == 'ArrowLeft') {
                    const nextIndex = index - 1;
                    inputs[nextIndex].focus();
                } else if (event.key == 'ArrowUp') {
                    const nextIndex = index - <?= count($contest->entries) ?>;
                    inputs[nextIndex].focus();
                } else if (event.key == 'ArrowDown') {
                    const nextIndex = index + <?= count($contest->entries) ?>;
                    inputs[nextIndex].focus();
                } else {
                    // Do nothing
                }
            });
        });
    });
</script>
