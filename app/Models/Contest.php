<?php

namespace App\Models;

use App\Models\Entry;
use App\Models\RatingFactor;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contest extends Model
{
    /** @use HasFactory<\Database\Factories\ContestFactory> */
    use HasFactory;

    /** @use SoftDeletes */
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
    ];

    /* Relationships */

    /**
     * Relationship to the entries in this contest
     *
     * @return HasMany
     */
    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }

    /**
     * Relationship to the rating factors for this contest
     *
     * @return HasMany
     */
    public function ratingFactors(): HasMany
    {
        return $this->hasMany(RatingFactor::class);
    }

    /**
     * Relationship to the votes for this contest
     *
     * @return HasMany
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /* Accessors */

    /**
     * Get the winning entries for this contest
     *
     * @param integer $numberOfEntriesToInclude The number of winning entries to include. Default is 3.
     *
     * @return array
     */
    public function getWinningEntries(int $numberOfEntriesToInclude = 3)
    {
        $entries = $this->entries->map(function ($entry) {
            return [
                'entry'  => $entry,
                'rating' => $entry->averageRating,
            ];
        })->sortByDesc('rating')->take($numberOfEntriesToInclude);

        // Only return them if the highest rated entry has a rating
        if (empty($entries->first()['rating'])) {
            $winners = [];
        } else {
            $details = $entries->values();
            $winners = [];
            foreach ($details as $index => $detail) {
                $place  = $index + 1;
                $suffix = match ($place) {
                    1 => 'st',
                    2 => 'nd',
                    3 => 'rd',
                    default => 'th',
                };

                $winners[$place . $suffix] = $detail;
            }
        }
        return $winners;
    }
}
