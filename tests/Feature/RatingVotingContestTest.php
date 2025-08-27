<?php

use App\Enums\VotingType;
use App\Models\Contest;
use App\Models\Entry;
use App\Models\RatingFactor;
use App\Models\Vote;
use App\Models\VoteRating;
use Carbon\Carbon;

beforeEach(function () {
    // Refresh database to avoid conflicts
    $this->artisan('migrate:fresh', ['--force' => true]);

    $this->contest = Contest::create([
        'name' => 'Rating Contest',
        'description' => 'A contest using rating system',
        'voting_type' => VotingType::RATING,
        'rating_max' => 10,
        'entry_description_display_type' => 'inline',
        'voting_window_opens_at' => Carbon::now()->subHour(),
        'voting_window_closes_at' => Carbon::now()->addHour(),
    ]);

    $this->entry1 = Entry::create([
        'contest_id' => $this->contest->id,
        'name' => 'Chocolate Cake',
        'description' => 'Rich chocolate layer cake',
    ]);

    $this->entry2 = Entry::create([
        'contest_id' => $this->contest->id,
        'name' => 'Apple Pie',
        'description' => 'Classic apple pie with cinnamon',
    ]);

    $this->entry3 = Entry::create([
        'contest_id' => $this->contest->id,
        'name' => 'Cheesecake',
        'description' => 'Creamy New York style cheesecake',
    ]);

    $this->factor1 = RatingFactor::create([
        'contest_id' => $this->contest->id,
        'name' => 'Taste',
        'description' => 'Overall flavor and deliciousness',
    ]);

    $this->factor2 = RatingFactor::create([
        'contest_id' => $this->contest->id,
        'name' => 'Presentation',
        'description' => 'Visual appeal and plating',
    ]);

    $this->factor3 = RatingFactor::create([
        'contest_id' => $this->contest->id,
        'name' => 'Creativity',
        'description' => 'Innovation and unique elements',
    ]);
});

describe('Rating Contest Setup', function () {
    it('creates a rating contest with proper configuration', function () {
        expect($this->contest->voting_type)->toBe(VotingType::RATING);
        expect($this->contest->rating_max)->toBe(10);
        expect($this->contest->isVotingOpen())->toBeTrue();
    });

    it('has multiple rating factors', function () {
        expect($this->contest->ratingFactors)->toHaveCount(3);
        $factorNames = $this->contest->ratingFactors->pluck('name')->toArray();
        expect($factorNames)->toContain('Taste', 'Presentation', 'Creativity');
    });

    it('has multiple entries to rate', function () {
        expect($this->contest->entries)->toHaveCount(3);
        $entryNames = $this->contest->entries->pluck('name')->toArray();
        expect($entryNames)->toContain('Chocolate Cake', 'Apple Pie', 'Cheesecake');
    });
});

