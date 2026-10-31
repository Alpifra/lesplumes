<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Tests\TestCase;

class DeactivateUserCommandTest extends TestCase
{
    /**
     * @test
     * @group console
     * @group user
     */
    public function a_plume_can_be_deactivated_by_her_handle(): void
    {
        $user = User::factory()->create();

        $this->artisan('users:deactivate', ['user' => $user->user_name])
            ->assertSuccessful();

        self::assertTrue($user->fresh()->isDeactivated());
    }

    /**
     * @test
     * @group console
     * @group user
     */
    public function a_plume_can_be_reactivated_by_her_email(): void
    {
        $user = User::factory()->create();
        $user->deactivate();

        $this->artisan('users:deactivate', ['user' => $user->email, '--reactivate' => true])
            ->assertSuccessful();

        self::assertFalse($user->fresh()->isDeactivated());
    }

    /**
     * @test
     * @group console
     * @group user
     */
    public function an_unknown_plume_is_reported(): void
    {
        $this->artisan('users:deactivate', ['user' => 'personne-' . uniqid()])
            ->assertFailed();
    }
}
