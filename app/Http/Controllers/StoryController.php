<?php

namespace App\Http\Controllers;

use App\Http\Resources\StoryCollection;
use App\Http\Resources\StoryResource;
use App\Models\Round;
use App\Models\Story;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Round $round): StoryCollection
    {
        return new StoryCollection(
            Story::where('round_id', $round->id)->paginate()
        );
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
    public function show(Round $round, Story $story): StoryResource
    {
        if ($round->id !== $story->round?->id) abort(404);

        return new StoryResource($story);
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
    public function destroy(string $id)
    {
        //
    }
}
