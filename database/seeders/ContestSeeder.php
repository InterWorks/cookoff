<?php

namespace Database\Seeders;

use App\Models\Contest;
use App\Models\Entry;
use App\Models\RatingFactor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Create some contests
        Contest::factory(10)->create();

        // Create entries for each contest
        Contest::all()->each(function (Contest $contest): void {
            $contest->entries()->createMany(
                Entry::factory(5)->make()->toArray()
            );
            $contest->ratingFactors()->createMany(
                RatingFactor::factory(5)->make()->toArray()
            );
        });
    }
}
