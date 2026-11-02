<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * The link to choose a new password, asked from the "Mot de passe oublié"
 * page. It opens the Vue app, which posts the token back to Fortify.
 */
class ResetPassword extends CircleMail
{
    public function __construct(
        public User $plume,
        public string $token,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Choisir un nouveau mot de passe');
    }

    public function content(): Content
    {
        $broker = config('auth.defaults.passwords');

        return new Content(
            view: 'emails.reset-password',
            with: [
                'minutes' => config("auth.passwords.{$broker}.expire"),
                'url'     => static::frontUrl('mot-de-passe/nouveau?' . http_build_query([
                    'token' => $this->token,
                    'email' => $this->plume->email,
                ])),
            ],
        );
    }
}
