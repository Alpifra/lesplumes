<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DeactivateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:deactivate
                            {user : Pseudo ou e-mail de la plume}
                            {--reactivate : Remet la plume dans le cercle}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Retire une plume du cercle en gardant ses textes et son historique, ou l'y remet";

    /**
     * The circle has no admin role: taking out a plume who left is done from
     * here, on the server.
     */
    public function handle(): int
    {
        $user = User::where('user_name', $this->argument('user'))
            ->orWhere('email', $this->argument('user'))
            ->first();

        if (!$user) {
            $this->error("Aucune plume ne répond à « {$this->argument('user')} ».");

            return self::FAILURE;
        }

        if ($this->option('reactivate')) {
            if (!$user->isDeactivated()) {
                $this->info("{$user->user_name} est déjà dans le cercle.");

                return self::SUCCESS;
            }

            $user->reactivate();
            $this->info("{$user->user_name} est de retour dans le cercle.");

            return self::SUCCESS;
        }

        if ($user->isDeactivated()) {
            $this->info("{$user->user_name} est déjà désactivée depuis le {$user->deactivated_at->format('d/m/Y')}.");

            return self::SUCCESS;
        }

        $user->deactivate();
        $this->info("{$user->user_name} est désactivée : elle ne peut plus se connecter, ses textes restent.");

        return self::SUCCESS;
    }
}
