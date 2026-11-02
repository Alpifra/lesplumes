<?php

namespace Tests\Feature\Http\Api\Resources;

use App\Models\Media;
use App\Models\Round;
use App\Models\Story;
use App\Models\User;
use Database\Factories\MediaFactory;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RankingApiTest extends TestCase
{
    /** A text the plume handed in, the given days after the opening of a session. */
    private function handIn(User $plume, int $days, ?int $characters = 35): Story
    {
        $round = Round::factory()->create([
            'start_at' => now()->subDays(30),
            'end_at'   => now()->subDay(),
        ]);
        $round->participants()->attach($plume);

        $story = Story::factory()->for($round)->create(['writer_id' => $plume->id]);
        Media::storeForStory($story, MediaFactory::createFile())
            ->update(['characters' => $characters]);

        DB::table('stories')->where('id', $story->id)
            ->update(['updated_at' => now()->subDays(30 - $days)]);

        return $story;
    }

    /** The entries of a ranking that belong to the given plumes, in order. */
    private function rankingOf(string $ranking, User ...$plumes): array
    {
        $ids = array_map(fn (User $plume) => $plume->id, $plumes);

        return collect($this->actingAs($plumes[0])->getJson('/api/rankings')
                ->assertStatus(200)
                ->json("data.{$ranking}"))
            ->filter(fn (array $entry) => in_array($entry['plume']['id'], $ids))
            ->values()
            ->all();
    }

    /**
     * @test
     * @group api
     * @group rankings
     */
    public function the_quickest_plume_comes_first_in_reactivity(): void
    {
        [$slow, $quick, $idle] = User::factory()->count(3)->create();
        $this->handIn($slow, 10);
        $this->handIn($slow, 6);
        $this->handIn($quick, 2);

        $ranking = $this->rankingOf('reactivity', $slow, $quick, $idle);

        $this->assertSame([$quick->id, $slow->id], array_column(array_column($ranking, 'plume'), 'id'));
        $this->assertEquals(8.0, $ranking[1]['value']);
        $this->assertSame(2, $ranking[1]['texts']);
    }

    /**
     * @test
     * @group api
     * @group rankings
     */
    public function the_wordiest_plume_comes_first_in_blabla(): void
    {
        [$brief, $wordy] = User::factory()->count(2)->create();
        $this->handIn($brief, 1, 1000);
        $this->handIn($brief, 1, 2000);
        $this->handIn($brief, 1, null); // a scan, without readable text
        $this->handIn($wordy, 1, 5000);

        $ranking = $this->rankingOf('blabla', $brief, $wordy);

        $this->assertSame([$wordy->id, $brief->id], array_column(array_column($ranking, 'plume'), 'id'));
        $this->assertSame(['value' => 1500, 'total' => 3000, 'texts' => 2],
            array_intersect_key($ranking[1], array_flip(['value', 'total', 'texts'])));
    }

    /**
     * @test
     * @group api
     * @group rankings
     */
    public function the_plume_with_the_fewest_jokers_comes_first(): void
    {
        [$sparing, $joker, $diligent, $newcomer, $outsider] = User::factory()->count(5)->create();
        $rounds = Round::factory()->count(3)->create();
        foreach ($rounds as $i => $round) {
            $round->participants()->attach([
                $joker->id    => ['joker_at' => $i < 2 ? now() : null],
                $sparing->id  => ['joker_at' => $i === 0 ? now() : null],
                $diligent->id => ['joker_at' => null],
            ]);
        }
        // Never played a joker either, but convened only once.
        $rounds[0]->participants()->attach($newcomer->id, ['joker_at' => null]);

        $ranking = $this->rankingOf('jokers', $sparing, $joker, $diligent, $newcomer, $outsider);

        $this->assertSame([
            ['id' => $diligent->id, 'value' => 0, 'sessions' => 3],
            ['id' => $newcomer->id, 'value' => 0, 'sessions' => 1],
            ['id' => $sparing->id, 'value' => 1, 'sessions' => 3],
            ['id' => $joker->id, 'value' => 2, 'sessions' => 3],
        ], array_map(fn ($entry) => ['id' => $entry['plume']['id'], 'value' => $entry['value'], 'sessions' => $entry['sessions']], $ranking));
    }

    /**
     * @test
     * @group api
     * @group rankings
     */
    public function the_plume_with_the_most_sessions_comes_first_in_participations(): void
    {
        [$keen, $joker, $gone] = User::factory()->count(3)->create();
        Round::factory()->create(['master_id' => $keen->id])
            ->participants()->attach([$joker->id, $gone->id]);
        Round::factory()->create()
            ->participants()->attach([$keen->id => ['joker_at' => null], $joker->id => ['joker_at' => now()]]);
        $gone->deactivate();

        $ranking = $this->rankingOf('participations', $keen, $joker, $gone);

        $this->assertSame([
            ['id' => $keen->id, 'value' => 2, 'as_master' => 1],
            ['id' => $joker->id, 'value' => 1, 'as_master' => 0],
        ], array_map(fn ($entry) => ['id' => $entry['plume']['id'], 'value' => $entry['value'], 'as_master' => $entry['as_master']], $ranking));
    }
}
