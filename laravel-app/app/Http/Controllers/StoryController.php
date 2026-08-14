<?php

namespace App\Http\Controllers;

use App\Http\Resources\StoryCollection;
use App\Http\Resources\StoryResource;
use App\Models\Media;
use App\Models\Round;
use App\Models\Story;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Round $round): StoryCollection
    {
        $this->authorize('view', $round);

        return new StoryCollection(
            Story::where('round_id', $round->id)->paginate()
        );
    }

    /**
     * Deposit the authenticated user's text (a "plume") on a round.
     *
     * Each participant holds a single story per round, so an existing one
     * is updated (title + file replaced) rather than duplicated.
     */
    public function store(Request $request, Round $round): JsonResponse
    {
        $user = $request->user();

        // Only the master or a participant may deposit on the session.
        $isParticipant = $round->participants()->where('users.id', $user->id)->exists();
        abort_unless(
            $isParticipant || $round->master_id === $user->id,
            403,
            'Vous ne participez pas à cette session.'
        );

        // A finished session no longer accepts deposits.
        abort_if($round->status === 'termine', 403, 'Cette session est terminée.');

        $request->validate(['title' => 'nullable|string|max:150']);
        Media::validate($request);

        $story = Story::firstOrNew([
            'round_id'  => $round->id,
            'writer_id' => $user->id,
        ]);
        $story->title = $request->input('title');
        $story->save();

        Media::storeForStory($story, $request->file('file'));

        $round->closeIfComplete();

        return (new StoryResource($story->fresh(['writer', 'media'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Round $round, Story $story): StoryResource
    {
        $this->authorize('view', $round);

        if ($round->id !== $story->round?->id) abort(404);

        return new StoryResource($story);
    }

    /**
     * Download the PDF attached to a story.
     */
    public function download(Round $round, Story $story): StreamedResponse
    {
        $this->authorize('view', $round);

        if ($round->id !== $story->round_id) abort(404);

        $media = $story->media;
        if (!$media) abort(404);

        $disk = Storage::disk(Media::STORY_MEDIA_DISK);
        abort_unless($disk->exists($media->relativePath()), 404);

        $name = Str::slug($story->title ?: 'texte') . '.' . $media->extension;

        return $disk->download($media->relativePath(), $name);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): never
    {
        abort(404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Round $round, Story $story): Response
    {
        $this->authorize('delete', $story);

        if ($round->id !== $story->round?->id) abort(404);

        $story->delete();

        return Response(null, 204);
    }
}
