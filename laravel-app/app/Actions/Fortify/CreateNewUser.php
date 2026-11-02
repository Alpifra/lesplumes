<?php

namespace App\Actions\Fortify;

use App\Mail\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'first_name' => ['required', 'string', 'max:50'],
            'last_name'  => ['required', 'string', 'max:50'],
            'user_name'  => ['required', 'string', 'max:50', Rule::unique(User::class)],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            // Signing up is on invitation only (see App\Mail\Invitation).
            'invitation' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) use ($input) {
                    if (!is_string($input['email'] ?? null) || !Invitation::isValid($input['email'], $value)) {
                        $fail("L'inscription se fait uniquement sur invitation.");
                    }
                },
            ],
        ], [
            'invitation.required' => "L'inscription se fait uniquement sur invitation.",
        ])->validate();

        return User::create([
            'first_name' => $input['first_name'],
            'last_name'  => $input['last_name'],
            'user_name'  => $input['user_name'],
            'email'      => $input['email'],
            'password'   => Hash::make($input['password']),
        ]);
    }
}
