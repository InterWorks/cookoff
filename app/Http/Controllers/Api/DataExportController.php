<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\Vote;
use App\Models\Entry;
use App\Models\VoteRating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataExportController extends Controller
{
    /**
     * Export all contests data
     */
    public function contests(): JsonResponse
    {
        $contests = Contest::with(['entries', 'ratingFactors', 'votes.voteRatings'])
            ->get()
            ->map(function ($contest) {
                return [
                    'id' => $contest->id,
                    'name' => $contest->name,
                    'description' => $contest->description,
                    'rating_max' => $contest->rating_max,
                    'voting_window_opens_at' => $contest->voting_window_opens_at,
                    'voting_window_closes_at' => $contest->voting_window_closes_at,
                    'created_at' => $contest->created_at,
                    'updated_at' => $contest->updated_at,
                    'entries_count' => $contest->entries->count(),
                    'votes_count' => $contest->votes->count(),
                    'rating_factors_count' => $contest->ratingFactors->count(),
                    'winning_entries' => $contest->getWinningEntries(3),
                    'is_voting_open' => $contest->isVotingOpen(),
                ];
            });

        return response()->json([
            'data' => $contests,
            'meta' => [
                'total' => $contests->count(),
                'exported_at' => now(),
            ]
        ]);
    }

    /**
     * Export all votes data
     */
    public function votes(): JsonResponse
    {
        $votes = Vote::with(['contest', 'voteRatings.entry', 'voteRatings.ratingFactor'])
            ->get()
            ->map(function ($vote) {
                return [
                    'id' => $vote->id,
                    'contest_id' => $vote->contest_id,
                    'contest_name' => $vote->contest->name,
                    'summary' => $vote->summary,
                    'metadata' => $vote->metadata,
                    'created_at' => $vote->created_at,
                    'updated_at' => $vote->updated_at,
                    'ratings_count' => $vote->voteRatings->count(),
                    'ratings' => $vote->voteRatings->map(function ($rating) {
                        return [
                            'entry_id' => $rating->entry_id,
                            'entry_name' => $rating->entry->name ?? 'Unknown',
                            'rating_factor_id' => $rating->rating_factor_id,
                            'rating_factor_name' => $rating->ratingFactor->name ?? 'Unknown',
                            'rating' => $rating->rating,
                        ];
                    }),
                ];
            });

        return response()->json([
            'data' => $votes,
            'meta' => [
                'total' => $votes->count(),
                'exported_at' => now(),
            ]
        ]);
    }

    /**
     * Export all entries data
     */
    public function entries(): JsonResponse
    {
        $entries = Entry::with(['contest', 'voteRatings.vote', 'voteRatings.ratingFactor'])
            ->get()
            ->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'contest_id' => $entry->contest_id,
                    'contest_name' => $entry->contest->name,
                    'name' => $entry->name,
                    'description' => $entry->description,
                    'created_at' => $entry->created_at,
                    'updated_at' => $entry->updated_at,
                    'average_rating' => $entry->averageRating,
                    'total_votes' => $entry->voteRatings->groupBy('vote_id')->count(),
                    'ratings' => $entry->voteRatings->map(function ($rating) {
                        return [
                            'vote_id' => $rating->vote_id,
                            'rating_factor_id' => $rating->rating_factor_id,
                            'rating_factor_name' => $rating->ratingFactor->name ?? 'Unknown',
                            'rating' => $rating->rating,
                        ];
                    }),
                ];
            });

        return response()->json([
            'data' => $entries,
            'meta' => [
                'total' => $entries->count(),
                'exported_at' => now(),
            ]
        ]);
    }

    /**
     * Export all vote ratings data
     */
    public function voteRatings(): JsonResponse
    {
        $voteRatings = VoteRating::with(['vote.contest', 'entry', 'ratingFactor'])
            ->get()
            ->map(function ($rating) {
                return [
                    'id' => $rating->id,
                    'vote_id' => $rating->vote_id,
                    'entry_id' => $rating->entry_id,
                    'entry_name' => $rating->entry->name ?? 'Unknown',
                    'rating_factor_id' => $rating->rating_factor_id,
                    'rating_factor_name' => $rating->ratingFactor->name ?? 'Unknown',
                    'rating' => $rating->rating,
                    'contest_id' => $rating->vote->contest_id ?? null,
                    'contest_name' => $rating->vote->contest->name ?? 'Unknown',
                    'created_at' => $rating->created_at,
                    'updated_at' => $rating->updated_at,
                ];
            });

        return response()->json([
            'data' => $voteRatings,
            'meta' => [
                'total' => $voteRatings->count(),
                'exported_at' => now(),
            ]
        ]);
    }

    /**
     * Export detailed data for a specific contest
     */
    public function singleContest(Contest $contest): JsonResponse
    {
        $contest->load(['entries.voteRatings.vote', 'ratingFactors', 'votes.voteRatings']);

        $data = [
            'contest' => [
                'id' => $contest->id,
                'name' => $contest->name,
                'description' => $contest->description,
                'rating_max' => $contest->rating_max,
                'voting_window_opens_at' => $contest->voting_window_opens_at,
                'voting_window_closes_at' => $contest->voting_window_closes_at,
                'created_at' => $contest->created_at,
                'updated_at' => $contest->updated_at,
                'is_voting_open' => $contest->isVotingOpen(),
                'winning_entries' => $contest->getWinningEntries(10), // More winners for detailed view
            ],
            'entries' => $contest->entries->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'name' => $entry->name,
                    'description' => $entry->description,
                    'average_rating' => $entry->averageRating,
                    'total_votes' => $entry->voteRatings->groupBy('vote_id')->count(),
                    'ratings_breakdown' => $entry->voteRatings->groupBy('rating_factor_id')->map(function ($ratings, $factorId) {
                        $factor = $ratings->first()->ratingFactor ?? null;
                        return [
                            'rating_factor_id' => $factorId,
                            'rating_factor_name' => $factor->name ?? 'Unknown',
                            'average' => $ratings->avg('rating'),
                            'count' => $ratings->count(),
                            'ratings' => $ratings->pluck('rating')->toArray(),
                        ];
                    }),
                ];
            }),
            'rating_factors' => $contest->ratingFactors->map(function ($factor) {
                return [
                    'id' => $factor->id,
                    'name' => $factor->name,
                    'description' => $factor->description,
                ];
            }),
            'votes' => $contest->votes->map(function ($vote) {
                return [
                    'id' => $vote->id,
                    'summary' => $vote->summary,
                    'metadata' => $vote->metadata,
                    'created_at' => $vote->created_at,
                    'ratings' => $vote->voteRatings->map(function ($rating) {
                        return [
                            'entry_id' => $rating->entry_id,
                            'entry_name' => $rating->entry->name ?? 'Unknown',
                            'rating_factor_id' => $rating->rating_factor_id,
                            'rating_factor_name' => $rating->ratingFactor->name ?? 'Unknown',
                            'rating' => $rating->rating,
                        ];
                    }),
                ];
            }),
        ];

        return response()->json([
            'data' => $data,
            'meta' => [
                'exported_at' => now(),
                'entries_count' => $contest->entries->count(),
                'votes_count' => $contest->votes->count(),
                'rating_factors_count' => $contest->ratingFactors->count(),
            ]
        ]);
    }
}
