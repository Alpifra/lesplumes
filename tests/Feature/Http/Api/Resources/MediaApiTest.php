<?php

namespace Tests\Feature\Http\Api\Resources;

use App\Models\Media;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class MediaApiTest extends TestCase
{

    use WithFaker;

    /**
     * Assert that a JSON has Media props
     */
    public static function assert_media_json(AssertableJson $json): AssertableJson
    {
        return $json->has('id')
            ->has('url')
            ->has('extension')
            ->has('mime_type')
            ->has('size')
            ->has('created_at')
            ->has('updated_at')
            ->etc();
    }

    /**
     * @test
     * @group api
     * @group apiGet
     * @group media
     */
    public function get_media(): void
    {
        $user = User::factory()->create(); 
        $story = Story::factory()
            ->has(Media::factory())
            ->create();

        $response = $this->actingAs($user)
            ->get("/api/stories/{$story->id}/media");

        $response->assertStatus(200)
            ->assertJson( fn (AssertableJson $json) =>
                $json->has('data')
                     ->has('data', fn (AssertableJson $json ) => self::assert_media_json($json))
        );
    }
}
