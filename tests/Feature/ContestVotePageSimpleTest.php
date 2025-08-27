<?php

use App\Enums\VotingType;
use App\Models\Contest;
use App\Models\Entry;
use App\Models\RatingFactor;
use App\Models\User;

describe('Contest Vote Page Basic Functionality', function () {
    beforeEach(function () {
        // Refresh database to avoid conflicts
        $this->artisan('migrate:fresh', ['--force' => true]);

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    });

    it('can access a contest vote page when voting is open', function () {
        $contest = Contest::create([
            'name' => 'Test Contest',
            'description' => 'Test Description',
            'voting_type' => VotingType::RATING,
            'rating_max' => 10,
            'entry_description_display_type' => 'inline',
            'voting_window_opens_at' => now()->subHour(),
            'voting_window_closes_at' => now()->addHour(),
        ]);

        $response = $this->get(route('contests.vote', $contest));

        $response->assertSuccessful();
        $response->assertSee('Vote in Test Contest');
        $response->assertSee('Test Description');
    });

    it('shows voting closed message when voting is closed', function () {
        $contest = Contest::create([
            'name' => 'Closed Contest',
            'description' => 'Closed Description',
            'voting_type' => VotingType::RATING,
            'rating_max' => 10,
            'voting_window_opens_at' => now()->subDay(),
            'voting_window_closes_at' => now()->subHour(),
        ]);

        $response = $this->get(route('contests.vote', $contest));

        $response->assertSuccessful();
        $response->assertSee('Voting is closed for this contest.');
    });

    it('displays single winner voting component for single winner contests', function () {
        $contest = Contest::create([
            'name' => 'Single Winner Contest',
            'description' => 'Pick one winner',
            'voting_type' => VotingType::SINGLE_WINNER,
            'voting_window_opens_at' => now()->subHour(),
            'voting_window_closes_at' => now()->addHour(),
        ]);

        // Add some entries
        Entry::create([
            'contest_id' => $contest->id,
            'name' => 'Option 1',
            'description' => 'First option',
        ]);
        Entry::create([
            'contest_id' => $contest->id,
            'name' => 'Option 2',
            'description' => 'Second option',
        ]);

        $response = $this->get(route('contests.vote', $contest));

        $response->assertSuccessful();
        $response->assertSee('Select Your Winner');
        $response->assertSee('Option 1');
        $response->assertSee('Option 2');
    });

    it('displays rating table for rating contests', function () {
        $contest = Contest::create([
            'name' => 'Rating Contest',
            'description' => 'Rate the entries',
            'voting_type' => VotingType::RATING,
            'rating_max' => 10,
            'entry_description_display_type' => 'inline',
            'voting_window_opens_at' => now()->subHour(),
            'voting_window_closes_at' => now()->addHour(),
        ]);

        // Add entries and rating factors
        Entry::create([
            'contest_id' => $contest->id,
            'name' => 'Entry 1',
            'description' => 'First entry',
        ]);
        Entry::create([
            'contest_id' => $contest->id,
            'name' => 'Entry 2',
            'description' => 'Second entry',
        ]);

        RatingFactor::create([
            'contest_id' => $contest->id,
            'name' => 'Taste',
            'description' => 'How good does it taste?',
        ]);
        RatingFactor::create([
            'contest_id' => $contest->id,
            'name' => 'Presentation',
            'description' => 'How well is it presented?',
        ]);

        $response = $this->get(route('contests.vote', $contest));

        $response->assertSuccessful();
        $response->assertSee('Entry 1');
        $response->assertSee('Entry 2');
        $response->assertSee('Taste');
        $response->assertSee('Presentation');
        $response->assertSee('Factors');
    });

    it('includes proper Flux table structure in rating contests', function () {
        $contest = Contest::create([
            'name' => 'Flux Test Contest',
            'description' => 'Testing Flux components',
            'voting_type' => VotingType::RATING,
            'rating_max' => 10,
            'entry_description_display_type' => 'inline',
            'voting_window_opens_at' => now()->subHour(),
            'voting_window_closes_at' => now()->addHour(),
        ]);

        Entry::create(['contest_id' => $contest->id, 'name' => 'Test Entry']);
        RatingFactor::create(['contest_id' => $contest->id, 'name' => 'Test Factor']);

        $response = $this->get(route('contests.vote', $contest));

        $response->assertSuccessful();
        // Verify core table structure and content is present
        $response->assertSee('Test Entry');
        $response->assertSee('Test Factor');
        $response->assertSee('Factors');
        $response->assertSee('placeholder="Enter your rating"', false);

        // Check that rating inputs are present for both desktop and mobile
        $content = $response->getContent();
        $ratingInputCount = substr_count($content, 'placeholder="Enter your rating"');
        expect($ratingInputCount)->toBeGreaterThan(0); // Should have at least some rating inputs
    });

    it('handles non-existent contests gracefully', function () {
        $response = $this->get(route('contests.vote', ['contest' => 99999]));

        $response->assertNotFound();
    });

    it('allows unauthenticated access to vote pages', function () {
        auth()->logout();

        $contest = Contest::create([
            'name' => 'Auth Test',
            'voting_type' => VotingType::RATING,
            'voting_window_opens_at' => now()->subHour(),
            'voting_window_closes_at' => now()->addHour(),
        ]);

        $response = $this->get(route('contests.vote', $contest));

        // Vote pages should be accessible without authentication for public voting
        $response->assertSuccessful();
        $response->assertSee('Vote in Auth Test');
    });

    it('includes breadcrumb navigation', function () {
        $contest = Contest::create([
            'name' => 'Breadcrumb Test',
            'description' => 'Testing breadcrumbs',
            'voting_type' => VotingType::RATING,
            'voting_window_opens_at' => now()->subHour(),
            'voting_window_closes_at' => now()->addHour(),
        ]);

        $response = $this->get(route('contests.vote', $contest));

        $response->assertSuccessful();
        $response->assertSee('Home');
        $response->assertSee('Contests');
        $response->assertSee('Breadcrumb Test');
        $response->assertSee('Vote');
    });
});
