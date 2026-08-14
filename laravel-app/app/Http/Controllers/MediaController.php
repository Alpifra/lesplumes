<?php

namespace App\Http\Controllers;

use App\Http\Resources\MediaResource;
use App\Models\Media;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MediaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Story $story): MediaResource
    {
        $this->authorize('view', $story);

        return new MediaResource($story->media);
    }

    /**
     * Store the newly created resource in storage.
     */
    public function store(Request $request, Story $story): MediaResource
    {
        $this->authorize('update', $story);

        abort_if($story->round?->status === 'termine', 403, 'Cette session est terminée.');

        Media::validate($request);

        $media = Media::storeForStory($story, $request->file('file'));

        return new MediaResource($media);
    }

    /**
     * Display the specified resource.
     */
    public function show(Story $story, Media $media): MediaResource
    {
        $this->authorize('view', $story);

        if ($story->id !== $media->story?->id) abort(404);

        return new MediaResource($story->media);
    }

    /**
     * Update the resource in storage.
     */
    public function update(Request $request, Story $story): MediaResource
    {
        $this->authorize('update', $story);

        abort_if($story->round?->status === 'termine', 403, 'Cette session est terminée.');

        if (!$story->media) abort(404);

        Media::validate($request);

        $media = Media::storeForStory($story, $request->file('file'));

        return new MediaResource($media);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Story $story): Response
    {
        $this->authorize('delete', $story);

        if (!$media = $story->media) abort(404);

        $media->delete();

        return Response(null, 204);
    }
}
