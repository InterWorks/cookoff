<?php

use App\Enums\VotingType;
use App\Livewire\SingleWinnerVote;
use App\Models\Contest;
use App\Models\Entry;
use App\Models\Vote;
use Carbon\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    // Refresh database to avoid conflicts
    $this->artisan('migrate:fresh', ['--force' => true]);

    $this->contest = Contest::create([
        'name' => 'Single Winner Contest',
        'description' => 'A contest where voters select one winner',
        'voting_type' => VotingType::SINGLE_WINNER,
        'rating_max' => 1,
        'entry_description_display_type' => 'inline',
        'voting_window_opens_at' => Carbon::now()->subHour(),
        'voting_window_closes_at' => Carbon::now()->addHour(),
    ]);

    $this->entry1 = Entry::create([
        'contest_id' => $this->contest->id,
        'name' => 'Italian Pasta',
        'description' => 'Homemade pasta with marinara sauce',
    ]);

    $this->entry2 = Entry::create([
        'contest_id' => $this->contest->id,
        'name' => 'Mexican Tacos',
        'description' => 'Authentic street tacos',
    ]);

    $this->entry3 = Entry::create([
        'contest_id' => $this->contest->id,
        'name' => 'Indian Curry',
        'description' => 'Spicy chicken curry with rice',
    ]);
});

describe('Single Winner Contest Setup', function () {
    it('creates a single winner contest with proper configuration', function () {
        expect($this->contest->voting_type)->toBe(VotingType::SINGLE_WINNER);
        expect($this->contest->rating_max)->toBe(1);
        expect($this->contest->isVotingOpen())->toBeTrue();
    });

    it('has multiple entries to choose from', function () {
        expect($this->contest->entries)->toHaveCount(3);
        $entryNames = $this->contest->entries->pluck('name')->toArray();
        expect($entryNames)->toContain('Italian Pasta', 'Mexican Tacos', 'Indian Curry');
    });
});

describe('SingleWinnerVote Livewire Component', function () {
    it('mounts with contest and creates vote session', function () {
        $component = Livewire::test(SingleWinnerVote::class, ['contest' => $this->contest]);

        expect($component->get('contest')->id)->toBe($this->contest->id);
        expect($component->get('selectedEntryId'))->toBeNull();
    });

    it('creates a default rating factor if none exists', function () {
        expect($this->contest->ratingFactors)->toHaveCount(0);

        Livewire::test(SingleWinnerVote::class, ['contest' => $this->contest])
            ->call('selectWinner', $this->entry1->id);

        $this->contest->refresh();
        expect($this->contest->ratingFactors)->toHaveCount(1);

        $ratingFactor = $this->contest->ratingFactors->first();
        expect($ratingFactor->name)->toBe('Winner');
        expect($ratingFactor->description)->toBe('Select the winning entry');
    });

    it('selects a winner correctly', function () {
        $component = Livewire::test(SingleWinnerVote::class, ['contest' => $this->contest])
            ->call('selectWinner', $this->entry2->id);

        expect($component->get('selectedEntryId'))->toBe($this->entry2->id);
    });

    it('updates selected entry when changed', function () {
        $component = Livewire::test(SingleWinnerVote::class, ['contest' => $this->contest])
            ->call('selectWinner', $this->entry1->id);

        expect($component->get('selectedEntryId'))->toBe($this->entry1->id);

        // Change selection
        $component->call('selectWinner', $this->entry3->id);
        expect($component->get('selectedEntryId'))->toBe($this->entry3->id);
    });

    it('can clear selection by passing null', function () {
        $component = Livewire::test(SingleWinnerVote::class, ['contest' => $this->contest])
            ->call('selectWinner', $this->entry1->id)
            ->call('selectWinner', null);

        expect($component->get('selectedEntryId'))->toBeNull();
    });
});

