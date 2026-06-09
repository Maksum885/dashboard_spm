<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AlarmController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CvController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PlcController;
use App\Http\Controllers\Api\RoomController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/dashboard', DashboardController::class);

    Route::get('/rooms', [RoomController::class, 'index']);
    Route::get('/rooms/{room_id}', [RoomController::class, 'show'])
        ->middleware('room.access');

    Route::get('/plc/{room_id}/data', [PlcController::class, 'getRoomData'])
        ->middleware('room.access');
    Route::get('/plc/{room_id}/logs', [PlcController::class, 'getRoomLogs'])
        ->middleware('room.access');

    Route::get('/alarms', [AlarmController::class, 'index']);
    Route::get('/alarms/count', [AlarmController::class, 'count']);
    Route::patch('/alarms/{id}/ack', [AlarmController::class, 'acknowledge'])
        ->middleware('role:admin,operator');
    Route::patch('/alarms/{id}/resolve', [AlarmController::class, 'resolve'])
        ->middleware('role:admin,operator');

    Route::get('/activity-logs', [ActivityLogController::class, 'index']);
    Route::get('/activity-logs/room/{room_id}', [ActivityLogController::class, 'byRoom'])
        ->middleware('room.access');
    Route::get('/activity-logs/maintenance/{room_id}', [ActivityLogController::class, 'maintenanceLogs'])
        ->middleware('room.access');

    Route::middleware('role:admin')->group(function () {
        Route::get('/plc/all/data', [PlcController::class, 'getAllRoomsData']);

        Route::get('/admin/users', [AdminUserController::class, 'index']);
        Route::post('/admin/users', [AdminUserController::class, 'store']);
        Route::put('/admin/users/{id}', [AdminUserController::class, 'update']);
        Route::delete('/admin/users/{id}', [AdminUserController::class, 'destroy']);
        Route::patch('/admin/users/{id}/toggle', [AdminUserController::class, 'toggle']);
    });
});

Route::post('/plc/webhook', [PlcController::class, 'webhook'])
    ->middleware('throttle:120,1');

Route::get('/plc/bridge-devices', [PlcController::class, 'bridgeDevices'])
    ->middleware('throttle:60,1');

Route::get('/cv/bridge-cameras', [CvController::class, 'bridgeCameras'])
    ->middleware('throttle:60,1');
