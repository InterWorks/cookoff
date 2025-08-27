<?php

namespace App\Livewire;

use App\Models\Contest;
use App\Models\Entry;
use App\Models\RatingFactor;
use App\Models\Vote;
use Exception;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class VoteRating extends Component
{
    public $componentID;

    public $contest;

    public $entry;

    public $ratingFactor;

    public $mode;

    public $rating = null;

    public $saveStatus = null; // 'success', 'error', or null

    private Vote $vote;

    /**
     * The validation rules.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'rating' => 'numeric|min:0|max:' . $this->contest->rating_max,
        ];
    }

    /**
     * Mount the component.
     *
     * @param  Contest      $contest      The contest the entry belongs to.
     * @param  Entry        $entry        The entry to vote on.
     * @param  RatingFactor $ratingFactor The rating factor to vote on.
     * @param  string       $mode         The mode of the contest.
     *
     * @return void
     * @throws Exception If the vote is not associated with the correct contest.
     */
    public function mount(Contest $contest, Entry $entry, RatingFactor $ratingFactor, string $mode): void
    {
        $this->mode         = $mode;
        $this->contest      = $contest;
        $this->entry        = $entry;
        $this->ratingFactor = $ratingFactor;
        $this->componentID  = $contest->id . '-' . $entry->id . '-' . $ratingFactor->id;
        $sessionKey         = 'vote_' . $this->contest->getKey();
        $this->vote         = Session::get($sessionKey, function () use ($sessionKey) {
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
     * Reset save status when rating is updated.
     *
     * @return void
     */
    public function updatedRating()
    {
        $this->saveStatus = null;
    }

    /**
     * Update the rating.
     *
     * @return void
     * @throws \Illuminate\Validation\ValidationException When rating validation fails.
     */
    public function updateRating()
    {
        try {
            $this->validate();

            $sessionKey = 'vote_' . $this->contest->getKey();
            $this->vote = Session::get($sessionKey);
            $voteRating = $this->vote->voteRatings()
                ->where('entry_id', $this->entry->id)
                ->where('rating_factor_id', $this->ratingFactor->id)
                ->first();

            if ($voteRating) {
                if (! isset($this->rating)) {
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

            $this->saveStatus = 'success';
        } catch (ValidationException $e) {
            $this->saveStatus = 'error';
            throw $e; // Re-throw to preserve validation error display
        } catch (Exception $e) {
            $this->saveStatus = 'error';
        }
    }
}
