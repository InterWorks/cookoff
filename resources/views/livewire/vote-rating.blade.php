
<div>
    <form onsubmit="return false;">
        <div wire:key="{{ $componentID }}" class="relative">
            <input type="text"
                wire:model.live.debounce="rating"
                wire:change="updateRating"
                wire:target="updateRating"
                wire:loading.class="bg-yellow-100"
                class="form-control rating-input @error('rating') bg-red-100 border-red-500 @enderror"
                placeholder="Enter your rating">
            <span wire:target="updateRating"
                wire:loading.class="inline-block"
                wire:loading.class.remove="hidden"
                class="bg-yellow-100 hidden absolute right-3 top-1/2 transform -translate-y-1/2">
                <svg class="inline-block h-5 w-5 text-gray-500 animate-spin"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4.5 12a7.5 7.5 0 017.5-7.5V3m0 18v-1.5a7.5 7.5 0 007.5-7.5H21" />
                </svg>
            </span>
            <span class="text-red-500">@error('rating'){{ $message }}@enderror</span>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputs = document.querySelectorAll('.rating-input');
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
