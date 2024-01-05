<?php

use App\Http\Resources\StoryCollection;
use App\Http\Resources\StoryResource;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\Story;
use App\Models\User;
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

    // Story resource routing
    Route::prefix('stories')->name('stories.')->group( function() {
        Route::name('collection')->get(
            '/', fn () => new StoryCollection(Story::paginate())
        );
        Route::name('single')->get(
            '/{id}', fn (string $id) => new StoryResource(Story::findOrFail($id))
        );
    });

    // User resource routing
    Route::prefix('users')->name('users.')->group( function() {
        Route::name('collection')->get(
            '/', fn () => new UserCollection(User::paginate())
        );
        Route::name('single')->get(
            '/{id}', fn (string $id) => new UserResource(User::findOrFail($id))
        );
    });

});