describe('Single Winner Vote Logic', function () {
    it('creates vote ratings with binary values (0 or 1)', function () {
        Livewire::test(SingleWinnerVote::class, ['contest' => $this->contest])
            ->call('selectWinner', $this->entry2->id);

        $this->contest->refresh();
        $votes = $this->contest->votes;
        expect($votes)->toHaveCount(1);

        $vote = $votes->first();
        $voteRatings = $vote->voteRatings;

        // Should have one rating per entry
        expect($voteRatings)->toHaveCount(3);

        // Only the selected entry should have rating 1
        $selectedRating = $voteRatings->where('entry_id', $this->entry2->id)->first();
        expect($selectedRating->rating)->toBe(1);

        // Other entries should have rating 0
        $otherRatings = $voteRatings->where('entry_id', '!=', $this->entry2->id);
        foreach ($otherRatings as $rating) {
            expect($rating->rating)->toBe(0);
        }
    });

    it('switches winner selection properly', function () {
        $component = Livewire::test(SingleWinnerVote::class, ['contest' => $this->contest])
            ->call('selectWinner', $this->entry1->id);

        // Verify first selection
        $this->contest->refresh();
        $vote = $this->contest->votes->first();
        $firstWinnerRating = $vote->voteRatings->where('entry_id', $this->entry1->id)->first();
        expect($firstWinnerRating->rating)->toBe(1);

        // Switch to different entry
        $component->call('selectWinner', $this->entry3->id);

        // Verify switch
        $vote->refresh();
        $previousWinnerRating = $vote->voteRatings->where('entry_id', $this->entry1->id)->first();
        $newWinnerRating = $vote->voteRatings->where('entry_id', $this->entry3->id)->first();

        expect($previousWinnerRating->rating)->toBe(0);
        expect($newWinnerRating->rating)->toBe(1);
    });

    it('maintains vote totals correctly', function () {
        Livewire::test(SingleWinnerVote::class, ['contest' => $this->contest])
            ->call('selectWinner', $this->entry2->id);

        $this->contest->refresh();
        $vote = $this->contest->votes->first();
        $vote->refreshSummary();

        expect($vote->getEntryTotalRating($this->entry1->id))->toBe(0.0);
        expect($vote->getEntryTotalRating($this->entry2->id))->toBe(1.0);
        expect($vote->getEntryTotalRating($this->entry3->id))->toBe(0.0);
    });
});

describe('Multiple Single Winner Votes', function () {
    it('handles multiple voters selecting different winners', function () {
        // First voter selects entry 1
        Livewire::test(SingleWinnerVote::class, ['contest' => $this->contest])
            ->call('selectWinner', $this->entry1->id);

        // Simulate second voter (new session)
        session()->flush();
        Livewire::test(SingleWinnerVote::class, ['contest' => $this->contest])
            ->call('selectWinner', $this->entry2->id);

        // Simulate third voter (new session)
        session()->flush();
        Livewire::test(SingleWinnerVote::class, ['contest' => $this->contest])
            ->call('selectWinner', $this->entry1->id);

        $this->contest->refresh();
        expect($this->contest->votes)->toHaveCount(3);

        // Count votes per entry
        $votes = $this->contest->votes;
        $entry1Votes = 0;
        $entry2Votes = 0;
        $entry3Votes = 0;

        foreach ($votes as $vote) {
            $winnerRating = $vote->voteRatings->where('rating', 1)->first();
            if ($winnerRating->entry_id == $this->entry1->id) {
                $entry1Votes++;
            }
            if ($winnerRating->entry_id == $this->entry2->id) {
                $entry2Votes++;
            }
            if ($winnerRating->entry_id == $this->entry3->id) {
                $entry3Votes++;
            }
        }

        expect($entry1Votes)->toBe(2);
        expect($entry2Votes)->toBe(1);
        expect($entry3Votes)->toBe(0);
    });

    it('calculates contest winners based on single winner votes', function () {
        // Create 5 votes with clear winner
        for ($i = 0; $i < 5; $i++) {
            session()->flush();
            $winner = match ($i) {
                0, 1, 2 => $this->entry1->id, // Entry 1 gets 3 votes
                3 => $this->entry2->id,       // Entry 2 gets 1 vote
                4 => $this->entry3->id,       // Entry 3 gets 1 vote
            };

            Livewire::test(SingleWinnerVote::class, ['contest' => $this->contest])
                ->call('selectWinner', $winner);
        }

        $winners = $this->contest->getWinningEntries(3);

        expect($winners)->toHaveCount(3);
        expect($winners['1st']['entry']->name)->toBe('Italian Pasta');
        expect($winners['1st']['rating'])->toBe(3.0); // 3 votes
    });
});

