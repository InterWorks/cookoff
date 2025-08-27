<?php

namespace App\Enums;

enum VotingType: string
{
    case RATING        = 'rating';
    case SINGLE_WINNER = 'single_winner';

    /**
     * Get the human-readable label for the voting type.
     *
     * @return string The display label for this voting type
     */
    public function label(): string
    {
        return match ($this) {
            self::RATING        => 'Rating System',
            self::SINGLE_WINNER => 'Single Winner',
        };
    }

    /**
     * Get a detailed description of how this voting type works.
     *
     * @return string A descriptive explanation of the voting mechanism
     */
    public function description(): string
    {
        return match ($this) {
            self::RATING        => 'Voters rate each entry across multiple factors',
            self::SINGLE_WINNER => 'Voters select one winning entry',
        };
    }
}
