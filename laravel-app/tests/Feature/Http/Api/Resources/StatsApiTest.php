<?php

namespace Tests\Feature\Http\Api\Resources;

use App\Models\Media;
use App\Models\Round;
use App\Models\Story;
use App\Models\User;
use Database\Factories\MediaFactory;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StatsApiTest extends TestCase
{
    /** A text handed in on the round, deposited on the given date. */
    private function handIn(Round $round, $date, bool $withMedia = true): Story
    {
        $story = Story::factory()->for($round)->create();

        if ($withMedia) {
            Media::storeForStory($story, MediaFactory::createFile());
        }

        DB::table('stories')->where('id', $story->id)->update(['updated_at' => $date]);

        return $story;
    }

    /**
     */
    #[Test]
    #[Group('api')]
    #[Group('stats')]
    public function texts_are_counted_month_by_month_over_a_year(): void
    {
        $user = User::factory()->create();

        $round = Round::factory()->create();
        $round->participants()->attach($user);

        $mastered = Round::factory()->create(['master_id' => $user->id]);

        $this->handIn($round, now());
        $this->handIn($mastered, now());
        $this->handIn($round, now()->subMonths(3));
        // Out of the window, not handed in, or on a round she is not part of.
        $this->handIn($round, now()->subMonths(12));
        $this->handIn($round, now(), withMedia: false);
        $this->handIn(Round::factory()->create(), now());

        $months = $this->actingAs($user)->getJson('/api/stats')
            ->assertStatus(200)
            ->json('data.monthly_texts');

        $this->assertCount(12, $months);
        $this->assertSame(now()->startOfMonth()->subMonths(11)->format('Y-m'), $months[0]['month']);
        $this->assertSame(['month' => now()->format('Y-m'), 'count' => 2], $months[11]);
        $this->assertSame(1, $months[8]['count']);
        $this->assertSame(3, array_sum(array_column($months, 'count')));
    }
}