describe('Single Winner Component State Management', function () {
    it('preserves selection across component re-mounts', function () {
        // First component instance - select winner
        Livewire::test(SingleWinnerVote::class, ['contest' => $this->contest])
            ->call('selectWinner', $this->entry2->id);

        // Second component instance - should remember selection
        $component = Livewire::test(SingleWinnerVote::class, ['contest' => $this->contest]);
        expect($component->get('selectedEntryId'))->toBe($this->entry2->id);
    });

    it('repopulates the selected radio button on initial load when selection exists', function () {
        // Make a selection first
        Livewire::test(SingleWinnerVote::class, ['contest' => $this->contest])
            ->call('selectWinner', $this->entry3->id);

        // Create a new component instance (simulating page reload/revisit)
        $component = Livewire::test(SingleWinnerVote::class, ['contest' => $this->contest]);

        // Check that the component has the correct selectedEntryId
        expect($component->get('selectedEntryId'))->toBe($this->entry3->id);

        // Check that the HTML shows the correct radio button as checked
        $html = $component->html();

        // Look for the specific radio button for entry3 and verify it has checked attributes
        // In Flux UI, checked radios should have data-checked attribute
        expect($html)->toContain('value="'.$this->entry3->id.'"');

        // Check for checked state - either data-checked attribute or checked="checked"
        // The radio for entry3 should be marked as checked in some way
        $hasDataChecked = str_contains($html, 'value="'.$this->entry3->id.'"') && str_contains($html, 'data-checked');
        $hasCheckedAttribute = str_contains($html, 'value="'.$this->entry3->id.'"') && str_contains($html, 'checked="checked"');

        expect($hasDataChecked || $hasCheckedAttribute)->toBeTrue();

        // Verify the success message shows the correct entry
        $component->assertSee('Your vote has been recorded for: '.$this->entry3->name);
    });

    it('allows changing votes after initial selection', function () {
        $component = Livewire::test(SingleWinnerVote::class, ['contest' => $this->contest]);

        // Make initial selection
        $component->call('selectWinner', $this->entry1->id);
        expect($component->get('selectedEntryId'))->toBe($this->entry1->id);
        $component->assertSee('Your vote has been recorded for: '.$this->entry1->name);

        // Change selection to entry2
        $component->call('selectWinner', $this->entry2->id);
        expect($component->get('selectedEntryId'))->toBe($this->entry2->id);
        $component->assertSee('Your vote has been recorded for: '.$this->entry2->name);

        // Change selection to entry3
        $component->call('selectWinner', $this->entry3->id);
        expect($component->get('selectedEntryId'))->toBe($this->entry3->id);
        $component->assertSee('Your vote has been recorded for: '.$this->entry3->name);

        // Verify only one vote rating has value 1
        $this->contest->refresh();
        $vote = $this->contest->votes->first();
        $winnerRatings = $vote->voteRatings->where('rating', 1);
        expect($winnerRatings)->toHaveCount(1);
        expect($winnerRatings->first()->entry_id)->toBe($this->entry3->id);
    });

    it('allows changing votes through wire model updates', function () {
        $component = Livewire::test(SingleWinnerVote::class, ['contest' => $this->contest]);

        // Make initial selection using wire:model update (simulating UI interaction)
        $component->set('selectedEntryId', $this->entry1->id);
        expect($component->get('selectedEntryId'))->toBe($this->entry1->id);

        // Change selection to entry2 using wire:model update
        $component->set('selectedEntryId', $this->entry2->id);
        expect($component->get('selectedEntryId'))->toBe($this->entry2->id);

        // Ensure the component HTML still shows clickable radio buttons
        $html = $component->html();

        // Check that all radio buttons are present and not disabled
        expect($html)->toContain('value="'.$this->entry1->id.'"');
        expect($html)->toContain('value="'.$this->entry2->id.'"');
        expect($html)->toContain('value="'.$this->entry3->id.'"');

        // Ensure no radio buttons have actual disabled attributes (not CSS classes)
        // Look specifically for disabled="true" or disabled="disabled" or just disabled
        expect($html)->not->toMatch('/disabled\s*=/');

        // Change to entry3 should still work
        $component->set('selectedEntryId', $this->entry3->id);
        expect($component->get('selectedEntryId'))->toBe($this->entry3->id);
    });

    it('has proper flux radio structure with labels', function () {
        $component = Livewire::test(SingleWinnerVote::class, ['contest' => $this->contest]);

        $html = $component->html();

        // Check that we have a radio group with proper structure
        expect($html)->toContain('data-flux-radio-group');
        expect($html)->toContain('wire:model.live="selectedEntryId"');

        // Check that individual radios have labels (built into Flux radio components)
        expect($html)->toMatch('/Italian Pasta/');
        expect($html)->toMatch('/Mexican Tacos/');
        expect($html)->toMatch('/Indian Curry/');

        // Verify we have the correct number of radio elements
        preg_match_all('/data-flux-radio(?!-group)/', $html, $radios);
        expect(count($radios[0]))->toBe(3); // Should have 3 individual radios
    });

    it('debugs actual HTML output for radio button structure', function () {
        $component = Livewire::test(SingleWinnerVote::class, ['contest' => $this->contest]);

        $html = $component->html();

        // Let's dump the HTML to see what's actually being rendered
        dump('=== FULL COMPONENT HTML ===');
        dump($html);

        // Check if wire:model is actually in the output
        $wireModelPresent = str_contains($html, 'wire:model');
        dump('wire:model present: '.($wireModelPresent ? 'YES' : 'NO'));

        // Check if there are actual input elements
        $hasInputs = str_contains($html, '<input');
        dump('Has input elements: '.($hasInputs ? 'YES' : 'NO'));

        // Check what data-flux-radio elements look like
        preg_match_all('/data-flux-radio[^>]*>/', $html, $fluxRadios);
        dump('Flux radio elements found: '.count($fluxRadios[0]));
        if (! empty($fluxRadios[0])) {
            dump('Sample flux radio: '.$fluxRadios[0][0]);
        }

        // This test will always pass - it's just for debugging
        expect(true)->toBeTrue();
    });

    it('can access flux test page', function () {
        $response = $this->get('/flux-test');
        $response->assertStatus(200);
        $response->assertSee('Flux Component Test');
        $response->assertSee('Radio Buttons Test');
    });

    it('handles component errors gracefully', function () {
        expect(function () {
            // Try to create component with invalid contest
            $invalidContest = new Contest;
            $invalidContest->id = 999999;

            Livewire::test(SingleWinnerVote::class, ['contest' => $invalidContest]);
        })->toThrow(Exception::class);
    });
});

