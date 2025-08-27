<?php

use App\Enums\VotingType;
use App\Models\Contest;

beforeEach(function () {
    // Refresh database to avoid conflicts
    $this->artisan('migrate:fresh', ['--force' => true]);
});

describe('Simple Voting Tests', function () {
    it('can create a basic contest', function () {
        $contest = Contest::create([
            'name' => 'Test Contest',
            'description' => 'A simple test contest',
            'voting_type' => 'rating',
            'rating_max' => 10,
            'entry_description_display_type' => 'inline',
        ]);

        expect($contest)->not->toBeNull();
        expect($contest->name)->toBe('Test Contest');
        expect($contest->voting_type)->toBe(VotingType::RATING);
    });
});
