<?php

namespace Database\Factories;

use App\Enums\VotingType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\contest>
 */
class ContestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'                           => $this->faker->city . ' ' . $this->faker->year . ' Cook-off',
            'description'                    => $this->faker->text,
            'entry_description_display_type' => $this->faker->randomElement(['hidden', 'tooltip', 'inline']),
            'voting_type'                    => VotingType::RATING,
            'rating_max'                     => 10,
        ];
    }
}
