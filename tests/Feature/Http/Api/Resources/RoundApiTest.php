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
     * @test
     * @group api
     * @group apiGet
     * @group round
     */
    public function get_round_collection(): void
    {
        $user = User::factory()->create();
        Round::factory()->create();

        $response = $this->actingAs($user)
            ->get('/api/rounds');

        $response->assertStatus(200)
            ->assertJson( fn (AssertableJson $json) =>
                $json->has('meta')
                     ->has('links')
                     ->has('data')
                     ->has(
                        'data.0',
                        fn (AssertableJson $json) =>
                        $json->has('id')
                             ->has('master')
                             ->has('word')
                             ->has('created_at')
                             ->etc()
                    )
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
        $round = Round::factory()->create();

        $response = $this->actingAs($user)
            ->get("/api/rounds/{$round->id}");

        $response->assertStatus(200)
            ->assertJson( fn (AssertableJson $json) =>
                $json->has('data')
                     ->has(
                        'data',
                        fn (AssertableJson $json) =>
                        $json->has('id')
                             ->has('master')
                             ->has('word')
                             ->has('created_at')
                             ->etc()
                    )
        );
    }
}
