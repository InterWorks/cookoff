<?php

namespace App\Models;

use App\Models\Contest;
use App\Models\VoteRating;
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
     * Relationship to the votes for this entry
     *
     * @return HasMany
     */
    public function votes(): HasMany
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
        $total = $this->votes->reduce(function ($carry, $vote) {
            $calculatedRating = $carry;
            if (isset($vote->rating) && is_numeric($vote->rating)) {
                $calculatedRating = $carry + $vote->rating;
            }
            return $calculatedRating;
        }, 0);
        return $this->votes->count() ? $total / $this->votes->count() : 0;
    }
}