describe('Single Winner UI Display', function () {
    it('displays entry names next to radio buttons', function () {
        $component = Livewire::test(SingleWinnerVote::class, ['contest' => $this->contest]);

        // Check that entry names are visible in the component HTML
        $component
            ->assertSee('Italian Pasta')
            ->assertSee('Mexican Tacos')
            ->assertSee('Indian Curry');

        // Also check that entry descriptions are shown when configured for inline display
        $component
            ->assertSee('Homemade pasta with marinara sauce')
            ->assertSee('Authentic street tacos')
            ->assertSee('Spicy chicken curry with rice');
    });

    it('displays entry descriptions as tooltips when configured for tooltip display', function () {
        // Create a contest specifically configured for tooltip display
        $tooltipContest = Contest::create([
            'name' => 'Tooltip Contest',
            'description' => 'A contest with tooltip descriptions',
            'voting_type' => VotingType::SINGLE_WINNER,
            'rating_max' => 1,
            'entry_description_display_type' => 'tooltip',
            'voting_window_opens_at' => Carbon::now()->subHour(),
            'voting_window_closes_at' => Carbon::now()->addHour(),
        ]);

        $entry1 = Entry::create([
            'contest_id' => $tooltipContest->id,
            'name' => 'Pizza Margherita',
            'description' => 'Classic Italian pizza with fresh basil',
        ]);

        $entry2 = Entry::create([
            'contest_id' => $tooltipContest->id,
            'name' => 'Burger Deluxe',
            'description' => 'Gourmet burger with special sauce',
        ]);

        $component = Livewire::test(SingleWinnerVote::class, ['contest' => $tooltipContest]);

        // Check that entry names are visible
        $component
            ->assertSee('Pizza Margherita')
            ->assertSee('Burger Deluxe');

        // Check that descriptions are NOT shown as visible text elements (since they should be in tooltips)
        $html = $component->html();
        // The descriptions should not appear as regular text content, only in title attributes
        expect($html)->not->toContain('<p class="[:where(&)]:text-sm [:where(&)]:text-zinc-500 [:where(&)]:dark:text-white/70 mt-1">Classic Italian pizza with fresh basil</p>')
            ->not->toContain('<p class="[:where(&)]:text-sm [:where(&)]:text-zinc-500 [:where(&)]:dark:text-white/70 mt-1">Gourmet burger with special sauce</p>');

        // Check that tooltip attributes are present in the HTML
        $html = $component->html();
        expect($html)->toContain('title="Classic Italian pizza with fresh basil"');
        expect($html)->toContain('title="Gourmet burger with special sauce"');
    });
});

