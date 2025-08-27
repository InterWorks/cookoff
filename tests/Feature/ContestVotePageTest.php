<?php

use App\Enums\VotingType;
use App\Models\Contest;
use App\Models\Entry;
use App\Models\RatingFactor;
use App\Models\User;
use App\Models\Vote;
use Livewire\Livewire;

beforeEach(function () {
    // Refresh database to avoid conflicts
    $this->artisan('migrate:fresh', ['--force' => true]);

    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Create rating system contest
    $this->ratingContest = Contest::factory()->create([
        'name' => 'Rating Contest',
        'description' => 'Test rating contest',
        'voting_type' => VotingType::RATING,
        'rating_max' => 10,
        'entry_description_display_type' => 'inline',
    ]);

    // Create single winner contest
    $this->singleWinnerContest = Contest::factory()->create([
        'name' => 'Single Winner Contest',
        'description' => 'Test single winner contest',
        'voting_type' => VotingType::SINGLE_WINNER,
    ]);

    // Create entries for both contests
    $this->entry1 = Entry::factory()->create([
        'contest_id' => $this->ratingContest->id,
        'name' => 'Test Entry 1',
        'description' => 'First test entry',
    ]);
    $this->entry2 = Entry::factory()->create([
        'contest_id' => $this->ratingContest->id,
        'name' => 'Test Entry 2',
        'description' => 'Second test entry',
    ]);

    $this->singleEntry1 = Entry::factory()->create([
        'contest_id' => $this->singleWinnerContest->id,
        'name' => 'Winner Option 1',
        'description' => 'First winner option',
    ]);
    $this->singleEntry2 = Entry::factory()->create([
        'contest_id' => $this->singleWinnerContest->id,
        'name' => 'Winner Option 2',
        'description' => 'Second winner option',
    ]);

    // Create rating factors for rating contest
    $this->factor1 = RatingFactor::factory()->create([
        'contest_id' => $this->ratingContest->id,
        'name' => 'Taste',
        'description' => 'How good does it taste?',
    ]);
    $this->factor2 = RatingFactor::factory()->create([
        'contest_id' => $this->ratingContest->id,
        'name' => 'Presentation',
        'description' => 'How well is it presented?',
    ]);
});

// describe('Contest Vote Page Access', function () {
//     it('can access rating contest vote page when voting is open', function () {
//         // Make sure voting is open
//         $this->ratingContest->update([
//             'voting_window_opens_at' => now()->subHour(),
//             'voting_window_closes_at' => now()->addHour(),
//         ]);

//         $response = $this->get(route('contests.vote', $this->ratingContest));

//         $response->assertSuccessful();
//         $response->assertSee('Vote in ' . $this->ratingContest->name);
//         $response->assertSee($this->ratingContest->description);
//     });

//     it('can access single winner contest vote page when voting is open', function () {
//         // Make sure voting is open
//         $this->singleWinnerContest->update([
//             'voting_window_opens_at' => now()->subHour(),
//             'voting_window_closes_at' => now()->addHour(),
//         ]);

//         $response = $this->get(route('contests.vote', $this->singleWinnerContest));

//         $response->assertSuccessful();
//         $response->assertSee('Vote in ' . $this->singleWinnerContest->name);
//         $response->assertSee($this->singleWinnerContest->description);
//     });

//     it('shows voting closed message when voting is closed', function () {
//         // Make sure voting is closed
//         $this->ratingContest->update([
//             'voting_window_opens_at' => now()->subDay(),
//             'voting_window_closes_at' => now()->subHour(),
//         ]);

//         $response = $this->get(route('contests.vote', $this->ratingContest));

//         $response->assertSuccessful();
//         $response->assertSee('Voting is closed for this contest.');
//     });

//     it('allows unauthenticated users to access vote page', function () {
//         auth()->logout();

//         $response = $this->get(route('contests.vote', $this->ratingContest));

//         $response->assertSuccessful();
//     });
// });

