<?php

use App\Enums\VotingType;
use App\Models\Contest;
use App\Models\Entry;
use App\Models\RatingFactor;
use App\Models\Vote;
use App\Models\VoteRating;

beforeEach(function () {
    // Refresh database to avoid conflicts
    $this->artisan('migrate:fresh', ['--force' => true]);

    $this->contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'A test contest for rating',
        'voting_type' => VotingType::RATING,
        'rating_max' => 10,
        'entry_description_display_type' => 'inline',
    ]);

    $this->entry1 = Entry::create([
        'contest_id' => $this->contest->id,
        'name' => 'Test Entry 1',
        'description' => 'First test entry',
    ]);
    $this->entry2 = Entry::create([
        'contest_id' => $this->contest->id,
        'name' => 'Test Entry 2',
        'description' => 'Second test entry',
    ]);

    $this->factor1 = RatingFactor::create([
        'contest_id' => $this->contest->id,
        'name' => 'Taste',
        'description' => 'How good does it taste?',
    ]);
    $this->factor2 = RatingFactor::create([
        'contest_id' => $this->contest->id,
        'name' => 'Presentation',
        'description' => 'How well is it presented?',
    ]);
});

describe('Contest Creation', function () {
    it('creates a contest with rating voting type', function () {
        expect($this->contest->voting_type)->toBe(VotingType::RATING);
        expect($this->contest->rating_max)->toBe(10);
    });

    it('has rating factors associated with the contest', function () {
        expect($this->contest->ratingFactors)->toHaveCount(2);
        expect($this->contest->ratingFactors->pluck('name'))->toContain('Taste', 'Presentation');
    });

    it('has entries associated with the contest', function () {
        expect($this->contest->entries)->toHaveCount(2);
    });
});

describe('Vote Creation and Rating', function () {
    it('can create a vote for a contest', function () {
        $vote = Vote::create(['contest_id' => $this->contest->id]);

        expect($vote->contest_id)->toBe($this->contest->id);
        expect($vote->contest->id)->toBe($this->contest->id);
    });

    it('can create vote ratings for entries and factors', function () {
        $vote = Vote::create(['contest_id' => $this->contest->id]);

        $voteRating = VoteRating::create([
            'vote_id' => $vote->id,
            'entry_id' => $this->entry1->id,
            'rating_factor_id' => $this->factor1->id,
            'rating' => 8,
        ]);

        expect($voteRating->vote_id)->toBe($vote->id);
        expect($voteRating->entry_id)->toBe($this->entry1->id);
        expect($voteRating->rating_factor_id)->toBe($this->factor1->id);
        expect($voteRating->rating)->toBe(8);
    });

    it('enforces maximum rating constraint', function () {
        $vote = Vote::create(['contest_id' => $this->contest->id]);

        expect(fn () => VoteRating::create([
            'vote_id' => $vote->id,
            'entry_id' => $this->entry1->id,
            'rating_factor_id' => $this->factor1->id,
            'rating' => 15,
        ]))->toThrow(InvalidArgumentException::class, 'Rating cannot exceed contest maximum of 10');
    });

    it('allows ratings within the maximum limit', function () {
        $vote = Vote::create(['contest_id' => $this->contest->id]);

        $voteRating = VoteRating::create([
            'vote_id' => $vote->id,
            'entry_id' => $this->entry1->id,
            'rating_factor_id' => $this->factor1->id,
            'rating' => 10,
        ]);

        expect($voteRating->rating)->toBe(10);
    });

    it('allows zero ratings', function () {
        $vote = Vote::create(['contest_id' => $this->contest->id]);

        $voteRating = VoteRating::create([
            'vote_id' => $vote->id,
            'entry_id' => $this->entry1->id,
            'rating_factor_id' => $this->factor1->id,
            'rating' => 0,
        ]);

        expect($voteRating->rating)->toBe(0);
    });
});

describe('Vote Rating Relationships', function () {
    it('has correct relationships between vote ratings and related models', function () {
        $vote = Vote::create(['contest_id' => $this->contest->id]);

        $voteRating = VoteRating::create([
            'vote_id' => $vote->id,
            'entry_id' => $this->entry1->id,
            'rating_factor_id' => $this->factor1->id,
            'rating' => 7,
        ]);

        expect($voteRating->vote->id)->toBe($vote->id);
        expect($voteRating->entry->id)->toBe($this->entry1->id);
        expect($voteRating->ratingFactor->id)->toBe($this->factor1->id);
        expect($vote->voteRatings->first()->id)->toBe($voteRating->id);
    });
});

