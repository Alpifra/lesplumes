<?php

namespace App\Http\Controllers;

use App\Http\Resources\MediaResource;
use App\Models\Story;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    /**
     * Store the newly created resource in storage.
     */
    public function store(Request $request): never
    {
        abort(404);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): MediaResource
    {
        $story = Story::findOrFail($id);

        return new MediaResource($story->media);
    }

    /**
     * Update the resource in storage.
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Remove the resource from storage.
     */
    public function destroy(): never
    {
        abort(404);
    }
}
