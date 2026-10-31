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
     * A circle of three plumes created out of alphabetical order, so the
     * rotation cannot pass by following the ids.
     *
     * @return array{auguste: User, camille: User, jeanne: User}
     */
    private function circle(): array
    {
        $jeanne = User::factory()->create(['first_name' => 'Jeanne', 'last_name' => 'Doe']);
        $auguste = User::factory()->create(['first_name' => 'Auguste', 'last_name' => 'Morel']);
        $camille = User::factory()->create(['first_name' => 'Camille', 'last_name' => 'Lefèvre']);

        return compact('auguste', 'camille', 'jeanne');
    }

    /**
     * @test
     * @group model
     * @group round
     */
    public function next_selector_opens_the_circle_on_the_first_plume()
    {
        self::assertNull(Round::nextSelector());

        ['auguste' => $auguste] = $this->circle();

        self::assertEquals($auguste->id, Round::nextSelector()->id);
    }

    /**
     * @test
     * @group model
     * @group round
     */
    public function next_selector_follows_the_circle_in_alphabetical_order()
    {
        ['auguste' => $auguste, 'camille' => $camille] = $this->circle();

        Round::factory()->create(['master_id' => $auguste->id]);

        self::assertEquals($camille->id, Round::nextSelector()->id);
    }

    /**
     * @test
     * @group model
     * @group round
     */
    public function next_selector_ignores_accents_and_case()
    {
        $emile = User::factory()->create(['first_name' => 'émile', 'last_name' => 'Roux']);
        $denise = User::factory()->create(['first_name' => 'Denise', 'last_name' => 'Petit']);
        $fanny = User::factory()->create(['first_name' => 'Fanny', 'last_name' => 'Blanc']);

        Round::factory()->create(['master_id' => $denise->id]);
        self::assertEquals($emile->id, Round::nextSelector()->id);

        Round::factory()->create(['master_id' => $emile->id, 'created_at' => now()->addMinute()]);
        self::assertEquals($fanny->id, Round::nextSelector()->id);
    }

    /**
     * @test
     * @group model
     * @group round
     */
    public function next_selector_orders_namesakes_by_last_name()
    {
        $martin = User::factory()->create(['first_name' => 'Léa', 'last_name' => 'Martin']);
        $bernard = User::factory()->create(['first_name' => 'Léa', 'last_name' => 'Bernard']);

        Round::factory()->create(['master_id' => $bernard->id]);

        self::assertEquals($martin->id, Round::nextSelector()->id);
    }

    /**
     * @test
     * @group model
     * @group round
     */
    public function next_selector_starts_over_after_the_last_plume()
    {
        ['auguste' => $auguste, 'jeanne' => $jeanne] = $this->circle();

        Round::factory()->create(['master_id' => $jeanne->id]);

        self::assertEquals($auguste->id, Round::nextSelector()->id);
    }

    /**
     * @test
     * @group model
     * @group round
     */
    public function next_selector_honours_a_hand_off()
    {
        ['auguste' => $auguste, 'jeanne' => $jeanne] = $this->circle();

        Round::factory()->create([
            'master_id'      => $auguste->id,
            'next_master_id' => $jeanne->id,
        ]);

        self::assertEquals($jeanne->id, Round::nextSelector()->id);
    }

    /**
     * @test
     * @group model
     * @group round
     */
    public function next_selector_opens_the_circle_on_the_first_active_plume()
    {
        ['auguste' => $auguste, 'camille' => $camille] = $this->circle();
        $auguste->deactivate();

        self::assertEquals($camille->id, Round::nextSelector()->id);
    }

    /**
     * @test
     * @group model
     * @group round
     */
    public function next_selector_skips_a_deactivated_plume()
    {
        ['auguste' => $auguste, 'camille' => $camille, 'jeanne' => $jeanne] = $this->circle();
        $camille->deactivate();

        Round::factory()->create(['master_id' => $auguste->id]);

        self::assertEquals($jeanne->id, Round::nextSelector()->id);
    }

    /**
     * @test
     * @group model
     * @group round
     */
    public function next_selector_resumes_after_a_deactivated_master()
    {
        ['camille' => $camille, 'jeanne' => $jeanne] = $this->circle();

        Round::factory()->create(['master_id' => $camille->id]);
        $camille->deactivate();

        self::assertEquals($jeanne->id, Round::nextSelector()->id);
    }

    /**
     * @test
     * @group model
     * @group round
     */
    public function next_selector_voids_a_hand_off_to_a_deactivated_plume()
    {
        ['auguste' => $auguste, 'camille' => $camille, 'jeanne' => $jeanne] = $this->circle();

        Round::factory()->create([
            'master_id'      => $auguste->id,
            'next_master_id' => $jeanne->id,
        ]);
        $jeanne->deactivate();

        self::assertEquals($camille->id, Round::nextSelector()->id);
    }
}
