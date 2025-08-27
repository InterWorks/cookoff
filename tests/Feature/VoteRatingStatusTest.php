<?php

use App\Livewire\VoteRating;
use App\Models\Contest;
use App\Models\Entry;
use App\Models\RatingFactor;
use Livewire\Livewire;

beforeEach(function () {
    // Refresh database to avoid conflicts
    $this->artisan('migrate:fresh', ['--force' => true]);

    $this->contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'A test contest for rating status',
        'voting_type' => \App\Enums\VotingType::RATING,
        'rating_min' => 0,
        'rating_max' => 10,
        'entry_description_display_type' => 'inline',
        'voting_window_opens_at' => now()->subHour(),
        'voting_window_closes_at' => now()->addHour(),
    ]);
    $this->entry = Entry::create([
        'contest_id' => $this->contest->id,
        'name' => 'Test Entry',
        'description' => 'Test entry description',
    ]);
    $this->ratingFactor = RatingFactor::create([
        'contest_id' => $this->contest->id,
        'name' => 'Test Factor',
        'description' => 'Test rating factor',
    ]);
});

test('vote rating shows success status after successful save', function () {
    $component = Livewire::test(VoteRating::class, [
        'contest' => $this->contest,
        'entry' => $this->entry,
        'ratingFactor' => $this->ratingFactor,
        'mode' => 'Desktop',
    ]);

    $component
        ->set('rating', 8)
        ->call('updateRating')
        ->assertSet('saveStatus', 'success');
});

test('vote rating shows error status when save fails', function () {
    // Create a component with a contest that has an entry that doesn't exist in database
    $component = Livewire::test(VoteRating::class, [
        'contest' => $this->contest,
        'entry' => $this->entry,
        'ratingFactor' => $this->ratingFactor,
        'mode' => 'Desktop',
    ]);

    // Delete the entry to cause a database error when trying to save
    $this->entry->delete();

    $component
        ->set('rating', 8)
        ->call('updateRating')
        ->assertSet('saveStatus', 'error');
});

test('vote rating resets status when field is edited', function () {
    $component = Livewire::test(VoteRating::class, [
        'contest' => $this->contest,
        'entry' => $this->entry,
        'ratingFactor' => $this->ratingFactor,
        'mode' => 'Desktop',
    ]);

    // First save successfully
    $component
        ->set('rating', 8)
        ->call('updateRating')
        ->assertSet('saveStatus', 'success');

    // Then edit the field, which should reset status
    $component
        ->set('rating', 7)
        ->assertSet('saveStatus', null);
});

test('vote rating validation error shows validation status', function () {
    $component = Livewire::test(VoteRating::class, [
        'contest' => $this->contest,
        'entry' => $this->entry,
        'ratingFactor' => $this->ratingFactor,
        'mode' => 'Desktop',
    ]);

    $component
        ->set('rating', 15) // Above max rating of 10
        ->call('updateRating')
        ->assertHasErrors('rating')
        ->assertSet('saveStatus', 'error');
});

test('vote rating does not overwrite itself during update', function () {
    $component = Livewire::test(VoteRating::class, [
        'contest' => $this->contest,
        'entry' => $this->entry,
        'ratingFactor' => $this->ratingFactor,
        'mode' => 'Desktop',
    ]);

    // First save a rating
    $component
        ->set('rating', 2)
        ->call('updateRating')
        ->assertSet('saveStatus', 'success')
        ->assertSet('rating', 2);

    // Now change to 3 - this should not revert to 2
    $component
        ->set('rating', 3)
        ->call('updateRating')
        ->assertSet('saveStatus', 'success')
        ->assertSet('rating', 3);
});
