<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoundCollection;
use App\Http\Resources\RoundResource;
use App\Models\Round;
use Illuminate\Http\Request;

class RoundController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): RoundCollection
    {
        return new RoundCollection(Round::paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RoundResource
    {
        $request = Round::validate($request);

        $round = new Round;
        $round->word = $request->word;
        $round->master_id = $request->master;
        $round->save();

        $round->participants()->sync($request->participants);

        return new RoundResource($round);
    }

    /**
     * Display the specified resource.
     */
    public function show(Round $round): RoundResource
    {
        return new RoundResource($round);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Round $round): RoundResource
    {
        $request = Round::validate($request);

        $round->word = $request->word;
        $round->master_id = $request->master;
        $round->save();

        $round->participants()->sync($request->participants);

        return new RoundResource($round);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
