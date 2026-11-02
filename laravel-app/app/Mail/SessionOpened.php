<?php

namespace App\Mail;

use App\Models\Round;
use App\Models\User;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Sent to each plume convened to a session the moment it opens.
 */
class SessionOpened extends CircleMail
{
    public function __construct(
        public Round $round,
        public User $plume,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Nouvelle session : « {$this->round->word} »");
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.session-opened',
            with: [
                'master'     => $this->round->master,
                'jokersLeft' => $this->plume->jokersLeft(),
                'url'        => static::frontUrl("sessions/{$this->round->id}"),
            ],
        );
    }
}
