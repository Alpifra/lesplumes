<?php

namespace Tests\Feature\Http\Api\Resources;

use App\Models\Round;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class RoundApiTest extends TestCase
{

    use WithFaker;

    /**
     * Assert that a JSON has Round props
     */
    public static function assert_round_json(AssertableJson $json): AssertableJson
    {
        return $json->has('id')
            ->has('master', fn (AssertableJson $json) => UserApiTest::assert_user_json($json))
            ->has('participants')
            ->has('participants.data')
            ->has('participants.data.0', fn (AssertableJson $json) => UserApiTest::assert_user_json($json))
            ->has('word')
            ->has('created_at')
            ->etc();
    }

    /**
     * @test
     * @group api
     * @group apiGet
     * @group round
     */
    public function get_round_collection(): void
    {
        $user = User::factory()->create();
        $round = Round::factory()
            ->has(User::factory()->count(2), 'participants')
            ->create();
        $round->participants()->attach($user);

        $response = $this->actingAs($user)
            ->get('/api/rounds');

        $response->assertStatus(200)
            ->assertJson( fn (AssertableJson $json) =>
                $json->has('meta')
                     ->has('links')
                     ->has('data')
                     ->has('data.0', fn (AssertableJson $json) => self::assert_round_json($json))
        );
    }

    /**
     * @test
     * @group api
     * @group apiGet
     * @group round
     */
    public function round_collection_only_returns_the_rounds_of_the_authenticated_user(): void
    {
        $user = User::factory()->create();

        $participating = Round::factory()->create();
        $participating->participants()->attach($user);

        $mastering = Round::factory()->create(['master_id' => $user->id]);

        $foreign = Round::factory()
            ->has(User::factory()->count(2), 'participants')
            ->create();

        $response = $this->actingAs($user)
            ->get('/api/rounds');

        $ids = collect($response->assertStatus(200)->json('data'))->pluck('id');

        $this->assertContains($participating->id, $ids);
        $this->assertContains($mastering->id, $ids);
        $this->assertNotContains($foreign->id, $ids);
        $this->assertSame(2, $response->json('meta.total'));
    }

    /**
     * @test
     * @group api
     * @group apiGet
     * @group round
     */
    public function get_round(): void
    {
        $user = User::factory()->create();
        $round = Round::factory()
            ->has(User::factory()->count(2), 'participants')
            ->create();
        $round->participants()->attach($user);

        $response = $this->actingAs($user)
            ->get("/api/rounds/{$round->id}");

        $response->assertStatus(200)
            ->assertJson( fn (AssertableJson $json) =>
                $json->has('data')
                     ->has('data', fn (AssertableJson $json) => self::assert_round_json($json))
        );
    }

    /**
     * @test
     * @group api
     * @group apiGet
     * @group round
     */
    public function an_outsider_cannot_get_a_round(): void
    {
        $intruder = User::factory()->create();
        $round = Round::factory()
            ->has(User::factory()->count(2), 'participants')
            ->create();

        $this->actingAs($intruder)
            ->get("/api/rounds/{$round->id}")
            ->assertStatus(403);
    }

    /**
     * @test
     * @group api
     * @group apiPost
     * @group round
     */
    public function post_round(): void
    {
        $user = User::factory()->create();

        $word = fake()->word();
        $participants = User::factory(4)->create();

        // The session that closed last hands the turn to $user, which is what
        // lets her open the next one and master it.
        Round::factory()->create(['next_master_id' => $user->id]);

        $response = $this->actingAs($user)
            ->post('/api/rounds', [
                'word'         => $word,
                'master'       => $user->id,
                'participants' => $participants->pluck('id')->toArray(),
            ]);

        $response->assertStatus(201)
            ->assertJson( fn (AssertableJson $json) =>
                $json->has('data')
                     ->has('data', fn (AssertableJson $json) => self::assert_round_json($json))
                     ->where('data.word', $word)
                     ->where('data.master.id', $user->id)
                     ->where('data.participants.data.0.id', $participants->first()->id)
            );
    }

    /**
     * @test
     * @group api
     * @group apiPost
     * @group round
     */
    public function post_round_is_refused_to_a_plume_whose_turn_it_is_not(): void
    {
        $user = User::factory()->create();
        $inTurn = User::factory()->create();
        $participants = User::factory(4)->create();

        Round::factory()->create(['next_master_id' => $inTurn->id]);

        $this->actingAs($user)
            ->post('/api/rounds', [
                'word'         => fake()->word(),
                'master'       => $user->id,
                'participants' => $participants->pluck('id')->toArray(),
            ])
            ->assertStatus(403);
    }

    /**
     * @test
     * @group api
     * @group apiPatch
     * @group round
     */
    public function patch_round(): void
    {
        $user = User::factory()->create();
        $round = Round::factory()
            ->has(User::factory()->count(2), 'participants')
            ->create(['master_id' => $user->id]);

        $word = fake()->word();
        $master = User::factory()->create();
        $length = 4;
        $participants = User::factory($length)->create();

        $response = $this->actingAs($user)
            ->patch("/api/rounds/{$round->id}", [
                'word'         => $word,
                'master'       => $master->id,
                'participants' => $participants->pluck('id')->toArray(),
            ]);

        $response->assertStatus(200)
            ->assertJson( fn (AssertableJson $json) =>
                $json->has('data')
                     ->has('data', fn (AssertableJson $json) => self::assert_round_json($json))
                     ->where('data.word', $word)
                     ->where('data.master.id', $master->id)
                     ->has('data.participants.data', $length)
                     ->where('data.participants.data.0.id', $participants->first()->id)
            );
    }

    /**
     * @test
     * @group api
     * @group apiDelete
     * @group round
     */
    public function delete_round()
    {
        $user = User::factory()->create();
        $round = Round::factory()->create(['master_id' => $user->id]);

        $response = $this->actingAs($user)
            ->delete("/api/rounds/{$round->id}");

        $response->assertStatus(204);
    }

    /**
     * @test
     * @group api
     * @group round
     */
    public function only_the_master_can_patch_or_delete_a_round(): void
    {
        $participant = User::factory()->create();
        $round = Round::factory()->create();
        $round->participants()->attach($participant);

        $this->actingAs($participant)
            ->patch("/api/rounds/{$round->id}", [
                'word'         => fake()->word(),
                'master'       => $participant->id,
                'participants' => [User::factory()->create()->id],
            ])
            ->assertStatus(403);

        $this->actingAs($participant)
            ->delete("/api/rounds/{$round->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas('rounds', ['id' => $round->id]);
    }
}
