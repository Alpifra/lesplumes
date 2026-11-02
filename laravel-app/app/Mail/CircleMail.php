<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * An email of the circle. Sent along a request (a deposit, a new session…),
 * so a mail server down must not fail what the plume was doing: the failure
 * is reported and the request goes on.
 */
abstract class CircleMail extends Mailable
{
    /**
     * Send the mail to an address, reporting a failure rather than throwing.
     */
    public function deliverTo(string $email): void
    {
        try {
            Mail::to($email)->send($this);
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * A page of the Vue app.
     */
    protected static function frontUrl(string $path = ''): string
    {
        return rtrim(config('app.frontend_url'), '/') . '/' . ltrim($path, '/');
    }
}