describe('Rating Validation', function () {
    it('accepts ratings within the maximum', function () {
        $vote = Vote::create(['contest_id' => $this->contest->id]);

        $voteRating = VoteRating::create([
            'vote_id' => $vote->id,
            'entry_id' => $this->entry1->id,
            'rating_factor_id' => $this->factor1->id,
            'rating' => 8,
        ]);

        expect($voteRating->rating)->toBe(8);
    });

    it('accepts maximum rating value', function () {
        $vote = Vote::create(['contest_id' => $this->contest->id]);

        $voteRating = VoteRating::create([
            'vote_id' => $vote->id,
            'entry_id' => $this->entry1->id,
            'rating_factor_id' => $this->factor1->id,
            'rating' => 10,
        ]);

        expect($voteRating->rating)->toBe(10);
    });

    it('accepts minimum rating value', function () {
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

describe('Complete Rating Workflow', function () {
    it('allows complete rating of all entries across all factors', function () {
        $vote = Vote::create(['contest_id' => $this->contest->id]);

        // Rate all entries for all factors
        $ratings = [
            [$this->entry1->id, $this->factor1->id, 9], // Chocolate Cake - Taste
            [$this->entry1->id, $this->factor2->id, 8], // Chocolate Cake - Presentation
            [$this->entry1->id, $this->factor3->id, 7], // Chocolate Cake - Creativity
            [$this->entry2->id, $this->factor1->id, 8], // Apple Pie - Taste
            [$this->entry2->id, $this->factor2->id, 9], // Apple Pie - Presentation
            [$this->entry2->id, $this->factor3->id, 6], // Apple Pie - Creativity
            [$this->entry3->id, $this->factor1->id, 10], // Cheesecake - Taste
            [$this->entry3->id, $this->factor2->id, 9], // Cheesecake - Presentation
            [$this->entry3->id, $this->factor3->id, 8], // Cheesecake - Creativity
        ];

        foreach ($ratings as [$entryId, $factorId, $rating]) {
            VoteRating::create([
                'vote_id' => $vote->id,
                'entry_id' => $entryId,
                'rating_factor_id' => $factorId,
                'rating' => $rating,
            ]);
        }

        $vote->refresh();
        expect($vote->voteRatings)->toHaveCount(9);
    });

    it('calculates correct total scores for each entry', function () {
        $vote = Vote::create(['contest_id' => $this->contest->id]);

        // Rate Chocolate Cake: 9 + 8 + 7 = 24
        VoteRating::create(['vote_id' => $vote->id, 'entry_id' => $this->entry1->id, 'rating_factor_id' => $this->factor1->id, 'rating' => 9]);
        VoteRating::create(['vote_id' => $vote->id, 'entry_id' => $this->entry1->id, 'rating_factor_id' => $this->factor2->id, 'rating' => 8]);
        VoteRating::create(['vote_id' => $vote->id, 'entry_id' => $this->entry1->id, 'rating_factor_id' => $this->factor3->id, 'rating' => 7]);

        // Rate Apple Pie: 8 + 9 + 6 = 23
        VoteRating::create(['vote_id' => $vote->id, 'entry_id' => $this->entry2->id, 'rating_factor_id' => $this->factor1->id, 'rating' => 8]);
        VoteRating::create(['vote_id' => $vote->id, 'entry_id' => $this->entry2->id, 'rating_factor_id' => $this->factor2->id, 'rating' => 9]);
        VoteRating::create(['vote_id' => $vote->id, 'entry_id' => $this->entry2->id, 'rating_factor_id' => $this->factor3->id, 'rating' => 6]);

        // Rate Cheesecake: 10 + 9 + 8 = 27
        VoteRating::create(['vote_id' => $vote->id, 'entry_id' => $this->entry3->id, 'rating_factor_id' => $this->factor1->id, 'rating' => 10]);
        VoteRating::create(['vote_id' => $vote->id, 'entry_id' => $this->entry3->id, 'rating_factor_id' => $this->factor2->id, 'rating' => 9]);
        VoteRating::create(['vote_id' => $vote->id, 'entry_id' => $this->entry3->id, 'rating_factor_id' => $this->factor3->id, 'rating' => 8]);

        $vote->refreshSummary();

        expect($vote->getEntryTotalRating($this->entry1->id))->toBe(24.0); // Chocolate Cake
        expect($vote->getEntryTotalRating($this->entry2->id))->toBe(23.0); // Apple Pie
        expect($vote->getEntryTotalRating($this->entry3->id))->toBe(27.0); // Cheesecake
    });
});

describe('Multiple Votes and Averages', function () {
    it('handles multiple votes for the same contest', function () {
        // First voter
        $vote1 = Vote::create(['contest_id' => $this->contest->id]);
        VoteRating::create(['vote_id' => $vote1->id, 'entry_id' => $this->entry1->id, 'rating_factor_id' => $this->factor1->id, 'rating' => 8]);
        VoteRating::create(['vote_id' => $vote1->id, 'entry_id' => $this->entry1->id, 'rating_factor_id' => $this->factor2->id, 'rating' => 7]);

        // Second voter
        $vote2 = Vote::create(['contest_id' => $this->contest->id]);
        VoteRating::create(['vote_id' => $vote2->id, 'entry_id' => $this->entry1->id, 'rating_factor_id' => $this->factor1->id, 'rating' => 9]);
        VoteRating::create(['vote_id' => $vote2->id, 'entry_id' => $this->entry1->id, 'rating_factor_id' => $this->factor2->id, 'rating' => 8]);

        // Third voter
        $vote3 = Vote::create(['contest_id' => $this->contest->id]);
        VoteRating::create(['vote_id' => $vote3->id, 'entry_id' => $this->entry1->id, 'rating_factor_id' => $this->factor1->id, 'rating' => 7]);
        VoteRating::create(['vote_id' => $vote3->id, 'entry_id' => $this->entry1->id, 'rating_factor_id' => $this->factor2->id, 'rating' => 9]);

        expect($this->contest->votes)->toHaveCount(3);

        // Verify each vote has correct individual totals
        $vote1->refreshSummary();
        $vote2->refreshSummary();
        $vote3->refreshSummary();

        expect($vote1->getEntryTotalRating($this->entry1->id))->toBe(15.0); // 8 + 7
        expect($vote2->getEntryTotalRating($this->entry1->id))->toBe(17.0); // 9 + 8
        expect($vote3->getEntryTotalRating($this->entry1->id))->toBe(16.0); // 7 + 9
    });
});

describe('Partial Ratings', function () {
    it('handles partial ratings gracefully', function () {
        $vote = Vote::create(['contest_id' => $this->contest->id]);

        // Only rate some factors for some entries
        VoteRating::create(['vote_id' => $vote->id, 'entry_id' => $this->entry1->id, 'rating_factor_id' => $this->factor1->id, 'rating' => 8]);
        VoteRating::create(['vote_id' => $vote->id, 'entry_id' => $this->entry1->id, 'rating_factor_id' => $this->factor2->id, 'rating' => 0]);
        // Skip factor3 for entry1

        VoteRating::create(['vote_id' => $vote->id, 'entry_id' => $this->entry2->id, 'rating_factor_id' => $this->factor1->id, 'rating' => 7]);
        // Skip factor2 and factor3 for entry2

        $vote->refreshSummary();

        expect($vote->getEntryTotalRating($this->entry1->id))->toBe(8.0); // 8 + 0 (zero counts)
        expect($vote->getEntryTotalRating($this->entry2->id))->toBe(7.0); // just 7
    });
});

describe('Contest Voting Window', function () {
    it('respects voting window timing', function () {
        // Contest with past voting window
        $pastContest = Contest::create([
            'name' => 'Past Contest',
            'description' => 'Contest with past voting window',
            'voting_type' => VotingType::RATING,
            'rating_max' => 10,
            'voting_window_opens_at' => Carbon::now()->subDays(2),
            'voting_window_closes_at' => Carbon::now()->subDay(),
        ]);

        // Contest with future voting window
        $futureContest = Contest::create([
            'name' => 'Future Contest',
            'description' => 'Contest with future voting window',
            'voting_type' => VotingType::RATING,
            'rating_max' => 10,
            'voting_window_opens_at' => Carbon::now()->addDay(),
            'voting_window_closes_at' => Carbon::now()->addDays(2),
        ]);

        expect($this->contest->isVotingOpen())->toBeTrue();
        expect($pastContest->isVotingOpen())->toBeFalse();
        expect($futureContest->isVotingOpen())->toBeFalse();
    });
});

describe('Contest Results and Winners', function () {
    it('determines winning entries based on ratings', function () {
        // Create multiple votes to establish clear winners
        for ($i = 1; $i <= 3; $i++) {
            $vote = Vote::create(['contest_id' => $this->contest->id]);

            // Consistently rate Cheesecake highest
            VoteRating::create(['vote_id' => $vote->id, 'entry_id' => $this->entry3->id, 'rating_factor_id' => $this->factor1->id, 'rating' => 10]);
            VoteRating::create(['vote_id' => $vote->id, 'entry_id' => $this->entry3->id, 'rating_factor_id' => $this->factor2->id, 'rating' => 9]);

            // Rate Chocolate Cake second
            VoteRating::create(['vote_id' => $vote->id, 'entry_id' => $this->entry1->id, 'rating_factor_id' => $this->factor1->id, 'rating' => 8]);
            VoteRating::create(['vote_id' => $vote->id, 'entry_id' => $this->entry1->id, 'rating_factor_id' => $this->factor2->id, 'rating' => 8]);

            // Rate Apple Pie third
            VoteRating::create(['vote_id' => $vote->id, 'entry_id' => $this->entry2->id, 'rating_factor_id' => $this->factor1->id, 'rating' => 7]);
            VoteRating::create(['vote_id' => $vote->id, 'entry_id' => $this->entry2->id, 'rating_factor_id' => $this->factor2->id, 'rating' => 6]);
        }

        $winners = $this->contest->getWinningEntries(3);

        expect($winners)->toHaveCount(3);
        expect($winners['1st']['entry']->name)->toBe('Cheesecake');
        expect($winners['2nd']['entry']->name)->toBe('Chocolate Cake');
        expect($winners['3rd']['entry']->name)->toBe('Apple Pie');
    });
});
