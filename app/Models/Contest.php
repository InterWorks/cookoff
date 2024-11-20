<?php

namespace App\Models;

use Exception;
use App\Models\Entry;
use App\Models\RatingFactor;
use App\Models\Vote;
use Carbon\Carbon;
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
        'entry_description_display_type',
        'voting_window_opens_at',
        'voting_window_closes_at',
    ];

    protected $dates = [
        'voting_window_opens_at',
        'voting_window_closes_at',
    ];

    protected $casts = [
        'voting_window_opens_at'  => 'datetime',
        'voting_window_closes_at' => 'datetime',
    ];

    /**
     * Boot the model
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($model) {
            $timezone = config('app.timezone');

            if ($model->voting_window_opens_at) {
                $model->voting_window_opens_at = Carbon::parse($model->voting_window_opens_at, $timezone)
                    ->setTimezone('UTC')
                    ->format('Y-m-d H:i:s');
            }

            if ($model->voting_window_closes_at) {
                $model->voting_window_closes_at = Carbon::parse($model->voting_window_closes_at, $timezone)
                    ->setTimezone('UTC')
                    ->format('Y-m-d H:i:s');
            }
        });
    }

    /**
     * Get the validation rules for this model
     *
     * @return array
     */
    public static function rules(): array
    {
        return [
            'voting_window_opens_at'  => ['nullable', 'date', 'before_or_equal:voting_window_closes_at'],
            'voting_window_closes_at' => ['nullable', 'date', 'after_or_equal:voting_window_opens_at'],
        ];
    }

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

    /**
     * Get the average rating for this contest
     *
     * @return float
     */
    public function isVotingOpen(): bool
    {
        $timezone = config('app.timezone');

        $votingWindowOpensAt  = $this->voting_window_opens_at
            ? Carbon::parse($this->voting_window_opens_at)->timezone($timezone)
            : null;
        $votingWindowClosesAt = $this->voting_window_closes_at
            ? Carbon::parse($this->voting_window_closes_at)->timezone($timezone)
            : null;

        $now = Carbon::now($timezone);

        if ($votingWindowOpensAt && $votingWindowClosesAt) {
            $isOpen = $now->between($votingWindowOpensAt, $votingWindowClosesAt);
        } elseif ($votingWindowOpensAt) {
            $isOpen = $now->gte($votingWindowOpensAt);
        } elseif ($votingWindowClosesAt) {
            $isOpen = $now->lte($votingWindowClosesAt);
        } else {
            $isOpen = true;
        }

        return $isOpen;
    }
}
