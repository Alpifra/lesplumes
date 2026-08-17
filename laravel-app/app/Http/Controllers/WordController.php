<?php

namespace App\Http\Controllers;

use App\Models\Word;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WordController extends Controller
{
    /**
     * A word for the session to come, drawn from the French dictionary.
     *
     * `exclude` carries what the plume already has under her eyes, so that
     * asking for another word never hands back the same one.
     */
    public function random(Request $request): JsonResponse
    {
        $request->validate(['exclude' => 'nullable|string|max:50']);

        $word = Word::draw($request->query('exclude'));

        // An empty table is a deployment left half-done, not a client error.
        abort_if(!$word, 503, "Le dictionnaire est vide : lancez php artisan words:import.");

        return response()->json(['data' => ['word' => $word]]);
    }
}
