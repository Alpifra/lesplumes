<?php

namespace App\Http\Controllers;

use App\Models\Round;
use App\Models\Story;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RankingController extends Controller
{
    /**
     * The rankings of the "Statistique" tab, over the whole history of the
     * circle. Only the plumes still in it are ranked; a plume with nothing to
     * rank yet (no text, no session) is left out of that ranking.
     */
    public function index(): JsonResponse
    {
        $plumes = User::active()->inCircleOrder()->get();

        // Every text handed in by the circle, with what the rankings read.
        $stories = Story::query()
            ->whereIn('writer_id', $plumes->pluck('id'))
            ->has('media')
            ->with(['media', 'round:id,start_at'])
            ->get()
            ->groupBy('writer_id');

        return response()->json([
            'data' => [
                'reactivity'     => $this->reactivity($plumes, $stories),
                'participations' => $this->participations($plumes),
                'jokers'         => $this->jokers($plumes),
                'blabla'         => $this->blabla($plumes, $stories),
            ],
        ]);
    }

    /**
     * Average days from the opening of a session to the deposit of the text,
     * quickest first. Jokers hand nothing in, so they do not count.
     */
    private function reactivity(Collection $plumes, Collection $stories): array
    {
        return $plumes
            ->map(function (User $plume) use ($stories) {
                $days = ($stories[$plume->id] ?? collect())
                    ->map(fn (Story $story) => $story->writingDays())
                    ->filter(fn (?float $days) => $days !== null);

                return $days->isEmpty() ? null : [
                    'plume' => static::plume($plume),
                    'value' => round($days->avg(), 1),
                    'texts' => $days->count(),
                ];
            })
            ->filter()
            ->sortBy('value')
            ->values()
            ->all();
    }

    /**
     * Average characters per text, wordiest first — the average rather than
     * the total, so the number of sessions played does not weigh in.
     * PDFs without readable text (scans) are left out.
     */
    private function blabla(Collection $plumes, Collection $stories): array
    {
        return $plumes
            ->map(function (User $plume) use ($stories) {
                $characters = ($stories[$plume->id] ?? collect())
                    ->map(fn (Story $story) => $story->media?->characters)
                    ->filter();

                return $characters->isEmpty() ? null : [
                    'plume' => static::plume($plume),
                    'value' => (int) round($characters->avg()),
                    'total' => $characters->sum(),
                    'texts' => $characters->count(),
                ];
            })
            ->filter()
            ->sortByDesc('value')
            ->values()
            ->all();
    }

    /**
     * Sessions each plume took part in, most first: as master, or convened
     * without playing a joker — a joker counts as sitting the session out.
     */
    private function participations(Collection $plumes): array
    {
        $convened = DB::table('round_user')
            ->whereIn('user_id', $plumes->pluck('id'))
            ->whereNull('joker_at')
            ->groupBy('user_id')
            ->selectRaw('user_id, count(*) as sessions')
            ->pluck('sessions', 'user_id');

        $mastered = Round::query()
            ->whereIn('master_id', $plumes->pluck('id'))
            ->groupBy('master_id')
            ->selectRaw('master_id, count(*) as sessions')
            ->pluck('sessions', 'master_id');

        return $plumes
            ->map(function (User $plume) use ($convened, $mastered) {
                $asMaster = (int) ($mastered[$plume->id] ?? 0);
                $sessions = (int) ($convened[$plume->id] ?? 0) + $asMaster;

                return $sessions === 0 ? null : [
                    'plume'     => static::plume($plume),
                    'value'     => $sessions,
                    'as_master' => $asMaster,
                ];
            })
            ->filter()
            ->sortByDesc('value')
            ->values()
            ->all();
    }

    /**
     * Jokers played over the circle's history, fewest first, out of the
     * sessions each plume was convened to — on a tie, the plume convened the
     * most comes first. A joker taken back is not counted.
     */
    private function jokers(Collection $plumes): array
    {
        $counts = DB::table('round_user')
            ->whereIn('user_id', $plumes->pluck('id'))
            ->groupBy('user_id')
            ->selectRaw('user_id, count(joker_at) as jokers, count(*) as sessions')
            ->get()
            ->keyBy('user_id');

        return $plumes
            ->filter(fn (User $plume) => isset($counts[$plume->id]))
            ->map(fn (User $plume) => [
                'plume'    => static::plume($plume),
                'value'    => (int) $counts[$plume->id]->jokers,
                'sessions' => (int) $counts[$plume->id]->sessions,
            ])
            ->sortBy([['value', 'asc'], ['sessions', 'desc']])
            ->values()
            ->all();
    }

    /**
     * What a ranking shows of a plume.
     */
    private static function plume(User $plume): array
    {
        return [
            'id'         => $plume->id,
            'first_name' => $plume->first_name,
            'last_name'  => $plume->last_name,
        ];
    }
}
