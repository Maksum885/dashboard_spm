<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PlcController;
use App\Http\Controllers\Api\AlarmController;

// Auth
Route::post('/login',  [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // PLC Data (semua role bisa, tapi dibatasi room access)
    Route::get('/plc/{room_id}/data', [PlcController::class, 'getRoomData'])
        ->middleware('room.access');

    Route::get('/plc/{room_id}/logs', [PlcController::class, 'getRoomLogs'])
        ->middleware('room.access');

    // Admin only
    Route::middleware('role:admin')->group(function () {
        Route::get('/plc/all/data', [PlcController::class, 'getAllRoomsData']);
        Route::get('/admin/users',  [\App\Http\Controllers\Api\UserController::class, 'index']);
    });

    // Alarm
    Route::get('/alarms',              [AlarmController::class, 'index']);
    Route::patch('/alarms/{id}/ack',   [AlarmController::class, 'acknowledge']);
});

// Webhook dari Python (no auth, tapi bisa tambah secret key)
Route::post('/plc/webhook', [PlcController::class, 'webhook'])
    ->middleware('throttle:120,1'); // max 120 req/menit

Route::get('/rooms', [App\Http\Controllers\Api\RoomController::class, 'index']);
