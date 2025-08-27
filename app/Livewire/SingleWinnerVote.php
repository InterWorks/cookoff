<?php

namespace App\Livewire;

use App\Models\Contest;
use App\Models\Entry;
use App\Models\Vote;
use Exception;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class SingleWinnerVote extends Component
{
    public Contest $contest;

    public $selectedEntryId = null;

    private Vote $vote;

    /**
     * Mount the component with contest data.
     *
     * @param Contest $contest The contest to vote in.
     *
     * @return void
     * @throws Exception If vote is not associated with correct contest.
     */
    public function mount(Contest $contest): void
    {
        $this->contest = $contest;

        $sessionKey = 'vote_' . $this->contest->getKey();
        $this->vote = Session::get($sessionKey, function () use ($sessionKey) {
            $vote = Vote::create([
                'contest_id' => $this->contest->id,
            ]);
            Session::put($sessionKey, $vote);

            return $vote;
        });

        // Fail safe to ensure the vote is always associated with the correct contest
        if (! $this->vote->contest->is($this->contest)) {
            throw new Exception('Vote is not associated with the correct contest');
        }

        // Find the currently selected entry (the one with rating 1)
        $selectedRating = $this->vote->voteRatings()
            ->where('rating', 1)
            ->first();

        $this->selectedEntryId = $selectedRating ? $selectedRating->entry_id : null;
    }

    /**
     * Handle updates to selected entry ID.
     *
     * @param mixed $value The new selected entry ID.
     *
     * @return void
     */
    public function updatedSelectedEntryId(mixed $value): void
    {
        $this->updateVotes();
    }

    /**
     * Select the winning entry.
     *
     * @param integer $entryId The ID of the entry to select as winner.
     *
     * @return void
     */
    public function selectWinner(int $entryId): void
    {
        $this->selectedEntryId = $entryId;
        $this->updateVotes();
    }

    /**
     * Update the votes based on current selection.
     *
     * @return void
     */
    private function updateVotes(): void
    {
        $sessionKey = 'vote_' . $this->contest->getKey();
        $this->vote = Session::get($sessionKey);

        // Get the default rating factor (we'll use the first one for single winner)
        $ratingFactor = $this->contest->ratingFactors()->first();

        if (! $ratingFactor) {
            // Create a default rating factor if none exists
            $ratingFactor = $this->contest->ratingFactors()->create([
                'name'        => 'Winner',
                'description' => 'Select the winning entry',
            ]);
        }

        // Reset all ratings to 0
        foreach ($this->contest->entries as $entry) {
            $voteRating = $this->vote->voteRatings()
                ->where('entry_id', $entry->id)
                ->where('rating_factor_id', $ratingFactor->id)
                ->first();

            if ($voteRating) {
                $voteRating->update(['rating' => 0]);
            } else {
                $this->vote->voteRatings()->create([
                    'entry_id'         => $entry->id,
                    'rating_factor_id' => $ratingFactor->id,
                    'rating'           => 0,
                ]);
            }
        }

        // Set selected entry to rating 1
        if ($this->selectedEntryId) {
            $selectedVoteRating = $this->vote->voteRatings()
                ->where('entry_id', $this->selectedEntryId)
                ->where('rating_factor_id', $ratingFactor->id)
                ->first();

            if ($selectedVoteRating) {
                $selectedVoteRating->update(['rating' => 1]);
            }
        }

        $this->vote->refreshSummary();
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.single-winner-vote', [
            'contest' => $this->contest,
            'entries' => $this->contest->entries,
        ]);
    }
}
