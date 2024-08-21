<?php

namespace Tests\Feature\Http\Api\Resources;

use App\Models\Media;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class StoryApiTest extends TestCase
{

    use WithFaker;

    /**
     * Assert that a JSON has Story props
     */
    public static function assert_story_json(AssertableJson $json): AssertableJson
    {
        return $json->has('id')
            ->has('writer', fn (AssertableJson $json) => UserApiTest::assert_user_json($json))
            ->has('media', fn (AssertableJson $json) => MediaApiTest::assert_media_json($json))
            ->has('created_at')
            ->has('updated_at')
            ->etc();
    }

    /**
     * @test
     * @group api
     * @group apiGet
     * @group story
     */
    public function get_story_collection(): void
    {
        $user = User::factory()->create();
        $story = Story::factory()
            ->has(Media::factory())
            ->create();

        $response = $this->actingAs($user)
            ->get("/api/rounds/{$story->round->id}/stories");

        $response->assertStatus(200)
            ->assertJson( fn (AssertableJson $json) =>
                $json->has('meta')
                     ->has('links')
                     ->has('data')
                     ->has('data.0', fn (AssertableJson $json ) => self::assert_story_json($json))
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
        $story = Story::factory()
            ->has(Media::factory())
            ->create();

        $response = $this->actingAs($user)
            ->get("/api/rounds/{$story->round->id}/stories/{$story->id}");

        $response->assertStatus(200)
            ->assertJson( fn (AssertableJson $json) =>
                $json->has('data')
                     ->has('data', fn (AssertableJson $json ) => self::assert_story_json($json))
        );
    }

    /**
     * @test
     * @group api
     * @group apiDelete
     * @group story
     */
    public function delete_story()
    {
        $user = User::factory()->create();
        $story = Story::factory()->create();

        $response = $this->actingAs($user)
            ->delete("/api/rounds/{$story->round->id}/stories/{$story->id}");

        $response->assertStatus(204);
    }
}
