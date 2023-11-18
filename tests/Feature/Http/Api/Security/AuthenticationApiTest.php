<?php

namespace Tests\Feature\Http\Api\Resources;

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
            'email'    => 'admin@email.com',
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
            'email'    => 'wrong@email.com',
            'password' => 'password'
        ]);

        $response->assertStatus(422);
        $response->assertJson( fn (AssertableJson $json) =>
            $json->has('message')
                 ->has('errors', fn (AssertableJson $json) =>
                    $json->has('email')
                         ->where('email', [0 => 'These credentials do not match our records.'])
                    )
        );
    }
}
