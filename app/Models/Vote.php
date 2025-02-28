<?php

namespace App\Models;

use App\Models\Contest;
use App\Models\VoteRating;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vote extends Model
{
    /** @use HasFactory<\Database\Factories\VoteFactory> */
    use HasFactory;

    /** @use SoftDeletes */
    // use SoftDeletes;

    protected $fillable = [
        'contest_id',
        'metadata',
        'summary',
    ];

    /* Relationships */

    /**
     * Relationship to the contest this vote belongs to
     *
     * @return BelongsTo
     */
    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    /**
     * Relationship to the ratings for this vote
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
     * @param integer $entryId The ID of the entry to get the average rating for.
     *
     * @return float
     */
    public function getEntryTotalRating(int $entryId): float
    {
        if (!isset($this->summary[$entryId]['total'])) {
            $this->refreshSummary();
        }
        return $this->summary[$entryId]['total'] ?? null;
    }

    /* Utilities */

    /**
     * Refresh the summary of the vote
     *
     * @return void
     */
    public function refreshSummary(): void
    {
        $summaryJSON = $this->summary ?? "[]";
        $summary     = json_decode($summaryJSON, true);
        foreach ($this->contest->entries as $entry) {
            $entrySum     = 0;
            $hasAnyRating = false;
            foreach ($this->contest->ratingFactors as $ratingFactor) {
                $voteRating = $this->voteRatings()
                    ->where('entry_id', $entry->id)
                    ->where('rating_factor_id', $ratingFactor->id)
                    ->first();
                $rating     = $voteRating->rating ?? null;

                if (isset($rating)) {
                    $entrySum    += $rating;
                    $hasAnyRating = true;
                }

                $summary[$entry->id][$ratingFactor->id] = $rating;
            }

            // If there are no ratings, set the total to null so it doesn't affect the average
            if (!$hasAnyRating) {
                $entrySum = null;
            }

            // Save the total
            $summary[$entry->id]['total'] = $entrySum;
        }
        $this->summary = $summary;
        $this->save();
    }
}
