<?php

use App\Http\Controllers\MediaController;
use App\Http\Controllers\RoundController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WordController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->name('api.')->group( function() {

    Route::get('stats', [StatsController::class, 'index'])->name('stats.index');

    // Declared before the resource so "next" is not read as a {round} key.
    Route::get('rounds/next', [RoundController::class, 'next'])->name('rounds.next');
    Route::post('rounds/hand-off', [RoundController::class, 'handOff'])->name('rounds.hand-off');

    // The word of the session to come, drawn from the French dictionary.
    Route::get('words/random', [WordController::class, 'random'])->name('words.random');

    Route::get('rounds/{round}/download', [RoundController::class, 'download'])->name('rounds.download');
    Route::post('rounds/{round}/invite', [RoundController::class, 'invite'])->name('rounds.invite');
    Route::get('rounds/{round}/stories/{story}/download', [StoryController::class, 'download'])->name('rounds.stories.download');
    Route::post('invitations', [UserController::class, 'invite'])->name('invitations.store');

    Route::resource('stories.media', MediaController::class);
    Route::resource('rounds', RoundController::class);
    Route::resource('rounds.stories', StoryController::class);
    Route::resource('users', UserController::class);

});
