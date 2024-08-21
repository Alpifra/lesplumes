<?php

namespace Tests\Feature\Models;

use App\Models\Round;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoundTest extends TestCase
{

    use RefreshDatabase;

    /**
     * @test
     * @group model
     * @group round
     */
    public function create_model()
    {
        $round = new Round;
        $masters = User::factory(1)->create();
        $participants = User::factory(5)->create();

        $round->word = fake()->word();
        $round->master()->associate($masters->first());

        self::assertNull($round->id);

        $round->save();

        $round->participants()->saveMany($participants);
        $round->save();
        $round->refresh();

        self::assertNotNull($round->id);
        self::assertCount(1, Round::all());
        self::assertCount(5, $round->participants);
        self::assertNotNull($round->created_at);
    }

    /**
     * @test
     * @group model
     * @group round
     */
    public function edit_model()
    {
        Round::factory(1)->create();
        $masters = User::factory(1)->create();
        $master = $masters->first();
        $participants = User::factory(5)->create();
        $word = fake()->word();

        $round = Round::all()->first();
        $round->word = $word;
        $round->master()->associate($master);
        $round->save();

        $round->participants()->saveMany($participants);
        $round->save();
        $round->refresh();

        $participantsId = $round->participants->pluck('id');

        self::assertEquals($master->id, $round->master->id);
        self::assertCount(5, $round->participants);
        self::assertContains($participants->random()->id, $participantsId);
    }

    /**
     * @test
     * @group model
     * @group round
     */
    public function delete_model()
    {
        Round::factory(1)
            ->has(User::factory(4), 'participants')
            ->has(Story::factory(4), 'roundStories')
            ->create();
        $round = Round::all()->first();
        $round->delete();

        self::assertEmpty(Round::all());
        self::assertNotEmpty(User::all());
        self::assertEmpty(Story::all());
    }
}
