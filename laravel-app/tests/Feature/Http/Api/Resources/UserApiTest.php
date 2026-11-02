<?php

namespace Tests\Feature\Http\Api\Resources;

use App\Models\Media;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserApiTest extends TestCase
{

    use WithFaker;

    /**
     * Assert that a JSON has User props
     */
    public static function assert_user_json(AssertableJson $json): AssertableJson
    {
        return $json->has('id')
            ->has('first_name')
            ->has('last_name')
            ->has('user_name')
            ->has('email')
            ->has('created_at')
            ->has('updated_at')
            ->missing('password')
            ->etc();
    }

    /**
     */
    #[Test]
    #[Group('api')]
    #[Group('apiGet')]
    #[Group('user')]
    public function get_user_collection(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/api/users');

        $response->assertStatus(200)
            ->assertJson( fn (AssertableJson $json) =>
                $json->has('meta')
                     ->has('links')
                     ->has('data')
                     ->has('data.0', fn (AssertableJson $json) => self::assert_user_json($json))
        );
    }

    #[Test]
    #[Group('api')]
    #[Group('apiGet')]
    #[Group('user')]
    public function get_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get("/api/users/{$user->user_name}");

        $response->assertStatus(200)
            ->assertJson( fn (AssertableJson $json) =>
                $json->has('data')
                     ->has( 'data', fn (AssertableJson $json) => self::assert_user_json($json))
        );
    }

    #[Test]
    #[Group('api')]
    #[Group('apiGet')]
    #[Group('user')]
    public function a_user_can_read_another_plume(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)
            ->get("/api/users/{$other->user_name}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $other->id);
    }

    #[Test]
    #[Group('api')]
    #[Group('apiPatch')]
    #[Group('user')]
    public function patch_user(): void
    {
        $user = User::factory()->create();

        $first_name = fake()->firstName();
        $last_name = fake()->firstName();
        $user_name = fake()->unique()->userName();

        $response = $this->actingAs($user)
            ->patch("/api/users/{$user->id}", [
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'user_name'  => $user_name,
            ]);

        $response->assertStatus(200)
            ->assertJson( fn (AssertableJson $json) =>
                $json->has('data')
                     ->has('data', fn (AssertableJson $json) => self::assert_user_json($json))
                     ->where('data.first_name', $first_name)
                     ->where('data.last_name', $last_name)
                     ->where('data.user_name', $user_name)
            );
    }

    #[Test]
    #[Group('api')]
    #[Group('apiDelete')]
    #[Group('user')]
    public function delete_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->delete("/api/users/{$user->id}");

        $response->assertStatus(204);

        self::assertTrue($user->fresh()->isDeactivated());
    }

    #[Test]
    #[Group('api')]
    #[Group('apiDelete')]
    #[Group('user')]
    public function closing_an_account_keeps_the_plume_texts(): void
    {
        $user = User::factory()->create();
        $story = Story::factory()->for($user, 'writer')->has(Media::factory())->create();

        $this->actingAs($user)
            ->delete("/api/users/{$user->id}")
            ->assertStatus(204);

        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseHas('stories', ['id' => $story->id, 'writer_id' => $user->id]);
        self::assertNotNull($story->fresh()->media);
    }

    #[Test]
    #[Group('api')]
    #[Group('apiGet')]
    #[Group('user')]
    public function the_user_list_leaves_deactivated_plumes_out(): void
    {
        $user = User::factory()->create();
        $gone = User::factory()->create();
        $gone->deactivate();

        $ids = [];
        $page = 1;

        // The suite shares a persistent database: walk every page rather
        // than assume the newcomers land on the first one.
        do {
            $response = $this->actingAs($user)->getJson("/api/users?page={$page}")->assertStatus(200);
            $ids = array_merge($ids, $response->json('data.*.id'));
        } while ($page++ < $response->json('meta.last_page'));

        self::assertContains($user->id, $ids);
        self::assertNotContains($gone->id, $ids);
    }

    #[Test]
    #[Group('api')]
    #[Group('user')]
    #[Group('security')]
    public function a_deactivated_plume_still_logged_in_is_turned_away(): void
    {
        $user = User::factory()->create();
        $user->deactivate();

        $this->actingAs($user)
            ->getJson('/api/rounds/next')
            ->assertStatus(401);
    }

    #[Test]
    #[Group('api')]
    #[Group('user')]
    public function a_user_cannot_patch_or_delete_another_plume(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)
            ->patch("/api/users/{$other->id}", [
                'first_name' => fake()->firstName(),
                'last_name'  => fake()->firstName(),
                'user_name'  => fake()->unique()->userName(),
            ])
            ->assertStatus(403);

        $this->actingAs($user)
            ->delete("/api/users/{$other->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas('users', [
            'id'        => $other->id,
            'user_name' => $other->user_name,
        ]);
    }
}
