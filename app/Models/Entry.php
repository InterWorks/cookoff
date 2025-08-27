<?php

namespace App\Models;

use App\Enums\VotingType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entry extends Model
{
    /** @use HasFactory<\Database\Factories\EntryFactory> */
    use HasFactory;

    /** @use SoftDeletes */
    use SoftDeletes;

    protected $fillable = [
        'contest_id',
        'name',
        'description',
    ];

    /* Relationships */

    /**
     * Relationship to the contest this entry belongs to
     *
     * @return BelongsTo
     */
    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    /**
     * Relationship to the vote ratings for this entry
     *
     * @return HasMany
     */
    public function voteRatings(): HasMany
    {
        return $this->hasMany(VoteRating::class);
    }

    /* Accessors */

    /**
     * Get the average rating for this entry
     *
     * @return float
     */
    public function getAverageRatingAttribute()
    {
        // For single-winner contests, only count non-zero ratings
        if ($this->contest->voting_type === VotingType::SINGLE_WINNER) {
            $nonZeroRatings = $this->voteRatings->filter(function ($voteRating) {
                return isset($voteRating->rating) && is_numeric($voteRating->rating) && $voteRating->rating > 0;
            });

            return (float) $nonZeroRatings->sum('rating');
        }

        // For rating contests, use traditional average calculation
        $total = $this->voteRatings->reduce(function ($carry, $voteRating) {
            $calculatedRating = $carry;
            if (isset($voteRating->rating) && is_numeric($voteRating->rating)) {
                $calculatedRating = $carry + $voteRating->rating;
            }

            return $calculatedRating;
        }, 0);

        return $this->voteRatings->count() ? $total / $this->voteRatings->count() : 0;
    }
}