describe('Rating System Vote Page Content', function () {
    beforeEach(function () {
        // Make sure voting is open
        $this->ratingContest->update([
            'voting_window_opens_at' => now()->subHour(),
            'voting_window_closes_at' => now()->addHour(),
        ]);
    });

    it('displays rating system table with correct Flux components', function () {
        $response = $this->get(route('contests.vote', $this->ratingContest));

        // Check for rendered Flux table components (they use data- attributes when compiled)
        $response->assertSee('data-flux-table', false);
        $response->assertSee('data-flux-columns', false);
        $response->assertSee('data-flux-column', false);
        $response->assertSee('data-flux-rows', false);
        $response->assertSee('data-flux-row', false);
        $response->assertSee('data-flux-cell', false);
    });

    it('displays all entries and rating factors in table headers', function () {
        $response = $this->get(route('contests.vote', $this->ratingContest));

        // Check entries are displayed
        $response->assertSee($this->entry1->name);
        $response->assertSee($this->entry2->name);

        // Check rating factors are displayed
        $response->assertSee($this->factor1->name);
        $response->assertSee($this->factor2->name);

        // Check for "Factors" column header
        $response->assertSee('Factors');
    });

    it('displays entry descriptions when configured for inline display', function () {
        $this->ratingContest->update(['entry_description_display_type' => 'inline']);

        $response = $this->get(route('contests.vote', $this->ratingContest));

        $response->assertSee($this->entry1->description);
        $response->assertSee($this->entry2->description);
        $response->assertSee($this->factor1->description);
        $response->assertSee($this->factor2->description);
    });

    it('includes vote rating components for each entry-factor combination', function () {
        $response = $this->get(route('contests.vote', $this->ratingContest));

        $content = $response->getContent();

        // Debug: Let's see what's actually in the content
        // Check for vote-rating component name in various formats
        $hasVoteRatingComponent = str_contains($content, 'vote-rating') ||
                                 str_contains($content, '"name":"vote-rating"') ||
                                 str_contains($content, "'name':'vote-rating'") ||
                                 str_contains($content, '&quot;name&quot;:&quot;vote-rating&quot;');

        expect($hasVoteRatingComponent)->toBeTrue('Should contain vote-rating component in some format');

        // Should have rating input fields for each entry-factor combination
        // We have 2 entries and 2 rating factors = 4 combinations
        $response->assertSee('placeholder="Enter your rating"', false);

        // Verify we have the correct number of rating inputs (2 entries × 2 factors = 4 inputs in desktop + 4 in mobile = 8 total)
        $ratingInputCount = substr_count($content, 'placeholder="Enter your rating"');
        expect($ratingInputCount)->toBe(8); // 4 desktop + 4 mobile
    });

    it('displays mobile view for smaller screens', function () {
        $response = $this->get(route('contests.vote', $this->ratingContest));

        // Check for mobile view container (compiled CSS classes)
        $response->assertSee('mt-8 lg:hidden', false);

        // Check for entry names in mobile view
        $response->assertSee($this->entry1->name);
        $response->assertSee($this->entry2->name);

        // Check for rating factor labels in mobile view
        $response->assertSee($this->factor1->name);
        $response->assertSee($this->factor2->name);
    });
});

describe('Single Winner Vote Page Content', function () {
    beforeEach(function () {
        // Make sure voting is open
        $this->singleWinnerContest->update([
            'voting_window_opens_at' => now()->subHour(),
            'voting_window_closes_at' => now()->addHour(),
        ]);
    });

    it('displays single winner voting component', function () {
        $response = $this->get(route('contests.vote', $this->singleWinnerContest));

        // Check for single winner component content
        $response->assertSee('Select Your Winner');
        $response->assertSee('Choose one entry as the winner');

        // Verify Livewire component is present by checking for wire attributes
        $content = $response->getContent();
        expect($content)->toContain('single-winner-vote');
    });

    it('shows all entry options for single winner voting', function () {
        $response = $this->get(route('contests.vote', $this->singleWinnerContest));

        $response->assertSee($this->singleEntry1->name);
        $response->assertSee($this->singleEntry2->name);
    });
});

