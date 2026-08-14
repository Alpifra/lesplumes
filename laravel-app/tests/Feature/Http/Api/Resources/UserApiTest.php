<?php

namespace Tests\Feature\Http\Api\Resources;

use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
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
     * @test
     * @group api
     * @group apiGet
     * @group user
     */
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

    /**
     * @test
     * @group api
     * @group apiGet
     * @group user
     */
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

    /**
     * @test
     * @group api
     * @group apiGet
     * @group user
     */
    public function a_user_can_read_another_plume(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)
            ->get("/api/users/{$other->user_name}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $other->id);
    }

    /**
     * @test
     * @group api
     * @group apiPatch
     * @group user
     */
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

    /**
     * @test
     * @group api
     * @group apiDelete
     * @group user
     */
    public function delete_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->delete("/api/users/{$user->id}");

        $response->assertStatus(204);
    }

    /**
     * @test
     * @group api
     * @group user
     */
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
