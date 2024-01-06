<?php

namespace App\Http\Controllers;

use App\Http\Resources\StoryCollection;
use App\Http\Resources\StoryResource;
use App\Models\Story;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): StoryCollection
    {
        return new StoryCollection(Story::paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): StoryResource
    {
        return new StoryResource(Story::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
