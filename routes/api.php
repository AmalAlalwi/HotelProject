<?php

use App\Http\Controllers\Api\Admin\RoomController;

use App\Http\Controllers\Api\Admin\ServiceController;
use App\Http\Controllers\Api\User\BookingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\User\MessageController;
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
//-----------------------------api auth-----------------------------------------------------------

    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth_jwt')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    });

//------------------------------CRUD Rooms--------------------------------------------------------

Route::middleware(['auth_jwt','admin'])->group(function () {
//-------------------------------Rooms api-------------------------------------------
    Route::get('/rooms', [RoomController::class, 'index']);
    Route::post('/rooms', [RoomController::class, 'store']);
    Route::put('/rooms/{room}', [RoomController::class, 'update']);
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy']);
    Route::get('/rooms/{room}', [RoomController::class, 'show']);
//-------------------------------Service api----------------------------------------
    Route::apiResource('/services', ServiceController::class);
});

Route::middleware(['auth_jwt'])->group(function () {
    Route::apiResource('booking', BookingController::class);
    Route::get('/getrooms', [\App\Http\Controllers\Api\User\RoomController::class, 'index']);
    Route::post('/sendMessage', [MessageController::class, 'sendMessage']);
    Route::get('/conversation/{receiverId}', [MessageController::class, 'getConversation']);
});
