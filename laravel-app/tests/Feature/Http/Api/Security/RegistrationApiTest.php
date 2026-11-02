<?php

namespace Tests\Feature\Http\Api\Security;

use App\Mail\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationApiTest extends TestCase
{
    use RefreshDatabase;

    private function signUp(array $overrides = []): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/register', array_merge([
            'first_name'            => 'Jeanne',
            'last_name'             => 'Doe',
            'user_name'             => 'jeanne.plume',
            'email'                 => 'jeanne@lesplumes.test',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'invitation'            => Invitation::token('jeanne@lesplumes.test'),
        ], $overrides));
    }

    /**
     * @test
     * @group api
     * @group security
     */
    public function an_invited_person_can_sign_up(): void
    {
        $this->signUp()->assertSuccessful();

        $this->assertDatabaseHas('users', ['email' => 'jeanne@lesplumes.test']);
        $this->assertAuthenticated();
    }

    /**
     * @test
     * @group api
     * @group security
     */
    public function nobody_can_sign_up_without_an_invitation(): void
    {
        $this->signUp(['invitation' => null])
            ->assertStatus(422)
            ->assertJsonPath('errors.invitation.0', "L'inscription se fait uniquement sur invitation.");

        $this->assertDatabaseMissing('users', ['email' => 'jeanne@lesplumes.test']);
        $this->assertGuest();
    }

    /**
     * @test
     * @group api
     * @group security
     */
    public function an_invitation_only_works_for_its_own_email(): void
    {
        $this->signUp(['email' => 'autre@lesplumes.test'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('invitation');

        $this->signUp(['invitation' => 'forged'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('invitation');

        $this->assertDatabaseCount('users', 0);
    }

    /**
     * @test
     * @group api
     * @group security
     */
    public function the_invitation_ignores_the_case_of_the_email(): void
    {
        $this->signUp(['email' => 'Jeanne@LesPlumes.test'])->assertSuccessful();
    }
}
