<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateUserCommandTest extends TestCase
{
    #[Test]
    #[Group('console')]
    #[Group('user')]
    public function a_plume_can_be_created_without_invitation(): void
    {
        $email = 'plume-' . uniqid() . '@example.com';

        $this->artisan('users:create', [
            'email'      => $email,
            'user_name'  => 'plume-' . uniqid(),
            'first_name' => 'Amélie',
            'last_name'  => 'Pichon',
        ])
            ->expectsQuestion('Mot de passe', 'un-mot-de-passe-solide')
            ->assertSuccessful();

        $user = User::where('email', $email)->firstOrFail();
        self::assertTrue(Hash::check('un-mot-de-passe-solide', $user->password));
    }

    #[Test]
    #[Group('console')]
    #[Group('user')]
    public function an_email_already_taken_is_refused(): void
    {
        $user = User::factory()->create();

        $this->artisan('users:create', [
            'email'      => $user->email,
            'user_name'  => 'plume-' . uniqid(),
            'first_name' => 'Amélie',
            'last_name'  => 'Pichon',
        ])
            ->expectsQuestion('Mot de passe', 'un-mot-de-passe-solide')
            ->assertFailed();

        self::assertSame(1, User::where('email', $user->email)->count());
    }
}
