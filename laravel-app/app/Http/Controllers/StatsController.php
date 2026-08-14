<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    /**
     * Aggregate stats for the authenticated user, computed over every round
     * (unpaginated) so they don't depend on the current page of the lists.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Only the texts the user actually deposited (a PDF is attached),
        // loaded with their round's start date to measure the writing delay.
        $stories = $user->writerStories()
            ->has('media')
            ->with('round:id,start_at')
            ->get();

        $durations = $stories
            ->filter(fn (Story $story) => $story->round?->start_at !== null)
            ->map(function (Story $story): float {
                // Writing delay = deposit (story.updated_at) − round start, in days.
                $seconds = $story->updated_at->getTimestamp() - $story->round->start_at->getTimestamp();

                return max(0, $seconds / 86400);
            });

        return response()->json([
            'data' => [
                'avg_writing_days' => $durations->isNotEmpty() ? round($durations->avg(), 1) : 0,
            ],
        ]);
    }
}
