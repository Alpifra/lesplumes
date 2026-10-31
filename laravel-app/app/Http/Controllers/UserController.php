<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    /**
     * The plumes of the circle. Those who left are kept out: they still show
     * through the sessions they took part in.
     */
    public function index(): UserCollection
    {
        $this->authorize('viewAny', User::class);

        return new UserCollection(User::active()->paginate());
    }

    /**
     * Invite a plume to join the circle by email.
     *
     * Existing members are reported as such; unknown emails receive a
     * lightweight invitation to register.
     */
    public function invite(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            return response()->json([
                'status' => $user->isDeactivated() ? 'deactivated' : 'exists',
                'user'   => new UserResource($user),
            ]);
        }

        Mail::raw(
            "Vous êtes invité·e à rejoindre le cercle des plumes sur Les Plumes. "
            . "Créez votre compte pour participer aux sessions d'écriture.",
            function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Invitation à rejoindre Les Plumes');
            }
        );

        return response()->json(['status' => 'invited']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): never
    {
        abort(404);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $username): UserResource
    {
        $user = User::where('user_name', $username)
            ->orWhere('email', $username)
            ->firstOrFail();

        $this->authorize('view', $user);

        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): UserResource
    {
        $this->authorize('update', $user);

        $request = User::validate($request, $user);

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->user_name = $request->user_name;
        $user->save();

        return new UserResource($user);
    }

    /**
     * Close an account by deactivating it rather than deleting it: the row
     * holds the plume's texts and her place in past sessions, which a delete
     * would cascade away. Her session ends with it.
     */
    public function destroy(Request $request, User $user): Response
    {
        $this->authorize('delete', $user);

        $user->deactivate();

        if ($request->user()->is($user)) {
            Auth::guard('web')->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
        }

        return Response(null, 204);
    }
}
