<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoundCollection;
use App\Http\Resources\RoundResource;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\Media;
use App\Models\Round;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class RoundController extends Controller
{
    /**
     * Display a listing of the resource, with optional search / status /
     * date filters used by the sessions list.
     *
     * Only the rounds the authenticated user takes part in — as master or as
     * participant — are returned.
     */
    public function index(Request $request): RoundCollection
    {
        $user = $request->user();

        $query = Round::query()
            ->with(['master', 'participants', 'roundStories.writer', 'roundStories.media'])
            ->where(function ($q) use ($user) {
                $q->where('master_id', $user->id)
                    ->orWhereHas('participants', fn ($p) => $p->whereKey($user->id));
            });

        if ($search = $request->query('search')) {
            $query->where('word', 'like', '%' . $search . '%');
        }

        switch ($request->query('status')) {
            case 'en-cours':
                $query->where(function ($q) {
                    $q->whereNull('end_at')->orWhere('end_at', '>', now());
                });
                break;
            case 'termine':
                $query->whereNotNull('end_at')->where('end_at', '<=', now());
                break;
        }

        if ($from = $request->query('date_from')) {
            $query->whereDate('start_at', '>=', $from);
        }

        if ($to = $request->query('date_to')) {
            $query->whereDate('end_at', '<=', $to);
        }

        return new RoundCollection(
            $query->orderByDesc('created_at')->orderByDesc('id')->paginate()->withQueryString()
        );
    }

    /**
     * The state of the session to come: the plume whose turn it is to pick
     * the word, the circle in rotation order, and the session that closed
     * last — which the dashboard reads once no session is running.
     */
    public function next(): JsonResponse
    {
        $selector = Round::nextSelector();
        $previous = Round::mostRecent();

        return response()->json([
            'data' => [
                'selector'       => $selector ? new UserResource($selector) : null,
                'plumes'         => new UserCollection(User::active()->inCircleOrder()->get()),
                'previous_round' => $previous ? new RoundResource($previous) : null,
            ],
        ]);
    }

    /**
     * Hand the word-picking over to the next plume of the circle, who opens
     * the next session in place of the plume whose turn it was.
     *
     * The hand-off is recorded on the session that closed last, so a plume
     * who received the turn may pass it on again, to her own next plume, by
     * overwriting it — the turn goes round until someone takes it.
     */
    public function handOff(Request $request): JsonResponse
    {
        $this->authorize('create', Round::class);

        $request->validate([
            'plume' => ['required', static::activePlume()],
        ], [
            'plume.exists' => "Cette plume n'est plus dans le cercle.",
        ]);

        abort_if(
            (int) $request->plume === $request->user()->id,
            422,
            'La main est déjà à vous.'
        );

        abort_unless(
            (int) $request->plume === User::nextInCircle($request->user())?->id,
            422,
            "La main ne passe qu'à la plume suivante."
        );

        $round = Round::mostRecent();

        abort_unless($round, 409, 'Aucune session close à laquelle rattacher la main.');

        $round->next_master_id = $request->plume;
        $round->save();

        return response()->json(['data' => new UserResource(User::findOrFail($request->plume))]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RoundResource
    {
        $this->authorize('create', Round::class);

        $request = Round::validate($request);

        // Checked on opening only: a past session keeps the plumes who took
        // part in it, even those who left since, when its master edits it.
        $request->validate([
            'participants.*' => static::activePlume(),
        ], [
            'participants.*.exists' => "Une des plumes convoquées n'est plus dans le cercle.",
        ]);

        abort_unless(
            (int) $request->master === $request->user()->id,
            403,
            "La plume qui ouvre la session en est le maître."
        );

        $round = new Round;
        $round->word = $request->word;
        $round->master_id = $request->master;
        $round->start_at = $request->start_at;
        $round->end_at = $request->end_at;
        $round->save();

        $round->participants()->sync($request->participants);

        return new RoundResource($round);
    }

    /**
     * Display the specified resource.
     */
    public function show(Round $round): RoundResource
    {
        $this->authorize('view', $round);

        return new RoundResource($round);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Round $round): RoundResource
    {
        $this->authorize('update', $round);

        $request = Round::validate($request);

        $round->word = $request->word;
        $round->master_id = $request->master;
        $round->start_at = $request->start_at;
        $round->end_at = $request->end_at;
        $round->save();

        $round->participants()->sync($request->participants);

        return new RoundResource($round);
    }

    /**
     * Invite a plume to a round by email.
     *
     * If the email belongs to an existing user, they are attached as a
     * participant; otherwise a lightweight invitation email is sent.
     */
    public function invite(Request $request, Round $round): JsonResponse
    {
        abort_unless($round->master_id === $request->user()->id, 403, 'Seul le maître du jeu peut inviter.');

        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        abort_if($user?->isDeactivated(), 422, "Cette plume n'est plus dans le cercle.");

        if ($user) {
            $round->participants()->syncWithoutDetaching([$user->id]);

            return response()->json([
                'status' => 'attached',
                'user'   => new UserResource($user),
            ]);
        }

        Mail::raw(
            "Vous êtes invité·e à rejoindre le cercle des plumes sur Les Plumes "
            . "et à participer à la session « {$round->word} ». "
            . "Créez votre compte pour commencer à écrire.",
            function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Invitation à rejoindre Les Plumes');
            }
        );

        return response()->json(['status' => 'invited']);
    }

    /**
     * Rule for a user id that must belong to a plume still in the circle.
     */
    private static function activePlume(): Exists
    {
        return Rule::exists('users', 'id')->whereNull('deactivated_at');
    }

    /**
     * Download every text of a round bundled as a ZIP archive.
     */
    public function download(Round $round): BinaryFileResponse
    {
        $this->authorize('view', $round);

        $round->load('roundStories.media', 'roundStories.writer');

        $tmpPath = tempnam(sys_get_temp_dir(), 'round') . '.zip';
        $zip = new ZipArchive;
        $zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $disk = Storage::disk(Media::STORY_MEDIA_DISK);
        foreach ($round->roundStories as $story) {
            $media = $story->media;
            if (!$media || !$disk->exists($media->relativePath())) continue;

            $writer = $story->writer?->user_name ?? ('plume-' . $story->id);
            $label  = Str::slug(($story->title ?: 'texte') . '-' . $writer);
            $zip->addFile($disk->path($media->relativePath()), $label . '.' . $media->extension);
        }

        $zip->close();

        $archiveName = 'session-' . Str::slug($round->word) . '-' . $round->id . '.zip';

        return response()->download($tmpPath, $archiveName)->deleteFileAfterSend(true);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Round $round): Response
    {
        $this->authorize('delete', $round);

        $round->delete();

        return Response(null, 204);
    }
}
