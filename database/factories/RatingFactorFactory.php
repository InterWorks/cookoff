<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RatingFactor>
 */
class RatingFactorFactory extends Factory
{
    protected static $factors = [
        'Taste',
        'Presentation',
        'Originality',
        'Creativity',
        'Use of Ingredients',
        'Smell',
        'Texture',
        'Plating',
        'Aroma',
        'Consistency',
        'Flavor Balance',
        'Temperature',
        'Aftertaste',
        'Visual Appeal',
        'Mouthfeel',
        'Complexity',
        'Seasoning',
        'Portion Size',
        'Freshness',
        'Nutritional Value',
        'Overall Impression',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'        => $this->faker->randomElement(self::$factors),
            'description' => $this->faker->text,
        ];
    }
}
