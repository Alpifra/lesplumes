<?php

namespace App\Mail;

use App\Models\Round;
use App\Models\User;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Invites someone outside the circle to sign up — to the circle itself, or
 * straight to a session when a master invites her to one.
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
                'url' => static::frontUrl('inscription?' . http_build_query(['email' => $this->email])),
            ],
        );
    }
}
