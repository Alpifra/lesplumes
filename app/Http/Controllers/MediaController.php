<?php

namespace App\Http\Controllers;

use App\Http\Resources\MediaResource;
use App\Models\Media;
use App\Models\Story;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Story $story): MediaResource
    {
        return new MediaResource($story->media);
    }

    /**
     * Store the newly created resource in storage.
     */
    public function store(Request $request, Story $story): MediaResource
    {
        $request = Media::validate($request);
        $now = new Carbon;
        $file = $request->file;
        $filename = md5($file->getFilename());

        $media = new Media;
        $media->disk = 'medias';
        $media->directory = 'stories';
        $media->filename = $filename;
        $media->extension = $file->getExtension();
        $media->mime_type = $file->getMimeType();
        $media->aggregate_type = $file->getExtension();
        $media->size = $file->getMaxFilesize();
        $media->created_at = $now;
        $media->updated_at = $now;
        $media->story_id = $story->id;
        $media->save();

        $file->storePubliclyAs(
            'uploads/stories/',
            $filename . '.' . $media->extension,
        );

        return new MediaResource($media);
    }

    /**
     * Display the specified resource.
     */
    public function show(Story $story, Media $media): MediaResource
    {
        if ($story->id !== $media->story?->id) abort(404);

        return new MediaResource($story->media);
    }

    /**
     * Update the resource in storage.
     */
    public function update(Request $request, Story $story): MediaResource
    {
        $request = Media::validate($request);

        $now = new Carbon;
        $file = $request->file;
        $filename = md5($file->getFilename());
        $media = $story->media;

        if (!$media) abort(404);

        $media->filename = $filename;
        $media->extension = $file->getExtension();
        $media->mime_type = $file->getMimeType();
        $media->aggregate_type = $file->getExtension();
        $media->size = $file->getMaxFilesize();
        $media->updated_at = $now;
        $media->save();

        $file->storePubliclyAs(
            'uploads/stories/',
            $filename . '.' . $media->extension,
        );

        return new MediaResource($media);
    }

    /**
     * Remove the resource from storage.
     */
    public function destroy(): never
    {
        abort(404);
    }
}
