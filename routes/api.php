<?php

use App\Http\Controllers\RoundController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\UserController;
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

    Route::resource('rounds', RoundController::class);
    Route::resource('stories', StoryController::class);
    Route::resource('users', UserController::class);

});
