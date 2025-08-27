<?php

use App\Enums\VotingType;
use App\Models\Contest;

it('has correct enum values', function () {
    expect(VotingType::RATING->value)->toBe('rating');
    expect(VotingType::SINGLE_WINNER->value)->toBe('single_winner');
});

it('can be created from string values', function () {
    expect(VotingType::from('rating'))->toBe(VotingType::RATING);
    expect(VotingType::from('single_winner'))->toBe(VotingType::SINGLE_WINNER);
});

it('provides correct labels', function () {
    expect(VotingType::RATING->label())->toBe('Rating System');
    expect(VotingType::SINGLE_WINNER->label())->toBe('Single Winner');
});

it('provides correct descriptions', function () {
    expect(VotingType::RATING->description())->toBe('Voters rate each entry across multiple factors');
    expect(VotingType::SINGLE_WINNER->description())->toBe('Voters select one winning entry');
});

it('has all expected cases', function () {
    $cases = VotingType::cases();

    expect($cases)->toHaveCount(2);
    expect($cases)->toContain(VotingType::RATING);
    expect($cases)->toContain(VotingType::SINGLE_WINNER);
});

it('contest model includes voting_type in fillable', function () {
    $contest = new Contest;
    expect($contest->getFillable())->toContain('voting_type');
});

it('contest model has correct casting for voting_type', function () {
    $contest = new Contest;
    $casts = $contest->getCasts();
    expect($casts['voting_type'])->toBe(VotingType::class);
});
