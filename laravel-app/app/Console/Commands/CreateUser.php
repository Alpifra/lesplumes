<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Rules\Password;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:create
                            {email : E-mail de la plume}
                            {user_name : Pseudo}
                            {first_name : Prénom}
                            {last_name : Nom}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crée une plume sans invitation (le mot de passe est demandé, sans être affiché)';

    /**
     * Signing up is on invitation only, and inviting requires being logged in:
     * the first plume is created from here, on the server.
     */
    public function handle(): int
    {
        $input = [
            'first_name' => $this->argument('first_name'),
            'last_name'  => $this->argument('last_name'),
            'user_name'  => $this->argument('user_name'),
            'email'      => $this->argument('email'),
            // Asked, not passed as an argument, so it stays out of the shell history
            'password'   => $this->secret('Mot de passe'),
        ];

        $validator = Validator::make($input, [
            'first_name' => ['required', 'string', 'max:50'],
            'last_name'  => ['required', 'string', 'max:50'],
            'user_name'  => ['required', 'string', 'max:50', Rule::unique(User::class)],
            'email'      => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'password'   => ['required', 'string', new Password],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([...$input, 'password' => Hash::make($input['password'])]);
        $this->info("{$user->user_name} a rejoint le cercle, elle peut se connecter avec {$user->email}.");

        return self::SUCCESS;
    }
}
