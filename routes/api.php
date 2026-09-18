<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FavoriteTeamController;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\SportController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/sports', [SportController::class, 'index']);

Route::get('/leagues', [LeagueController::class, 'index']);
Route::get('/leagues/{league}/teams', [LeagueController::class, 'teams']);
Route::get('/leagues/{league}/table', [LeagueController::class, 'table']);

Route::get('/teams/{team}', [TeamController::class, 'show']);
Route::get('/teams/{team}/previous-matches', [TeamController::class, 'previousMatches']);

/*
|--------------------------------------------------------
| auth-sanctum
|--------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('favorites', FavoriteTeamController::class)
        ->only(['index', 'store', 'destroy']);
});
