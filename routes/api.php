<?php

use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
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
