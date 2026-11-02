<?php

namespace App\Http\Controllers;

use App\Models\Round;
use App\Models\Story;
use App\Models\User;
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
            ->map(fn (Story $story) => $story->writingDays())
            ->filter(fn (?float $days) => $days !== null);

        return response()->json([
            'data' => [
                'avg_writing_days' => $durations->isNotEmpty() ? round($durations->avg(), 1) : 0,
                'jokers_left'      => $user->jokersLeft(),
                'jokers_per_year'  => Round::JOKERS_PER_YEAR,
                'monthly_texts'    => $this->monthlyTexts($user),
            ],
        ]);
    }

    /**
     * Texts handed in over the last 12 months, the current one included, on
     * the sessions the user takes part in — as master or participant. A text
     * is counted in the month of its deposit, months without any included.
     *
     * @return array<int, array{month: string, count: int}>
     */
    private function monthlyTexts(User $user): array
    {
        $since = now()->startOfMonth()->subMonths(11);

        $counts = Story::query()
            ->has('media')
            ->where('updated_at', '>=', $since)
            ->whereHas('round', fn ($round) => $round->where(fn ($q) => $q
                ->where('master_id', $user->id)
                ->orWhereHas('participants', fn ($p) => $p->whereKey($user->id))))
            ->pluck('updated_at')
            ->countBy(fn ($date) => $date->format('Y-m'));

        return collect(range(0, 11))
            ->map(function (int $offset) use ($since, $counts): array {
                $month = $since->copy()->addMonths($offset)->format('Y-m');

                return ['month' => $month, 'count' => $counts->get($month, 0)];
            })
            ->all();
    }
}
