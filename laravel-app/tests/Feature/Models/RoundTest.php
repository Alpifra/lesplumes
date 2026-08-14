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

    /**
     * @test
     * @group model
     * @group round
     */
    public function next_selector_opens_the_circle_on_the_first_plume()
    {
        self::assertNull(Round::nextSelector());

        $plumes = User::factory(3)->create();

        self::assertEquals($plumes->first()->id, Round::nextSelector()->id);
    }

    /**
     * @test
     * @group model
     * @group round
     */
    public function next_selector_follows_the_circle_by_ascending_id()
    {
        $plumes = User::factory(3)->create();

        Round::factory()->create(['master_id' => $plumes[0]->id]);

        self::assertEquals($plumes[1]->id, Round::nextSelector()->id);
    }

    /**
     * @test
     * @group model
     * @group round
     */
    public function next_selector_starts_over_after_the_last_plume()
    {
        $plumes = User::factory(3)->create();

        Round::factory()->create(['master_id' => $plumes->last()->id]);

        self::assertEquals($plumes->first()->id, Round::nextSelector()->id);
    }

    /**
     * @test
     * @group model
     * @group round
     */
    public function next_selector_honours_a_hand_off()
    {
        $plumes = User::factory(3)->create();

        Round::factory()->create([
            'master_id'      => $plumes[0]->id,
            'next_master_id' => $plumes->last()->id,
        ]);

        self::assertEquals($plumes->last()->id, Round::nextSelector()->id);
    }
}
