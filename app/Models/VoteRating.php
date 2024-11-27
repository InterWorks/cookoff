<?php

namespace App\Models;

use InvalidArgumentException;
use App\Models\Entry;
use App\Models\RatingFactor;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoteRating extends Model
{
    /** @use HasFactory<\Database\Factories\VoteRatingFactory> */
    use HasFactory;

    protected $fillable = [
        'vote_id',
        'entry_id',
        'rating_factor_id',
        'rating',
    ];

    /**
     * Boot the model
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function ($voteRating) {
            $maxRating = $voteRating->entry->contest->rating_max;
            if ($voteRating->rating > $maxRating) {
                throw new InvalidArgumentException("Rating cannot exceed contest maximum of {$maxRating}");
            }
        });

        static::updating(function ($voteRating) {
            $maxRating = $voteRating->entry->contest->rating_max;
            if ($voteRating->rating > $maxRating) {
                throw new InvalidArgumentException("Rating cannot exceed contest maximum of {$maxRating}");
            }
        });
    }

    /**
     * Relationship to the entry this rating belongs to
     *
     * @return BelongsTo
     */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }

    /**
     * Relationship to the rating factor this rating belongs to
     *
     * @return BelongsTo
     */
    public function ratingFactor(): BelongsTo
    {
        return $this->belongsTo(RatingFactor::class);
    }

    /**
     * Relationship to the vote this rating belongs to
     *
     * @return BelongsTo
     */
    public function vote(): BelongsTo
    {
        return $this->belongsTo(Vote::class);
    }
}
