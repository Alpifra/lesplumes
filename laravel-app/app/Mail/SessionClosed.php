<?php

namespace App\Mail;

use App\Models\Round;
use App\Models\User;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Sent to the whole session — master and convened plumes — once it is over
 * and its texts open to the circle.
 */
class SessionClosed extends CircleMail
{
    public function __construct(
        public Round $round,
        public User $plume,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Session terminée : « {$this->round->word} »");
    }

    public function content(): Content
    {
        $stories = $this->round->roundStories()
            ->has('media')
            ->with('writer')
            ->get();

        // The recap of the session: who handed a text in, and who played a joker.
        $recap = $stories
            ->map(fn ($story) => ['plume' => $story->writer, 'title' => $story->title, 'joker' => false])
            ->concat($this->round->jokers()->get()
                ->map(fn (User $plume) => ['plume' => $plume, 'title' => null, 'joker' => true]))
            ->sortBy(fn ($entry) => mb_strtolower($entry['plume']?->first_name ?? ''))
            ->values();

        // Whose turn it is next only reads true for the latest session.
        $nextSelector = Round::mostRecent()?->is($this->round) ? Round::nextSelector() : null;

        return new Content(
            view: 'emails.session-closed',
            with: [
                'master'       => $this->round->master,
                'stories'      => $stories,
                'recap'        => $recap,
                'playedJoker'  => $this->round->jokers()->whereKey($this->plume->id)->exists(),
                'nextSelector' => $nextSelector,
                'url'          => static::frontUrl("sessions/{$this->round->id}"),
            ],
        );
    }
}