describe('Single Winner Validation', function () {
    it('ensures only one winner can be selected at a time', function () {
        Livewire::test(SingleWinnerVote::class, ['contest' => $this->contest])
            ->call('selectWinner', $this->entry1->id);

        $this->contest->refresh();
        $vote = $this->contest->votes->first();
        $winnerCount = $vote->voteRatings->where('rating', 1)->count();

        expect($winnerCount)->toBe(1);
    });

    it('maintains vote integrity when switching selections', function () {
        $component = Livewire::test(SingleWinnerVote::class, ['contest' => $this->contest]);

        // Make several selection changes
        $selections = [$this->entry1->id, $this->entry3->id, $this->entry2->id, null, $this->entry1->id];

        foreach ($selections as $selection) {
            $component->call('selectWinner', $selection);

            $this->contest->refresh();
            $vote = $this->contest->votes->first();
            $winnerRatings = $vote->voteRatings->where('rating', 1);

            if ($selection === null) {
                expect($winnerRatings)->toHaveCount(0);
            } else {
                expect($winnerRatings)->toHaveCount(1);
                expect($winnerRatings->first()->entry_id)->toBe($selection);
            }
        }
    });
});

describe('Contest Integration', function () {
    it('works with existing contest voting window logic', function () {
        // Create contest with closed voting window
        $closedContest = Contest::create([
            'name' => 'Closed Contest',
            'description' => 'Contest with closed voting',
            'voting_type' => VotingType::SINGLE_WINNER,
            'rating_max' => 1,
            'voting_window_opens_at' => Carbon::now()->subDays(2),
            'voting_window_closes_at' => Carbon::now()->subDay(),
        ]);

        Entry::create([
            'contest_id' => $closedContest->id,
            'name' => 'Test Entry',
            'description' => 'Test',
        ]);

        expect($closedContest->isVotingOpen())->toBeFalse();

        // Component should still work (business logic for preventing votes would be elsewhere)
        $component = Livewire::test(SingleWinnerVote::class, ['contest' => $closedContest]);
        expect($component->get('contest')->id)->toBe($closedContest->id);
    });

    it('integrates with existing contest winning entries calculation', function () {
        // Create votes that establish a clear winner
        for ($i = 0; $i < 3; $i++) {
            session()->flush();
            Livewire::test(SingleWinnerVote::class, ['contest' => $this->contest])
                ->call('selectWinner', $this->entry2->id);
        }

        // Add one vote for different entry
        session()->flush();
        Livewire::test(SingleWinnerVote::class, ['contest' => $this->contest])
            ->call('selectWinner', $this->entry1->id);

        $winners = $this->contest->getWinningEntries(2);

        expect($winners['1st']['entry']->name)->toBe('Mexican Tacos');
        expect($winners['1st']['rating'])->toBe(3.0);
        expect($winners['2nd']['entry']->name)->toBe('Italian Pasta');
        expect($winners['2nd']['rating'])->toBe(1.0);
    });
});
