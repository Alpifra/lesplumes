<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Turn away a plume whose account was deactivated while she was still
     * logged in: her session is closed and she is answered as a guest, so
     * the front sends her back to the login page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isDeactivated()) {
            Auth::guard('web')->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            abort(401, 'Ce compte est désactivé.');
        }

        return $next($request);
    }
}