describe('Complete Voting Scenario', function () {
    it('can create a complete voting scenario with multiple ratings', function () {
        $vote = Vote::create(['contest_id' => $this->contest->id]);

        // Rate entry 1
        VoteRating::create([
            'vote_id' => $vote->id,
            'entry_id' => $this->entry1->id,
            'rating_factor_id' => $this->factor1->id,
            'rating' => 8,
        ]);
        VoteRating::create([
            'vote_id' => $vote->id,
            'entry_id' => $this->entry1->id,
            'rating_factor_id' => $this->factor2->id,
            'rating' => 7,
        ]);

        // Rate entry 2
        VoteRating::create([
            'vote_id' => $vote->id,
            'entry_id' => $this->entry2->id,
            'rating_factor_id' => $this->factor1->id,
            'rating' => 6,
        ]);
        VoteRating::create([
            'vote_id' => $vote->id,
            'entry_id' => $this->entry2->id,
            'rating_factor_id' => $this->factor2->id,
            'rating' => 9,
        ]);

        $vote->refresh();
        expect($vote->voteRatings)->toHaveCount(4);

        // Verify all ratings are properly stored
        $entry1Taste = $vote->voteRatings->where('entry_id', $this->entry1->id)
            ->where('rating_factor_id', $this->factor1->id)->first();
        $entry1Presentation = $vote->voteRatings->where('entry_id', $this->entry1->id)
            ->where('rating_factor_id', $this->factor2->id)->first();
        $entry2Taste = $vote->voteRatings->where('entry_id', $this->entry2->id)
            ->where('rating_factor_id', $this->factor1->id)->first();
        $entry2Presentation = $vote->voteRatings->where('entry_id', $this->entry2->id)
            ->where('rating_factor_id', $this->factor2->id)->first();

        expect($entry1Taste->rating)->toBe(8);
        expect($entry1Presentation->rating)->toBe(7);
        expect($entry2Taste->rating)->toBe(6);
        expect($entry2Presentation->rating)->toBe(9);
    });
});

describe('Vote Summary Functionality', function () {
    it('calculates vote summary correctly', function () {
        $vote = Vote::create(['contest_id' => $this->contest->id]);

        VoteRating::create([
            'vote_id' => $vote->id,
            'entry_id' => $this->entry1->id,
            'rating_factor_id' => $this->factor1->id,
            'rating' => 8,
        ]);
        VoteRating::create([
            'vote_id' => $vote->id,
            'entry_id' => $this->entry1->id,
            'rating_factor_id' => $this->factor2->id,
            'rating' => 7,
        ]);

        $vote->refreshSummary();
        $totalRating = $vote->getEntryTotalRating($this->entry1->id);

        expect($totalRating)->toBe(15.0);
    });

    it('handles zero ratings in summary calculation', function () {
        $vote = Vote::create(['contest_id' => $this->contest->id]);

        VoteRating::create([
            'vote_id' => $vote->id,
            'entry_id' => $this->entry1->id,
            'rating_factor_id' => $this->factor1->id,
            'rating' => 0,
        ]);
        VoteRating::create([
            'vote_id' => $vote->id,
            'entry_id' => $this->entry1->id,
            'rating_factor_id' => $this->factor2->id,
            'rating' => 5,
        ]);

        $vote->refreshSummary();
        $totalRating = $vote->getEntryTotalRating($this->entry1->id);

        expect($totalRating)->toBe(5.0);
    });
});

describe('Contest Auto-Rating Creation', function () {
    it('automatically creates zero ratings then deletes vote if all remain zero', function () {
        $vote = Vote::create(['contest_id' => $this->contest->id]);
        $voteId = $vote->id;

        // Initially no vote ratings exist
        expect($vote->voteRatings)->toHaveCount(0);

        // Trigger the auto-creation manually (boot logic is disabled during tests)
        $this->contest->ensureAllVotesHaveRatings();

        // Vote should be deleted because all auto-created ratings are 0
        expect(Vote::find($voteId))->toBeNull();
    });

    it('deletes vote if all ratings remain zero after auto-creation', function () {
        $vote = Vote::create(['contest_id' => $this->contest->id]);
        $voteId = $vote->id;

        // Trigger auto-creation and deletion logic manually (boot logic is disabled during tests)
        $this->contest->ensureAllVotesHaveRatings();

        // Vote should be deleted because all ratings are 0
        expect(Vote::find($voteId))->toBeNull();
    });

    it('preserves vote if at least one rating is non-zero', function () {
        $vote = Vote::create(['contest_id' => $this->contest->id]);

        // Create one non-zero rating
        VoteRating::create([
            'vote_id' => $vote->id,
            'entry_id' => $this->entry1->id,
            'rating_factor_id' => $this->factor1->id,
            'rating' => 5,
        ]);

        // Trigger auto-creation and deletion logic manually (boot logic is disabled during tests)
        $this->contest->ensureAllVotesHaveRatings();
        $vote->refresh();

        // Vote should still exist and have all required ratings
        expect($vote->exists)->toBeTrue();
        expect($vote->voteRatings)->toHaveCount(4);

        // The one we created should still have rating 5
        $nonZeroRating = $vote->voteRatings->where('entry_id', $this->entry1->id)
            ->where('rating_factor_id', $this->factor1->id)->first();
        expect($nonZeroRating->rating)->toBe(5);
    });
});
