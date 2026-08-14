<?php

namespace Tests\Feature\Http\Api\Resources;

use App\Models\Media;
use App\Models\Round;
use App\Models\Story;
use App\Models\User;
use Database\Factories\MediaFactory;
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
     * @group story
     * @group media
     */
    public function get_media(): void
    {
        $user = User::factory()->create();
        $story = Story::factory()
            ->has(Media::factory())
            ->create();
        $story->round->participants()->attach($user);

        $response = $this->actingAs($user)
            ->get("/api/stories/{$story->id}/media");

        $response->assertStatus(200)
            ->assertJson( fn (AssertableJson $json) =>
                $json->has('data')
                     ->has('data', fn (AssertableJson $json ) => self::assert_media_json($json))
        );
    }

    /**
     * @test
     * @group api
     * @group apiPost
     * @group story
     * @group media
     */
    public function post_media()
    {
        $user = User::factory()->create();
        $round = Round::factory()->create([
            'start_at' => now()->subWeek(),
            'end_at'   => now()->addWeek(),
        ]);
        $story = Story::factory()->for($round)->create(['writer_id' => $user->id]);
        $file = MediaFactory::createFile();

        $response = $this->actingAs($user)
            ->post("/api/stories/{$story->id}/media", [
                'file' => $file
            ]);

        $response->assertStatus(201)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->has('data')
                     ->has('data', fn (AssertableJson $json) => self::assert_media_json($json))
                     ->where('data.extension', 'pdf')
                     ->where('data.mime_type', 'application/pdf')
            );
    }

    /**
     * @test
     * @group api
     * @group apiPatch
     * @group story
     * @group media
     */
    public function patch_media()
    {
        $user = User::factory()->create();
        $round = Round::factory()->create([
            'start_at' => now()->subWeek(),
            'end_at'   => now()->addWeek(),
        ]);
        $story = Story::factory()->for($round)->has(Media::factory())->create(['writer_id' => $user->id]);
        $file = MediaFactory::createFile();

        $response = $this->actingAs($user)
            ->patch("/api/stories/{$story->id}/media/{$story->media->id}", [
                'file' => $file
            ]);

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->has('data')
                     ->has('data', fn (AssertableJson $json) => self::assert_media_json($json))
                     ->where('data.extension', 'pdf')
                     ->where('data.mime_type', 'application/pdf')
            );
    }

    /**
     * @test
     * @group api
     * @group apiDelete
     * @group media
     */
    public function delete_media()
    {
        $user = User::factory()->create();
        $story = Story::factory()
            ->has(Media::factory())
            ->create(['writer_id' => $user->id]);

        $response = $this->actingAs($user)
            ->delete("/api/stories/{$story->id}/media/{$story->media->id}");

        $response->assertStatus(204);
    }

    /**
     * @test
     * @group api
     * @group media
     */
    public function only_the_author_can_replace_or_delete_a_media(): void
    {
        $round = Round::factory()->create([
            'start_at' => now()->subWeek(),
            'end_at'   => now()->addWeek(),
        ]);
        $story = Story::factory()->for($round)->has(Media::factory())->create();
        $intruder = User::factory()->create();
        $round->participants()->attach($intruder);

        $this->actingAs($intruder)
            ->patch("/api/stories/{$story->id}/media/{$story->media->id}", [
                'file' => MediaFactory::createFile(),
            ])
            ->assertStatus(403);

        $this->actingAs($intruder)
            ->delete("/api/stories/{$story->id}/media/{$story->media->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas('medias', ['id' => $story->media->id]);
    }

    /**
     * @test
     * @group api
     * @group media
     */
    public function an_outsider_cannot_read_a_media(): void
    {
        $intruder = User::factory()->create();
        $story = Story::factory()
            ->has(Media::factory())
            ->create();

        $this->actingAs($intruder)
            ->get("/api/stories/{$story->id}/media")
            ->assertStatus(403);
    }
}
