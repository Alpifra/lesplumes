<?php

namespace Tests\Feature\Http\Api\Resources;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AuthenticationApiTest extends TestCase
{

    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     * @group api
     * @group apiPost
     * @group user
     * @group security
     */
    public function user_can_login_with_success(): void
    {
        $this->refreshDatabase();
        $this->seed();

        $response = $this->postJson('/api/login', [
            'username' => 'admin@email.com',
            'password' => 'password'
        ]);

        $response->assertStatus(200);
    }

    /**
     * @test
     * @group api
     * @group apiPost
     * @group user
     * @group security
     */
    public function user_cannot_login_with_wrong_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'username' => 'wrong@email.com',
            'password' => 'password'
        ]);

        $response->assertStatus(422);
        $response->assertJson( fn (AssertableJson $json) =>
            $json->has('message')
                 ->has('errors', fn (AssertableJson $json) =>
                    $json->has('username')
                         ->where('username', [0 => 'These credentials do not match our records.'])
                    )
        );
    }

    /**
     * @test
     * @group api
     * @group apiPost
     * @group user
     * @group security
     */
    public function a_deactivated_plume_cannot_login(): void
    {
        $user = User::factory()->create();
        $user->deactivate();

        $this->postJson('/api/login', [
            'username' => $user->email,
            'password' => 'password',
        ])
            ->assertStatus(422)
            ->assertJsonPath('errors.username.0', 'Ce compte est désactivé.');

        $this->assertGuest();
    }

    /**
     * @test
     * @group api
     * @group apiPost
     * @group user
     * @group security
     */
    public function a_deactivated_account_is_not_revealed_without_its_password(): void
    {
        $user = User::factory()->create();
        $user->deactivate();

        $this->postJson('/api/login', [
            'username' => $user->email,
            'password' => 'wrong-password',
        ])
            ->assertStatus(422)
            ->assertJsonPath('errors.username.0', 'These credentials do not match our records.');
    }
}
