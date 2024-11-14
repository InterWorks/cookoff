<?php

namespace App\Livewire;

use App\Models\Contest;
use App\Models\Entry;
use App\Models\RatingFactor;
use App\Models\Vote;
use Exception;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Validate;
use Livewire\Component;

class VoteRating extends Component
{
    public $contest;
    public $entry;
    public $ratingFactor;

    #[Validate('numeric')]
    public $rating = null;

    private Vote $vote;

    /**
     * Mount the component.
     *
     * @param Contest      $contest      The contest the entry belongs to.
     * @param Entry        $entry        The entry to vote on.
     * @param RatingFactor $ratingFactor The rating factor to vote on.
     *
     * @return void
     *
     * @throws Exception If the vote is not associated with the correct contest.
     */
    public function mount(Contest $contest, Entry $entry, RatingFactor $ratingFactor): void
    {
        $this->contest      = $contest;
        $this->entry        = $entry;
        $this->ratingFactor = $ratingFactor;
        $sessionKey         = "vote_" . $this->contest->getKey();
        $this->vote         = Session::get($sessionKey, function () use ($sessionKey) {
            $vote = Vote::create([
                'contest_id' => $this->contest->id,
            ]);
            Session::put($sessionKey, $vote);
            return $vote;
        });

        // Fail safe to ensure the vote is always associated with the correct contest
        if (!$this->vote->contest->is($this->contest)) {
            throw new Exception('Vote is not associated with the correct contest');
        }

        $this->rating = $this->vote->voteRatings()
            ->where('entry_id', $this->entry->id)
            ->where('rating_factor_id', $this->ratingFactor->id)
            ->first()
            ->rating ?? null;
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.vote-rating', [
            'contest'      => $this->contest,
            'entry'        => $this->entry,
            'ratingFactor' => $this->ratingFactor,
        ]);
    }

    /**
     * Update the rating.
     *
     * @return void
     */
    public function updateRating()
    {
        $this->validate();

        $sessionKey = "vote_" . $this->contest->getKey();
        $this->vote = Session::get($sessionKey);
        $voteRating = $this->vote->voteRatings()
            ->where('entry_id', $this->entry->id)
            ->where('rating_factor_id', $this->ratingFactor->id)
            ->first();

        if ($voteRating) {
            if (!isset($this->rating)) {
                return;
            } else {
                $voteRating->update([
                    'rating' => $this->rating,
                ]);
            }
        } else {
            $voteRating = $this->vote->voteRatings()->create([
                'entry_id'         => $this->entry->id,
                'rating_factor_id' => $this->ratingFactor->id,
                'rating'           => $this->rating,
            ]);
        }
    }
}
