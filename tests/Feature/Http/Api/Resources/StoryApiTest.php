<?php

namespace Tests\Feature\Http\Api\Resources;

use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class StoryApiTest extends TestCase
{

    use WithFaker;

    /**
     * @test
     * @group api
     * @group apiGet
     * @group story
     */
    public function get_story_collection(): void
    {
        $user = User::factory()->create();
        Story::factory()->create();

        $response = $this->actingAs($user)
            ->get('/api/stories');

        $response->assertStatus(200)
            ->assertJson( fn (AssertableJson $json) =>
                $json->has('meta')
                     ->has('links')
                     ->has('data')
                     ->has(
                        'data.0',
                        fn (AssertableJson $json) =>
                        $json->has('id')
                             ->has('writer')
                             ->has('created_at')
                             ->has('updated_at')
                             ->etc()
                    )
        );
    }

    /**
     * @test
     * @group api
     * @group apiGet
     * @group story
     */
    public function get_story(): void
    {
        $user = User::factory()->create(); 
        $story = Story::factory()->create();

        $response = $this->actingAs($user)
            ->get("/api/stories/{$story->id}");

        $response->assertStatus(200)
            ->assertJson( fn (AssertableJson $json) =>
                $json->has('data')
                     ->has(
                        'data',
                        fn (AssertableJson $json) =>
                        $json->has('id')
                             ->has('writer')
                             ->has('created_at')
                             ->has('updated_at')
                             ->etc()
                    )
        );
    }
}
