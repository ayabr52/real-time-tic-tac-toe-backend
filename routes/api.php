<?php

use App\Events\MovePlayed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/rooms', [\App\Http\Controllers\GameController::class, 'createRoom']);
Route::post('/rooms/join', [\App\Http\Controllers\GameController::class, 'joinRoom']);
Route::get('/rooms/{name}', [\App\Http\Controllers\GameController::class, 'getRoomState']);
Route::post('/rooms/{name}/move', [\App\Http\Controllers\GameController::class, 'playMove']);
Route::post('/rooms/{name}/reset', [\App\Http\Controllers\GameController::class, 'resetRoom']);
Route::delete('/rooms/{name}', [\App\Http\Controllers\GameController::class, 'deleteRoom']);
