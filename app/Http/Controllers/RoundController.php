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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): RoundResource
    {
        return new RoundResource(Round::findOrFail($id));
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
