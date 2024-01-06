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
        Round::all()->first()->delete(); // remove entries from prev tests
        $user = User::factory()->create();
        Round::factory()
            ->has(User::factory()->count(2), 'participants')
            ->create();

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
    public function get_round(): void
    {
        $user = User::factory()->create(); 
        $round = Round::factory()
            ->has(User::factory()->count(2), 'participants')
            ->create();

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
     * @group apiPost
     * @group round
     */
    public function post_round(): void
    {
        $user = User::factory()->create();

        $word = fake()->word();
        $master = User::factory()->create();
        $participants = User::factory(4)->create();

        $response = $this->actingAs($user)
            ->post('/api/rounds', [
                'word'         => $word,
                'master'       => $master->id,
                'participants' => $participants->pluck('id')->toArray(),
            ]);

        $response->assertStatus(201)
            ->assertJson( fn (AssertableJson $json) =>
                $json->has('data')
                     ->has('data', fn (AssertableJson $json) => self::assert_round_json($json))
                     ->where('data.word', $word)
                     ->where('data.master.id', $master->id)
                     ->where('data.participants.data.0.id', $participants->first()->id)
            );
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
            ->create();

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
}
