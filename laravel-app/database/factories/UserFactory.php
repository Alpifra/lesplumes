<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // The `testing` database is persistent and the tests do not reset it,
        // while Faker's `unique()` memory is rebuilt with the application on
        // every test. A time-based suffix is what actually keeps the handle
        // free of collisions against the rows left by the previous tests.
        $handle = fake()->userName() . '_' . uniqid();

        return [
            'first_name'     => fake()->firstName(),
            'last_name'      => fake()->firstName(),
            'user_name'      => $handle,
            'email'          => $handle . '@example.com',
            'password'       => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }
}
