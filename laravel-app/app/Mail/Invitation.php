<?php

namespace App\Mail;

use App\Models\Round;
use App\Models\User;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Invites someone outside the circle to sign up — to the circle itself, or
 * straight to a session when a master invites her to one.
 *
 * Signing up is on invitation only: the link carries a token signed with the
 * app key and bound to the invited email, which registration checks.
 */
class Invitation extends CircleMail
{
    public function __construct(
        public string $email,
        public ?User $inviter = null,
        public ?Round $round = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Invitation à rejoindre Les Plumes');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invitation',
            with: [
                'url' => static::frontUrl('inscription?' . http_build_query([
                    'email'      => $this->email,
                    'invitation' => static::token($this->email),
                ])),
            ],
        );
    }

    /**
     * The token of the invitation sent to an email.
     */
    public static function token(string $email): string
    {
        return hash_hmac('sha256', 'invitation|' . mb_strtolower(trim($email)), config('app.key'));
    }

    /**
     * Whether a token is the one of the invitation sent to this email.
     */
    public static function isValid(string $email, string $token): bool
    {
        return hash_equals(static::token($email), $token);
    }
}