describe('Flux Components Integration', function () {
    beforeEach(function () {
        $this->ratingContest->update([
            'voting_window_opens_at' => now()->subHour(),
            'voting_window_closes_at' => now()->addHour(),
        ]);
    });

    it('includes required Flux directives in layout', function () {
        $response = $this->get(route('contests.vote', $this->ratingContest));

        // Check for Flux compiled output instead of directives
        $response->assertSee('window.Flux', false);
        $response->assertSee('data-flux-', false);

        // Verify the page loaded with Flux styles and scripts
        $content = $response->getContent();
        expect($content)->toContain('flux');
    });

    it('includes Flux breadcrumbs navigation', function () {
        $response = $this->get(route('contests.vote', $this->ratingContest));

        // Check for breadcrumb content
        $response->assertSee('Home');
        $response->assertSee('Contests');
        $response->assertSee($this->ratingContest->name);
        $response->assertSee('Vote');

        // Check for flux breadcrumb attributes in compiled HTML
        $response->assertSee('data-flux-breadcrumbs', false);
    });

    it('includes see results button with correct Flux styling', function () {
        $response = $this->get(route('contests.vote', $this->ratingContest));

        // Check for See Results button text and link
        $response->assertSee('See Results');

        // Check for Flux button attributes in compiled HTML
        $response->assertSee('data-flux-button', false);

        // Verify the link points to the correct contest
        $content = $response->getContent();
        expect($content)->toContain(route('contests.show', ['contest' => $this->ratingContest->id]));
    });
});

describe('Single Winner Vote Component Functionality', function () {
    beforeEach(function () {
        $this->singleWinnerContest->update([
            'voting_window_opens_at' => now()->subHour(),
            'voting_window_closes_at' => now()->addHour(),
        ]);
    });

    it('can render single winner vote component', function () {
        Livewire::test('single-winner-vote', ['contest' => $this->singleWinnerContest])
            ->assertSee('Select Your Winner')
            ->assertSee($this->singleEntry1->name)
            ->assertSee($this->singleEntry2->name);
    });

    it('can select a winner in single winner vote', function () {
        Livewire::test('single-winner-vote', ['contest' => $this->singleWinnerContest])
            ->call('selectWinner', $this->singleEntry1->id)
            ->assertSee('Your vote has been recorded for: '.$this->singleEntry1->name);
    });

    it('displays success banner with correct Flux component when vote is recorded', function () {
        $component = Livewire::test('single-winner-vote', ['contest' => $this->singleWinnerContest])
            ->call('selectWinner', $this->singleEntry1->id);

        // Check that the component shows the success message
        $component->assertSee('Your vote has been recorded for: '.$this->singleEntry1->name);

        // Check for success banner elements
        $component->assertSee('Vote Recorded');
        $content = $component->html();
        expect($content)->toContain('data-flux-callout');
    });

    it('uses Flux radio group for entry selection', function () {
        $component = Livewire::test('single-winner-vote', ['contest' => $this->singleWinnerContest]);

        // Check for radio input elements and entry names
        $component->assertSee($this->singleEntry1->name);
        $component->assertSee($this->singleEntry2->name);

        // Check for Flux radio components
        $content = $component->html();
        expect($content)->toContain('data-flux-radio');
        expect($content)->toContain('wire:model.live="selectedEntryId"');
    });
});

describe('Page Performance and Error Handling', function () {
    it('handles non-existent contest gracefully', function () {
        $response = $this->get(route('contests.vote', ['contest' => 99999]));

        $response->assertNotFound();
    });

    it('loads page within reasonable time', function () {
        $this->ratingContest->update([
            'voting_window_opens_at' => now()->subHour(),
            'voting_window_closes_at' => now()->addHour(),
        ]);

        $start = microtime(true);
        $response = $this->get(route('contests.vote', $this->ratingContest));
        $end = microtime(true);

        $response->assertSuccessful();
        expect($end - $start)->toBeLessThan(2.0); // Should load within 2 seconds
    });

    it('handles contests with no entries gracefully', function () {
        $emptyContest = Contest::factory()->create([
            'voting_type' => VotingType::RATING,
            'voting_window_opens_at' => now()->subHour(),
            'voting_window_closes_at' => now()->addHour(),
        ]);

        $response = $this->get(route('contests.vote', $emptyContest));

        $response->assertSuccessful();
        // Should still show the basic page structure
        $response->assertSee('Vote in '.$emptyContest->name);
    });

    it('handles contests with no rating factors gracefully', function () {
        $noFactorsContest = Contest::factory()->create([
            'voting_type' => VotingType::RATING,
            'voting_window_opens_at' => now()->subHour(),
            'voting_window_closes_at' => now()->addHour(),
        ]);

        Entry::factory()->create(['contest_id' => $noFactorsContest->id]);

        $response = $this->get(route('contests.vote', $noFactorsContest));

        $response->assertSuccessful();
        $response->assertSee('Vote in '.$noFactorsContest->name);
    });
});
