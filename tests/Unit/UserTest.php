<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{

    use RefreshDatabase;

    /**
     * @test
     * @group model
     * @group user
     */
    public function create_model()
    {
        $user = new User;
        $user->first_name = fake()->firstName();
        $user->last_name = fake()->lastName();
        $user->email = fake()->email();
        $user->password = Hash::make(fake()->password());

        self::assertNull($user->id);

        $user->save();

        self::assertNotNull($user->id);
        self::assertCount(1, User::all());
    }

    /**
     * @test
     * @group model
     * @group user
     */
    public function edit_model()
    {
        User::factory(1)->create();

        $first_name = fake()->firstName();
        $last_name = fake()->lastName();
        $email = fake()->email();
        $password = Hash::make(fake()->password());

        $user = User::all()->first();
        $user->first_name = $first_name;
        $user->last_name = $last_name;
        $user->email = $email;
        $user->password = $password;

        $user->save();
        $user->refresh();

        self::assertEquals($first_name, $user->first_name);
        self::assertEquals($last_name, $user->last_name);
        self::assertEquals($email, $user->email);
        self::assertEquals($password, $user->password);
    }

    /**
     * @test
     * @group model
     * @group user
     */
    public function delete_model()
    {
        User::factory(1)->create();
        $user = User::all()->first();
        $user->delete();

        self::assertEmpty(User::all());
    }
}
